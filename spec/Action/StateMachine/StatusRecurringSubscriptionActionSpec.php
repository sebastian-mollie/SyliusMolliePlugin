<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusMolliePlugin\Action\StateMachine;

use BitBag\SyliusMolliePlugin\Action\Api\BaseApiAwareAction;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use BitBag\SyliusMolliePlugin\Action\StateMachine\StatusRecurringSubscriptionAction;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use BitBag\SyliusMolliePlugin\Client\MollieApiClient;
use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use BitBag\SyliusMolliePlugin\Request\Api\CancelRecurringSubscription;
use BitBag\SyliusMolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use BitBag\SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use BitBag\SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;
use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Endpoints\CustomerEndpoint;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\SubscriptionStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use PhpSpec\ObjectBehavior;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusRecurringSubscriptionActionSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $subscriptionManager,
        SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator,
        SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $this->beConstructedWith(
            $subscriptionManager,
            $subscriptionAndPaymentIdApplicator,
            $subscriptionAndSyliusPaymentApplicator,
            $stateMachineTransition
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(StatusRecurringSubscriptionAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_extends_base_api_aware(): void
    {
        $this->shouldHaveType(BaseApiAwareAction::class);
    }

    function it_executes_and_aborts(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription,
        EntityManagerInterface $subscriptionManager,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $request->getModel()->willReturn($subscription);

        $request->getPaymentId()->willReturn(null);
        $request->getPayment()->willReturn(null);

        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_COMPLETE
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ABORT
        )->shouldBeCalled();

        $subscriptionManager->persist($subscription)->shouldBeCalled();
        $subscriptionManager->flush()->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_with_payment_id_not_null(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription,
        EntityManagerInterface $subscriptionManager,
        StateMachineTransitionInterface $stateMachineTransition,
        SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator
    ): void {
        $request->getModel()->willReturn($subscription);

        $request->getPaymentId()->willReturn('payment_id');
        $request->getPayment()->willReturn(null);

        $subscriptionAndPaymentIdApplicator->execute($subscription, 'payment_id')->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_COMPLETE
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ABORT
        )->shouldBeCalled();

        $subscriptionManager->persist($subscription)->shouldBeCalled();
        $subscriptionManager->flush()->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_with_sylius_payment_not_null(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription,
        EntityManagerInterface $subscriptionManager,
        StateMachineTransitionInterface $stateMachineTransition,
        SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        PaymentInterface $payment
    ): void {
        $request->getModel()->willReturn($subscription);

        $request->getPaymentId()->willReturn(null);
        $request->getPayment()->willReturn($payment);

        $subscriptionAndSyliusPaymentApplicator->execute($subscription, $payment)->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_COMPLETE
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ABORT
        )->shouldBeCalled();

        $subscriptionManager->persist($subscription)->shouldBeCalled();
        $subscriptionManager->flush()->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_status_recurring_subscription_request_and_subscription_model(
        StatusRecurringSubscription $request,
        MollieSubscriptionInterface $subscription
    ): void {
        $request->getModel()->willReturn($subscription);

        $this->supports($request)->shouldReturn(true);
    }
}
