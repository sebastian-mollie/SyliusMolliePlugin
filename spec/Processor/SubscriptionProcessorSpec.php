<?php
/*
    This file was created by developers working at BitBag
    Do you need more information about us and what we do? Visit our   website!
    We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/
declare(strict_types=1);

namespace spec\BitBag\SyliusMolliePlugin\Processor;

use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use BitBag\SyliusMolliePlugin\Entity\OrderInterface;
use BitBag\SyliusMolliePlugin\Factory\PaymentDetailsFactoryInterface;
use BitBag\SyliusMolliePlugin\Order\SubscriptionOrderClonerInterface;
use BitBag\SyliusMolliePlugin\Processor\SubscriptionProcessor;
use BitBag\SyliusMolliePlugin\Processor\SubscriptionProcessorInterface;
use BitBag\SyliusMolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use BitBag\SyliusMolliePlugin\Repository\OrderRepositoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusCorePayment;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;


final class SubscriptionProcessorSpec extends ObjectBehavior
{
    function let(
        SubscriptionOrderClonerInterface $orderCloner,
        PaymentFactoryInterface $paymentFactory,
        OrderRepositoryInterface $orderRepository,
        PaymentDetailsFactoryInterface $paymentDetailsFactory,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        Payum $paymentRegistry
    ): void {
        $this->beConstructedWith(
            $orderCloner,
            $paymentFactory,
            $orderRepository,
            $paymentDetailsFactory,
            $subscriptionRepository,
            $paymentRegistry
        );
    }
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionProcessor::class);
    }

    function it_should_implement_subscription_processor_interface(): void
    {
        $this->shouldImplement(SubscriptionProcessorInterface::class);
    }

    function it_process_next_payment(
        MollieSubscriptionInterface $subscription,
        OrderItemInterface $orderItem,
        OrderInterface $order,
        OrderInterface $clonedOrder,
        SubscriptionOrderClonerInterface $orderCloner,
        SyliusCorePayment $payment,
        SyliusCorePayment $lastPayment,
        PaymentFactoryInterface $paymentFactory,
        OrderInterface $firstOrder,
        PaymentDetailsFactoryInterface $paymentDetailsFactory,
        MollieSubscriptionConfigurationInterface $configuration,
        OrderRepositoryInterface $orderRepository,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        Payum $paymentRegistry,
        GatewayInterface $gateway,
        GenericTokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        PaymentMethodInterface $method
    ): void
    {
        $subscription->getOrderItem()->willReturn($orderItem);
        $subscription->getFirstOrder()->willReturn($order, $firstOrder);
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $orderCloner->clone(
            $subscription,
            $order,
            $orderItem
        )->willReturn($clonedOrder);

        $clonedOrder->getTotal()->willReturn(2);
        $clonedOrder->getCurrencyCode()->willReturn('EUR');

        $paymentFactory->createWithAmountAndCurrencyCode(
            2,
            'EUR'
        )->willReturn($payment);

        $firstOrder->getLastPayment(PaymentInterface::STATE_COMPLETED)->willReturn($lastPayment);

        $methodData = [
            'metadata' => [
                'molliePaymentMethods' => 'test_method'
            ]
        ];
        $lastPayment->getDetails()->willReturn($methodData);
        $lastPayment->getMethod()->willReturn($method);
        $payment->setMethod($method);
        $payment->setState(PaymentInterface::STATE_NEW);

        $details = [
            'metadata' => [
                'gateway' => 'gateway_id'
            ]
        ];

        $paymentDetailsFactory->createForSubscriptionAndOrder(
            $configuration,
            $clonedOrder
        )->willReturn($details);

        $payment->setDetails($details);
        $payment->getDetails()->willReturn($details);

        $clonedOrder->addPayment($payment);
        $orderRepository->add($clonedOrder);

        $subscription->addOrder($clonedOrder);
        $subscription->addPayment($payment);
        $subscriptionRepository->add($subscription);

        $paymentRegistry->getGateway('gateway_id')->willReturn($gateway);
        $paymentRegistry->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory->createToken(
            'gateway_id',
            $payment,
            'sylius_shop_order_thank_you'
        )->willReturn($token);

        $gateway->execute(new Capture($token->getWrappedObject()))->shouldBeCalled();
        $this->processNextPayment($subscription);
    }

    function it_process_next_subscription_payment(
        MollieSubscriptionInterface $subscription,
        OrderItemInterface $orderItem,
        OrderInterface $order,
        OrderInterface $clonedOrder,
        SubscriptionOrderClonerInterface $orderCloner,
        SyliusCorePayment $payment,
        SyliusCorePayment $lastPayment,
        PaymentFactoryInterface $paymentFactory,
        OrderInterface $firstOrder,
        PaymentDetailsFactoryInterface $paymentDetailsFactory,
        MollieSubscriptionConfigurationInterface $configuration,
        OrderRepositoryInterface $orderRepository,
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        Payum $paymentRegistry,
        GatewayInterface $gateway,
        GenericTokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        PaymentMethodInterface $method
    ): void
    {
        $subscription->getOrderItem()->willReturn($orderItem);
        $subscription->getFirstOrder()->willReturn($order, $firstOrder);
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $orderCloner->clone(
            $subscription,
            $order,
            $orderItem
        )->willReturn($clonedOrder);

        $clonedOrder->getTotal()->willReturn(2);
        $clonedOrder->getCurrencyCode()->willReturn('EUR');

        $paymentFactory->createWithAmountAndCurrencyCode(
            2,
            'EUR'
        )->willReturn($payment);

        $firstOrder->getLastPayment(PaymentInterface::STATE_COMPLETED)->willReturn($lastPayment);

        $methodData = [
            'metadata' => [
                'molliePaymentMethods' => 'test_method'
            ]
        ];
        $lastPayment->getDetails()->willReturn($methodData);
        $lastPayment->getMethod()->willReturn($method);
        $payment->setMethod($method);
        $payment->setState(PaymentInterface::STATE_NEW);

        $details = [
            'metadata' => [
                'gateway' => 'gateway_id'
            ]
        ];

        $paymentDetailsFactory->createForSubscriptionAndOrder(
            $configuration,
            $clonedOrder
        )->willReturn($details);

        $payment->setDetails($details);
        $payment->getDetails()->willReturn($details);

        $clonedOrder->addPayment($payment);
        $orderRepository->add($clonedOrder);

        $subscription->addOrder($clonedOrder);
        $subscription->addPayment($payment);
        $subscriptionRepository->add($subscription);

        $paymentRegistry->getGateway('gateway_id')->willReturn($gateway);
        $paymentRegistry->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory->createToken(
            'gateway_id',
            $payment,
            'sylius_shop_order_thank_you'
        )->willReturn($token);

        $this->processNextSubscriptionPayment($subscription);
    }
}
