<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Action;

use BitBag\SyliusMolliePlugin\Action\Api\BaseApiAwareAction;
use BitBag\SyliusMolliePlugin\Checker\Refund\MollieOrderRefundCheckerInterface;
use BitBag\SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use BitBag\SyliusMolliePlugin\Payments\Methods\MealVoucher;
use BitBag\SyliusMolliePlugin\Refund\OrderRefundInterface;
use BitBag\SyliusMolliePlugin\Refund\PaymentRefundInterface;
use BitBag\SyliusMolliePlugin\Updater\Order\OrderVoucherAdjustmentUpdaterInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction extends BaseApiAwareAction implements StatusActionInterface
{
    use GatewayAwareTrait;

    /** @var PaymentRefundInterface */
    private $paymentRefund;

    /** @var OrderRefundInterface */
    private $orderRefund;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var OrderVoucherAdjustmentUpdaterInterface */
    private $orderVoucherAdjustmentUpdater;

    private MollieOrderRefundCheckerInterface $mollieOrderRefundChecker;

    public function __construct(
        PaymentRefundInterface $paymentRefund,
        OrderRefundInterface $orderRefund,
        MollieLoggerActionInterface $loggerAction,
        OrderVoucherAdjustmentUpdaterInterface $orderVoucherAdjustmentUpdater,
        MollieOrderRefundCheckerInterface $mollieOrderRefundChecker
    ) {
        $this->paymentRefund = $paymentRefund;
        $this->orderRefund = $orderRefund;
        $this->loggerAction = $loggerAction;
        $this->orderVoucherAdjustmentUpdater = $orderVoucherAdjustmentUpdater;
        $this->mollieOrderRefundChecker = $mollieOrderRefundChecker;
    }

    /** @param GetStatusInterface $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (
            !isset($details['payment_mollie_id']) &&
            !isset($details['subscription_mollie_id']) &&
            !isset($details['order_mollie_id']) &&
            !isset($details['statusError'])
        ) {
            $request->markNew();
            $this->loggerAction->addLog(sprintf('Mark new payment with id %s', $payment->getId()));

            return;
        }

        if (isset($details['statusError'])) {
            $request->markFailed();

            return;
        }

        if (true === isset($details['subscription_mollie_id'])) {
            try {
                /** @var Customer $customer */
                $customer = $this->mollieApiClient->customers->get($details['customer_mollie_id']);
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with get customer from mollie with: %s', $e->getMessage()));

                throw new ApiException(sprintf('Error with get customer from mollie with: %s', $e->getMessage()));
            }

            /** @var Subscription $subscription */
            $subscription = $customer->getSubscription($details['subscription_mollie_id']);

            switch ($subscription->status) {
                case SubscriptionStatus::STATUS_CANCELED:
                    $request->markCanceled();

                    break;
                case SubscriptionStatus::STATUS_ACTIVE:
                case SubscriptionStatus::STATUS_PENDING:
                case SubscriptionStatus::STATUS_COMPLETED:
                case SubscriptionStatus::STATUS_SUSPENDED:
                    $request->markCaptured();

                    break;
                default:
                    $request->markUnknown();

                    break;
            }

            $this->loggerAction->addLog(sprintf('Mark subscription status to: %s', $subscription->status));

            return;
        }

        if (false === isset($details['subscription_mollie_id']) && isset($details['payment_mollie_id'])) {
            try {
                $molliePayment = $this->mollieApiClient->payments->get($details['payment_mollie_id']);
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with get payment in status action with id %s', $details['payment_mollie_id']));

                throw new ApiException(sprintf('Error with get payment in status action with id %s', $details['payment_mollie_id']));
            }
        }

        if (false === isset($details['subscription_mollie_id']) && isset($details['order_mollie_id'])) {
            try {
                $order = $this->mollieApiClient->orders->get($details['order_mollie_id'], ['embed' => 'payments']);
                $payments = $order->_embedded->payments;

                /** @var Payment $payment */
                $payment = current($payments);

                if ($payment->method === MealVoucher::MEAL_VOUCHERS) {
                    $this->orderVoucherAdjustmentUpdater->update($payment, $order->metadata->order_id);
                }

                /** @var Payment $molliePayment */
                $molliePayment = $this->mollieApiClient->payments->get($payment->id);
                $molliePayment->metadata = $order->metadata;
            } catch (\Exception $e) {
                $this->loggerAction->addNegativeLog(sprintf('Error with get payment page with id %s', $details['payment_mollie_id']));

                throw new ApiException(sprintf('Error with get payment page with id %s', $details['payment_mollie_id']));
            }
        }
        if ($molliePayment->hasRefunds() || $molliePayment->hasChargebacks()) {
            if (isset($details['order_mollie_id'])) {
                $mollieOrderLinesRefundable = $this->mollieOrderRefundChecker->check($order);

                if ($mollieOrderLinesRefundable) {
                    $this->orderRefund->refund($order);
                    $this->loggerAction->addLog(sprintf('Mark payment order refunded to: %s', $molliePayment->status));
                } else {
                    $this->paymentRefund->refund($molliePayment);
                    $this->loggerAction->addLog(sprintf('Mark payment refunded to: %s', $molliePayment->status));
                }

                return;
            }

            $this->paymentRefund->refund($molliePayment);

            $this->loggerAction->addLog(sprintf('Mark payment refunded to: %s', $molliePayment->status));

            return;
        }

        switch ($molliePayment->status) {
            case PaymentStatus::STATUS_PENDING:
            case PaymentStatus::STATUS_OPEN:
                $request->markPending();

                break;
            case PaymentStatus::STATUS_AUTHORIZED:
                $request->markAuthorized();

                break;
            case PaymentStatus::STATUS_PAID:
                $request->markCaptured();

                break;
            case PaymentStatus::STATUS_CANCELED:
                $request->markCanceled();

                break;
            case PaymentStatus::STATUS_FAILED:
                $request->markFailed();

                break;
            case PaymentStatus::STATUS_EXPIRED:
                $request->markExpired();

                break;
            default:
                $request->markUnknown();

                break;
        }

        $this->loggerAction->addLog(sprintf('Mark payment status to: %s', $molliePayment->status));
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface;
    }
}
