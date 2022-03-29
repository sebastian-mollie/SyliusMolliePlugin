<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Factory;

use BitBag\SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use BitBag\SyliusMolliePlugin\Entity\OrderInterface;
use BitBag\SyliusMolliePlugin\Entity\ProductVariantInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

final class MollieSubscriptionFactory implements MollieSubscriptionFactoryInterface
{
    private FactoryInterface $decoratedFactory;

    private RouterInterface $router;

    public function __construct(
        FactoryInterface $decoratedFactory,
        RouterInterface $router
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->router = $router;
    }

    public function createNew(): MollieSubscriptionInterface
    {
        /** @var MollieSubscriptionInterface $subscriptionTemplate */
        $subscriptionTemplate = $this->decoratedFactory->createNew();

        return $subscriptionTemplate;
    }

    public function createFromFirstOrder(OrderInterface $order): MollieSubscriptionInterface
    {
        $subscriptionTemplate = $this->createNew();

        Assert::notNull($order->getCustomer());
        $subscriptionTemplate->setCustomer($order->getCustomer());
        $subscriptionTemplate->addOrder($order);

        /** @var PaymentInterface $payment */
        foreach ($order->getPayments() as $payment) {
            $subscriptionTemplate->addPayment($payment);
        }

        return $subscriptionTemplate;
    }

    public function createFromFirstOrderWithOrderItemAndPaymentConfiguration(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        array $paymentConfiguration = [],
        string $mandateId = null
    ): MollieSubscriptionInterface {
        $variant = $orderItem->getVariant();
        if (false === $variant instanceof ProductVariantInterface) {
            throw new \InvalidArgumentException(
                sprintf('Variant should be instance of "%s::class".', ProductVariantInterface::class)
            );
        }
        $routerContext = $this->router->getContext();
        $hostname = $routerContext->getHost();

        $subscriptionTemplate = $this->createFromFirstOrder($order);
        $configuration = $subscriptionTemplate->getSubscriptionConfiguration();

        Assert::notNull($variant->getInterval());
        Assert::notNull($variant->getTimes());
        $configuration->setInterval($variant->getInterval());
        $configuration->setNumberOfRepetitions($variant->getTimes());
        $configuration->setPaymentDetailsConfiguration($paymentConfiguration);
        $configuration->setMandateId($mandateId);
        $configuration->setHostName($hostname);
        $subscriptionTemplate->setOrderItem($orderItem);

        return $subscriptionTemplate;
    }
}
