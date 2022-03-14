<?php
/*
    This file was created by developers working at BitBag
    Do you need more information about us and what we do? Visit our   website!
    We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/
declare(strict_types=1);

namespace spec\BitBag\SyliusMolliePlugin\Action\StateMachine\Applicator;

use BitBag\SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicator;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransition;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Transition\ProcessingStateMachineTransitionInterface;
use BitBag\SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use BitBag\SyliusMolliePlugin\Client\MollieApiClient;
use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use BitBag\SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use BitBag\SyliusMolliePlugin\Transitions\MollieSubscriptionProcessingTransitions;
use BitBag\SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;
use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use PhpSpec\ObjectBehavior;

final class SubscriptionAndPaymentIdApplicatorSpec extends ObjectBehavior
{
    function let(
        MollieApiClient $mollieApiClient,
        StateMachineTransitionInterface $stateMachineTransition,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ): void {
        $this->beConstructedWith(
            $mollieApiClient,
            $stateMachineTransition,
            $paymentStateMachineTransition,
            $processingStateMachineTransition
        );
    }
    function it_is_initializable(): void
    {
        $this->shouldHaveType(SubscriptionAndPaymentIdApplicator::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(SubscriptionAndPaymentIdApplicatorInterface::class);
    }

    function it_executes_status_open(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_OPEN;

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_executes_status_pending(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_PENDING;

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_executes_status_authorized(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_AUTHORIZED;

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_PROCESS
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_executes_status_paid(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition,
        StateMachineTransitionInterface $stateMachineTransition,
        ProcessingStateMachineTransitionInterface $processingStateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = PaymentStatus::STATUS_PAID;

        $subscription->resetFailedPaymentCount()->shouldBeCalled();

        $processingStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionProcessingTransitions::TRANSITION_SCHEDULE
        )->shouldBeCalled();
        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_SUCCESS
        )->shouldBeCalled();
        $stateMachineTransition->apply(
            $subscription,
            MollieSubscriptionTransitions::TRANSITION_ACTIVATE
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }

    function it_executes_and_fail(
        MollieSubscriptionInterface $subscription,
        MollieSubscriptionConfigurationInterface $configuration,
        MollieApiClient $mollieApiClient,
        PaymentEndpoint $paymentEndpoint,
        Payment $payment,
        PaymentStateMachineTransitionInterface $paymentStateMachineTransition
    ): void {
        $subscription->getSubscriptionConfiguration()->willReturn($configuration);
        $mollieApiClient->payments = $paymentEndpoint;
        $paymentEndpoint->get('id_1')->willReturn($payment);
        $configuration->getMandateId()->willReturn(null);
        $configuration->getCustomerId()->willReturn(null);
        $payment->mandateId = 'mandate_id';
        $payment->customerId = 'customer_id';

        $configuration->setMandateId('mandate_id')->shouldBeCalled();
        $configuration->setCustomerId('customer_id')->shouldBeCalled();

        $payment->status = 'definitely not payment status';

        $subscription->incrementFailedPaymentCounter()->shouldBeCalled();

        $paymentStateMachineTransition->apply(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::TRANSITION_FAILURE
        )->shouldBeCalled();

        $this->execute($subscription, 'id_1');
    }
}
