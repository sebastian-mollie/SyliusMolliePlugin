<?php
/*
    This file was created by developers working at BitBag
    Do you need more information about us and what we do? Visit our   website!
    We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/
declare(strict_types=1);

namespace spec\BitBag\SyliusMolliePlugin\Order;

use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use BitBag\SyliusMolliePlugin\Entity\OrderInterface;
use BitBag\SyliusMolliePlugin\Order\AdjustmentClonerInterface;
use BitBag\SyliusMolliePlugin\Order\OrderItemClonerInterface;
use BitBag\SyliusMolliePlugin\Order\ShipmentClonerInterface;
use BitBag\SyliusMolliePlugin\Order\SubscriptionOrderCloner;
use BitBag\SyliusMolliePlugin\Order\SubscriptionOrderClonerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface as SyliusOrderInterface;
use Sylius\Component\Promotion\Model\PromotionCouponInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;

final class SubscriptionOrderClonerSpec extends ObjectBehavior
{
    function let(
        OrderItemClonerInterface $orderItemCloner,
        FactoryInterface $orderFactory,
        RandomnessGeneratorInterface $generator,
        AdjustmentClonerInterface $adjustmentCloner,
        ShipmentClonerInterface $shipmentCloner
    ): void {
        $this->beConstructedWith(
            $orderItemCloner,
            $orderFactory,
            $generator,
            $adjustmentCloner,
            $shipmentCloner
        );
    }
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionOrderCloner::class);
    }

    function it_should_implements_subscription_order_cloner_interface(): void
    {
        $this->shouldImplement(SubscriptionOrderClonerInterface::class);
    }

    function it_clones(
        OrderInterface $order,
        OrderInterface $clonedOrder,
        OrderInterface $rootOrder,
        MollieSubscriptionInterface $subscription,
        OrderItemInterface $orderItem,
        OrderItemInterface $clonedItem,
        OrderItemUnitInterface $itemUnit,
        FactoryInterface $orderFactory,
        ChannelInterface $channel,
        CustomerInterface $customer,
        PromotionCouponInterface $coupon,
        AddressInterface $address,
        AddressInterface $billingAddress,
        RandomnessGeneratorInterface $generator,
        OrderItemClonerInterface $orderItemCloner,
        AdjustmentInterface $adjustment,
        AdjustmentInterface $clonedOrderAdjustment,
        AdjustmentClonerInterface $adjustmentCloner,
        ShipmentInterface $shipment
    ): void {
        $subscription->getFirstOrder()->willReturn($rootOrder);
        $rootOrder->getNumber()->willReturn('#001');
        $orderFactory->createNew()->willReturn($clonedOrder);
        $subscription->getOrders()->willReturn(new ArrayCollection([
            $order->getWrappedObject()
        ]));
        $subscription->getId()->willReturn(3);
        $ordersCount = 1;
        $orderNumberSequence = $ordersCount + 1;

        $order->getNotes()->willReturn('test_notes');
        $order->isAbandonedEmail()->willReturn(false);
        $order->getChannel()->willReturn($channel);
        $order->getCustomer()->willReturn($customer);
        $order->getCurrencyCode()->willReturn('EUR');
        $order->getCustomerIp()->willReturn('127.0.0.0');
        $order->getLocaleCode()->willReturn('en_US');
        $order->getPromotionCoupon()->willReturn($coupon);
        $order->getShippingAddress()->willReturn($address);
        $order->getBillingAddress()->willReturn($billingAddress);
        $order->setTokenValue($generator->generateUriSafeString(10));
        $order->getAdjustments()->willReturn(new ArrayCollection([
            $adjustment->getWrappedObject()
        ]));
        $order->getShipments()->willReturn(new ArrayCollection([
            $shipment->getWrappedObject()
        ]));
        $order->getItemUnits()->willReturn(new ArrayCollection([
            $itemUnit->getWrappedObject()
        ]));

        $clonedOrder->setNumber(
            sprintf('%s-%d-%d','#001',3,$orderNumberSequence));

        $clonedOrder->setRecurringSequenceIndex($ordersCount)->shouldBeCalled();
        $clonedOrder->setState(SyliusOrderInterface::STATE_NEW)->shouldBeCalled();
        $clonedOrder->setNotes('test_notes')->shouldBeCalled();
        $clonedOrder->setAbandonedEmail(false)->shouldBeCalled();
        $clonedOrder->setChannel($channel)->shouldBeCalled();
        $clonedOrder->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED)->shouldBeCalled();
        $clonedOrder->setCheckoutCompletedAt(Argument::any())->shouldBeCalled();
        $clonedOrder->setCustomer($customer)->shouldBeCalled();
        $clonedOrder->setCurrencyCode('EUR')->shouldBeCalled();
        $clonedOrder->setCustomerIp('127.0.0.0')->shouldBeCalled();
        $clonedOrder->setLocaleCode('en_US')->shouldBeCalled();
        $clonedOrder->setPaymentState(OrderPaymentStates::STATE_AWAITING_PAYMENT)->shouldBeCalled();
        $clonedOrder->setPromotionCoupon($coupon)->shouldBeCalled();
        $clonedOrder->setShippingAddress($address)->shouldBeCalled();
        $clonedOrder->setBillingAddress($billingAddress)->shouldBeCalled();
        $clonedOrder->setShippingState(OrderShippingStates::STATE_READY)->shouldBeCalled();
        $clonedOrder->setTokenValue('')->shouldBeCalled();

        $shipment->getAdjustments()->willReturn(new ArrayCollection([
            $adjustment->getWrappedObject()
        ]));
        $clonedOrder->getItemUnits()->willReturn(new ArrayCollection([
            $itemUnit->getWrappedObject()
        ]));

        $orderItemCloner->clone($orderItem, $clonedOrder)->willReturn($clonedItem);
        $clonedOrder->addItem($clonedItem);

        $adjustment->getType()->willReturn('test_type');

        $adjustmentCloner->clone($adjustment->getWrappedObject())->willReturn($clonedOrderAdjustment);
        $clonedOrder->addAdjustment($clonedOrderAdjustment);
        $clonedOrder->isShippingRequired()->willReturn(false);
        $clonedOrder->recalculateAdjustmentsTotal()->shouldBeCalled();
        $clonedOrder->recalculateItemsTotal()->shouldBeCalled();

        $this->clone($subscription, $order, $orderItem)->shouldReturn($clonedOrder);

    }
}
