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
use BitBag\SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use BitBag\SyliusMolliePlugin\Factory\ApiCustomerFactoryInterface;
use BitBag\SyliusMolliePlugin\Helper\ConvertOrderInterface;
use BitBag\SyliusMolliePlugin\Helper\PaymentDescriptionInterface;
use BitBag\SyliusMolliePlugin\Payments\PaymentTerms\Options;
use BitBag\SyliusMolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Mollie\Api\Types\PaymentMethod;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Webmozart\Assert\Assert;

final class ConvertMolliePaymentAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /** @var PaymentDescriptionInterface */
    private $paymentDescription;

    /** @var SessionInterface */
    private $session;

    /** @var RepositoryInterface */
    private $mollieMethodsRepository;

    /** @var ConvertOrderInterface */
    private $orderConverter;

    /** @var CustomerContextInterface */
    private $customerContext;

    /** @var PaymentLocaleResolverInterface */
    private $paymentLocaleResolver;

    /** @var ApiCustomerFactoryInterface */
    private $apiCustomerFactory;

    public function __construct(
        PaymentDescriptionInterface $paymentDescription,
        SessionInterface $session,
        RepositoryInterface $mollieMethodsRepository,
        ConvertOrderInterface $orderConverter,
        CustomerContextInterface $customerContext,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        ApiCustomerFactoryInterface $apiCustomerFactory
    ) {
        $this->paymentDescription = $paymentDescription;
        $this->session = $session;
        $this->mollieMethodsRepository = $mollieMethodsRepository;
        $this->orderConverter = $orderConverter;
        $this->customerContext = $customerContext;
        $this->paymentLocaleResolver = $paymentLocaleResolver;
        $this->apiCustomerFactory = $apiCustomerFactory;
    }

    /** @param Convert|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        Assert::notNull($payment->getCurrencyCode());
        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $divisor = 10 ** $currency->exp;

        Assert::notNull($payment->getAmount());
        $amount = number_format(abs($payment->getAmount() / $divisor), 2, '.', '');

        $paymentOptions = $payment->getDetails();

        if (isset($paymentOptions['metadata'])) {
            $molliePaymentMethod = $paymentOptions['metadata']['molliePaymentMethods'] ?? null;
            $cartToken = $paymentOptions['metadata']['cartToken'];
            $selectedIssuer = PaymentMethod::IDEAL === $molliePaymentMethod ? $paymentOptions['metadata']['selected_issuer'] : null;
        } else {
            $molliePaymentMethod = $paymentOptions['molliePaymentMethods'] ?? null;
            $cartToken = $paymentOptions['cartToken'];
            $selectedIssuer = PaymentMethod::IDEAL === $molliePaymentMethod ? $paymentOptions['issuers']['id'] : null;
        }

        $paymentMethod = $payment->getMethod();
        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);

        /** @var MollieGatewayConfigInterface $method */
        $method = $this->mollieMethodsRepository->findOneBy([
            'methodId' => $molliePaymentMethod,
            'gateway' => $paymentMethod->getId(),
        ]);
        $gatewayConfig = $method->getGateway()->getConfig();
        $details = [
            'amount' => [
                'value' => "$amount",
                'currency' => $currency->code,
            ],
            'description' => $this->paymentDescription->getPaymentDescription($payment, $method, $order),
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $customer->getId() ?? null,
                'molliePaymentMethods' => $molliePaymentMethod ?? null,
                'cartToken' => $cartToken ?? null,
                'selected_issuer' => $selectedIssuer ?? null,
            ],
            'full_name' => $customer->getFullName() ?? null,
            'email' => $customer->getEmail() ?? null,
        ];

        if (null !== $this->customerContext->getCustomer() && true === ($gatewayConfig['single_click_enabled'] ?? false)) {
            $mollieCustomer = $this->apiCustomerFactory->createNew($details);
            $this->gateway->execute($mollieCustomer);
            $model = $mollieCustomer->getModel();
            $details['metadata']['customer_mollie_id'] = $model['customer_mollie_id'];
        }

        if (false === $this->mollieApiClient->isRecurringSubscription()) {
            $details['customerId'] = $model['customer_mollie_id'] ?? null;
            $details['metadata']['methodType'] = Options::PAYMENT_API;

            if (null !== ($paymentLocale = $this->paymentLocaleResolver->resolveFromOrder($order))) {
                $details['locale'] = $paymentLocale;
            }

            if (Options::ORDER_API === array_search($method->getPaymentType(), Options::getAvailablePaymentType(), true)) {
                unset($details['customerId']);

                $details['metadata']['methodType'] = Options::ORDER_API;
                $details = $this->orderConverter->convert($order, $details, $divisor, $method);
            }
        }

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' === $request->getTo()
            ;
    }
}
