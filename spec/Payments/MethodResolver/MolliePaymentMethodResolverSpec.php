<?php
/*
    This file was created by developers working at BitBag
    Do you need more information about us and what we do? Visit our   website!
    We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/
declare(strict_types=1);

namespace spec\BitBag\SyliusMolliePlugin\Payments\MethodResolver;

use BitBag\SyliusMolliePlugin\Entity\OrderInterface;
use BitBag\SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use BitBag\SyliusMolliePlugin\Payments\MethodResolver\MollieMethodFilterInterface;
use BitBag\SyliusMolliePlugin\Payments\MethodResolver\MolliePaymentMethodResolver;
use BitBag\SyliusMolliePlugin\Resolver\MollieFactoryNameResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentInterface as CorePaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use BitBag\SyliusMolliePlugin\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class MolliePaymentMethodResolverSpec extends ObjectBehavior
{
    function let(
        PaymentMethodsResolverInterface $decoratedService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        MollieFactoryNameResolverInterface $factoryNameResolver,
        MollieMethodFilterInterface $mollieMethodFilter
    ): void
    {
        $this->beConstructedWith(
            $decoratedService,
            $paymentMethodRepository,
            $factoryNameResolver,
            $mollieMethodFilter
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(MolliePaymentMethodResolver::class);
    }

    function it_should_implement_payment_methods_resolver_interface(): void
    {
        $this->shouldImplement(PaymentMethodsResolverInterface::class);
    }

    function it_gets_supported_methods_when_method_not_null(
        CorePaymentInterface $subject,
        OrderInterface $order,
        ChannelInterface $channel,
        MollieFactoryNameResolverInterface $factoryNameResolver,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $method
    ): void {
        $subject->getOrder()->willReturn($order);
        $order->getChannel()->willReturn($channel);
        $factoryName = 'mollie_subscription';
        $factoryNameResolver->resolve($order)->willReturn($factoryName);

        $paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            $factoryName
        )->willReturn($method);

        $this->getSupportedMethods($subject)->shouldReturn([$method]);
    }

    function it_gets_supported_methods_and_has_not_recurring_contents(
        PaymentMethodsResolverInterface $decoratedService,
        CorePaymentInterface $subject,
        OrderInterface $order,
        ChannelInterface $channel,
        MollieFactoryNameResolverInterface $factoryNameResolver,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $method,
        PaymentMethodInterface $parentMethod,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        MollieMethodFilterInterface $mollieMethodFilter
    ): void
    {
        $subject->getOrder()->willReturn($order);
        $order->getChannel()->willReturn($channel);

        $factoryName = 'not_mollie_subscription';
        $factoryNameResolver->resolve($order)->willReturn($factoryName);

        $paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            $factoryName
        )->willReturn($method);

        $decoratedService->getSupportedMethods($subject)->willReturn([$parentMethod]);

        $order->hasRecurringContents()->willReturn(false);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('not_mollie_subscription');

        $mollieMethodFilter->nonRecurringFilter([$parentMethod])->willReturn([$parentMethod]);

        $this->getSupportedMethods($subject)->shouldReturn([$parentMethod]);
    }

    function it_gets_supported_methods_and_has_recurring_contents(
        PaymentMethodsResolverInterface $decoratedService,
        CorePaymentInterface $subject,
        OrderInterface $order,
        ChannelInterface $channel,
        MollieFactoryNameResolverInterface $factoryNameResolver,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $method,
        PaymentMethodInterface $parentMethod,
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig,
        MollieMethodFilterInterface $mollieMethodFilter
    ): void
    {
        $subject->getOrder()->willReturn($order);
        $order->getChannel()->willReturn($channel);

        $factoryName = 'not_mollie_subscription';
        $factoryNameResolver->resolve($order)->willReturn($factoryName);

        $paymentMethodRepository->findOneByChannelAndGatewayFactoryName(
            $channel,
            $factoryName
        )->willReturn($method);

        $decoratedService->getSupportedMethods($subject)->willReturn([$parentMethod]);

        $order->hasRecurringContents()->willReturn(true);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $gatewayConfig->getFactoryName()->willReturn('not_mollie_subscription');

        $mollieMethodFilter->recurringFilter([$parentMethod])->willReturn([$parentMethod]);

        $this->getSupportedMethods($subject)->shouldReturn([$parentMethod]);
    }

    function it_supports(
        CorePaymentInterface $subject,
        OrderInterface $order,
        ChannelInterface $channel
    ): void {
        $subject->getOrder()->willReturn($order);

        $order->hasRecurringContents()->willReturn(true);
        $order->hasNonRecurringContents()->willReturn(false);
        $order->getChannel()->willReturn($channel);

        $this->supports($subject)->shouldReturn(true);

        $order->hasRecurringContents()->willReturn(false);
        $order->hasNonRecurringContents()->willReturn(true);
        $order->getChannel()->willReturn($channel);

        $this->supports($subject)->shouldReturn(true);

        $order->hasRecurringContents()->willReturn(false);
        $order->hasNonRecurringContents()->willReturn(false);
        $order->getChannel()->willReturn($channel);

        $this->supports($subject)->shouldReturn(false);

        $order->hasRecurringContents()->willReturn(false);
        $order->hasNonRecurringContents()->willReturn(true);
        $order->getChannel()->willReturn(null);

        $this->supports($subject)->shouldReturn(false);

    }
}
