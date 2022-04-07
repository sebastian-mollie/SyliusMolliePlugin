<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Creator;

use BitBag\SyliusMolliePlugin\DTO\PartialRefundItems;
use BitBag\SyliusMolliePlugin\Exceptions\OfflineRefundPaymentMethodNotFound;
use BitBag\SyliusMolliePlugin\Helper\ConvertOrderInterface;
use BitBag\SyliusMolliePlugin\Refund\Units\UnitsItemOrderRefundInterface;
use BitBag\SyliusMolliePlugin\Refund\Units\UnitsShipmentOrderRefundInterface;
use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;
use Webmozart\Assert\Assert;

final class OrderRefundCommandCreator implements OrderRefundCommandCreatorInterface
{
    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var UnitsItemOrderRefundInterface */
    private $unitsItemOrderRefund;

    /** @var UnitsShipmentOrderRefundInterface */
    private $shipmentOrderRefund;

    /** @var RefundPaymentMethodsProviderInterface */
    private $refundPaymentMethodProvider;

    public function __construct(
        RepositoryInterface $orderRepository,
        UnitsItemOrderRefundInterface $unitsItemOrderRefund,
        UnitsShipmentOrderRefundInterface $shipmentOrderRefund,
        RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->unitsItemOrderRefund = $unitsItemOrderRefund;
        $this->shipmentOrderRefund = $shipmentOrderRefund;
        $this->refundPaymentMethodProvider = $refundPaymentMethodProvider;
    }

    public function fromOrder(Order $order): RefundUnits
    {
        $orderId = $order->metadata->order_id;
        /** @var OrderInterface $syliusOrder */
        $syliusOrder = $this->orderRepository->findOneBy(['id' => $orderId]);
        Assert::notNull($order, sprintf('Cannot find order id with id %s', $orderId));

        $partialRefundItems = new PartialRefundItems();

        foreach ($order->lines as $line) {
            if ($line->status === 'paid' && $line->type === ConvertOrderInterface::PHYSICAL_TYPE) {
                $getRefundedQuantity = $this->unitsItemOrderRefund->getActualRefundedQuantity($syliusOrder, $line->metadata->item_id);
                $partialRefundItems->addPartialRefundItemByQuantity(
                    $line->metadata->item_id,
                    $line->type,
                    $line->quantityRefunded - $getRefundedQuantity
                );
            }
        }

        $refundMethods = $this->refundPaymentMethodProvider->findForChannel($syliusOrder->getChannel());

        if (empty($refundMethods)) {
            throw new OfflineRefundPaymentMethodNotFound(
                sprintf('Not found offline payment method on this channel with code :%s', $syliusOrder->getChannel()->getCode())
            );
        }

        $refundMethod = current($refundMethods);

        $unitsToRefund = $this->unitsItemOrderRefund->refund($syliusOrder, $partialRefundItems);
        $shipmentToRefund = $this->shipmentOrderRefund->refund($order, $syliusOrder);

        return new RefundUnits($syliusOrder->getNumber(), $unitsToRefund, $shipmentToRefund, $refundMethod->getId(), '');
    }
}
