<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Helper;

use BitBag\SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use BitBag\SyliusMolliePlugin\Payments\PaymentTerms\Options;
use Mollie\Api\Types\PaymentMethod;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class PaymentDescription implements PaymentDescriptionInterface
{
    /** @var PaymentDescriptionProviderInterface */
    private $paymentDescriptionProvider;

    public function __construct(PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
        $this->paymentDescriptionProvider = $paymentDescriptionProvider;
    }

    public function getPaymentDescription(
        PaymentInterface $payment,
        MollieGatewayConfigInterface $methodConfig,
        OrderInterface $order
    ): string {
        $paymentMethodType = array_search($methodConfig->getPaymentType(), Options::getAvailablePaymentType(), true);
        $description = $methodConfig->getPaymentDescription();

        if (PaymentMethod::PAYPAL === $methodConfig->getMethodId()) {
            Assert::notNull($order->getNumber());

            return $this->createPayPalDescription($order->getNumber());
        }

        if (Options::PAYMENT_API === $paymentMethodType
            && isset($description)
            && '' !== $description
        ) {
            Assert::notNull($order->getChannel());
            $replacements = [
                '{ordernumber}' => $order->getNumber(),
                '{storename}' => $order->getChannel()->getName(),
            ];

            Assert::notNull($methodConfig->getPaymentDescription());

            return str_replace(
                array_keys($replacements),
                array_values($replacements),
                $methodConfig->getPaymentDescription()
            );
        }

        return $this->paymentDescriptionProvider->getPaymentDescription($payment);
    }

    private function createPayPalDescription(string $orderNumber): string
    {
        return sprintf('%s %s', self::PAYPAL_DESCRIPTION, $orderNumber);
    }
}
