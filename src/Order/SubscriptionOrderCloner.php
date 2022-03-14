<?php
declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Order;

use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use BitBag\SyliusMolliePlugin\Entity\OrderInterface;
use BitBag\SyliusMolliePlugin\Factory\PartialShip\ShipmentFactoryInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Order\Model\OrderInterface as SyliusOrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;

final class SubscriptionOrderCloner implements SubscriptionOrderClonerInterface
{
    private OrderItemClonerInterface $orderItemCloner;
    private FactoryInterface $orderFactory;
    private RandomnessGeneratorInterface $generator;
    private AdjustmentClonerInterface $adjustmentCloner;
    private ShipmentClonerInterface $shipmentCloner;

    public function __construct(
        OrderItemClonerInterface $orderItemCloner,
        FactoryInterface $orderFactory,
        RandomnessGeneratorInterface $generator,
        AdjustmentClonerInterface $adjustmentCloner,
        ShipmentClonerInterface $shipmentCloner
    )
    {
        $this->orderItemCloner = $orderItemCloner;
        $this->orderFactory = $orderFactory;
        $this->generator = $generator;
        $this->adjustmentCloner = $adjustmentCloner;
        $this->shipmentCloner = $shipmentCloner;
    }

    public function clone(
        MollieSubscriptionInterface $subscription,
        OrderInterface $order,
        OrderItemInterface $orderItem
    ): OrderInterface
    {
        $rootOrder = $subscription->getFirstOrder();

        /** @var OrderInterface $clonedOrder */
        $clonedOrder = $this->orderFactory->createNew();
        $ordersCount = $subscription->getOrders()->count();
        $orderNumberSequence = $ordersCount + 1;
        $clonedOrder->setNumber(
            sprintf('%s-%d-%d', $rootOrder->getNumber(), $subscription->getId(), $orderNumberSequence)
        );
        $clonedOrder->setRecurringSequenceIndex($ordersCount);
        $clonedOrder->setState(SyliusOrderInterface::STATE_NEW);
        $clonedOrder->setNotes($order->getNotes());
        $clonedOrder->setAbandonedEmail($order->isAbandonedEmail());
        $clonedOrder->setChannel($order->getChannel());
        $clonedOrder->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
        $clonedOrder->setCheckoutCompletedAt(new \DateTime());
        $clonedOrder->setCustomer($order->getCustomer());
        $clonedOrder->setCurrencyCode($order->getCurrencyCode());
        $clonedOrder->setCustomerIp($order->getCustomerIp());
        $clonedOrder->setLocaleCode($order->getLocaleCode());
        $clonedOrder->setPaymentState(OrderPaymentStates::STATE_AWAITING_PAYMENT);
        $clonedOrder->setPromotionCoupon($order->getPromotionCoupon());
        $clonedOrder->setShippingAddress(clone $order->getShippingAddress());
        $clonedOrder->setBillingAddress(clone $order->getBillingAddress());
        $clonedOrder->setShippingState(OrderShippingStates::STATE_READY);
        $clonedOrder->setTokenValue($this->generator->generateUriSafeString(10));

        $clonedItem = $this->orderItemCloner->clone($orderItem, $clonedOrder);
        $clonedOrder->addItem($clonedItem);

        foreach ($order->getAdjustments() as $adjustment) {
            if (\Sylius\Component\Core\Model\AdjustmentInterface::SHIPPING_ADJUSTMENT === $adjustment->getType()) {
                continue;
            }
            $clonedOrderAdjustment = $this->adjustmentCloner->clone($adjustment);

            $clonedOrder->addAdjustment($clonedOrderAdjustment);
        }

        if ($clonedOrder->isShippingRequired()) {
            foreach ($order->getShipments() as $shipment) {
                $clonedShipment = $this->shipmentCloner->clone($shipment);
                $clonedOrder->addShipment($clonedShipment);

                foreach ($clonedOrder->getItemUnits() as $unit) {
                    $clonedShipment->addUnit($unit);
                }

                foreach ($shipment->getAdjustments() as $adjustment) {
                    /** @var AdjustmentInterface $clonedAdjustment */
                    $clonedAdjustment = $this->adjustmentCloner->clone($adjustment);

                    $clonedShipment->addAdjustment($clonedAdjustment);
                    $clonedAdjustment->setShipment($clonedShipment);
                    $clonedAdjustment->setAdjustable($clonedOrder);
                }
            }

        }

        $clonedOrder->recalculateAdjustmentsTotal();
        $clonedOrder->recalculateItemsTotal();

        return $clonedOrder;
    }
}
