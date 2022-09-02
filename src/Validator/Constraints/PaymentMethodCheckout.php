<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class PaymentMethodCheckout extends Constraint
{
    /** @var string */
    public $message = 'bitbag_sylius_mollie_plugin.credit_cart_error.empty_payment_method_checkout';

    public function validatedBy(): string
    {
        return PaymentMethodCheckoutValidator::class;
    }

    /** @return string[] */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }
}
