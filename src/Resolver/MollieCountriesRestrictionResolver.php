<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Resolver;

use BitBag\SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use BitBag\SyliusMolliePlugin\Entity\MollieGatewayConfigTranslationInterface;

final class MollieCountriesRestrictionResolver implements MollieCountriesRestrictionResolverInterface
{
    /** @var MolliePaymentMethodImageResolverInterface */
    private $imageResolver;

    public function __construct(MolliePaymentMethodImageResolverInterface $imageResolver)
    {
        $this->imageResolver = $imageResolver;
    }

    public function resolve(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode
    ): ?array {
        if (MollieGatewayConfigInterface::ALL_COUNTRIES === $paymentMethod->getCountryRestriction()) {
            return $this->excludeCountryLevel($paymentMethod, $methods, $countryCode);
        }
        if (MollieGatewayConfigInterface::SELECTED_COUNTRIES === $paymentMethod->getCountryRestriction()) {
            return $this->allowCountryLevel($paymentMethod, $methods, $countryCode);
        }

        return $methods;
    }

    private function allowCountryLevel(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode
    ): array {
        if (is_array($paymentMethod->getCountryLevelAllowed()) &&
            in_array($countryCode, $paymentMethod->getCountryLevelAllowed(), true)) {
            return $this->setData($methods, $paymentMethod);
        }

        return $methods;
    }

    private function excludeCountryLevel(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode
    ): array {
        if (is_array($paymentMethod->getCountryLevelExcluded()) &&
            in_array($countryCode, $paymentMethod->getCountryLevelExcluded(), true)) {
            return $methods;
        }

        return $this->setData($methods, $paymentMethod);
    }

    private function setData(array $methods, MollieGatewayConfigInterface $paymentMethod): array
    {
        /** @var MollieGatewayConfigTranslationInterface $translation */
        $translation = $paymentMethod->getTranslation();
        $methods['data'][$translation->getName() ?? $paymentMethod->getName()] = $paymentMethod->getMethodId();
        $methods['image'][$paymentMethod->getMethodId()] = $this->imageResolver->resolve($paymentMethod);
        $methods['issuers'][$paymentMethod->getMethodId()] = $paymentMethod->getIssuers();
        $methods['paymentFee'][$paymentMethod->getMethodId()] = null !== $paymentMethod->getPaymentSurchargeFee() ? $paymentMethod->getPaymentSurchargeFee() : [];

        return $methods;
    }
}
