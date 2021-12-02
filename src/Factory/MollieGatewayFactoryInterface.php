<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Factory;

interface MollieGatewayFactoryInterface
{
    /** @deprecated since 3.7.0 use MollieGatewayFactoryInterface::LOCALES_AVAILABLE_MAP instead */
    public const LOCALES_AVAILABLE = [
        'en_US',
        'nl_NL',
        'nl_BE',
        'fr_FR',
        'fr_BE',
        'de_DE',
        'de_AT',
        'de_CH',
        'es_ES',
        'ca_ES',
        'pt_PT',
        'it_IT',
        'nb_NO',
        'sv_SE',
        'fi_FI',
        'da_DK',
        'is_IS',
        'hu_HU',
        'pl_PL',
        'lv_LV',
        'lt_LT',
    ];

    public const LOCALES_AVAILABLE_MAP = [
        'en_US' => 'en_US',
        'nl_NL' => 'nl_NL',
        'nl_BE' => 'nl_BE',
        'fr_FR' => 'fr_FR',
        'fr_BE' => 'fr_BE',
        'de_DE' => 'de_DE',
        'de_AT' => 'de_AT',
        'de_CH' => 'de_CH',
        'es_ES' => 'es_ES',
        'ca_ES' => 'ca_ES',
        'pt_PT' => 'pt_PT',
        'it_IT' => 'it_IT',
        'nb_NO' => 'nb_NO',
        'sv_SE' => 'sv_SE',
        'fi_FI' => 'fi_FI',
        'da_DK' => 'da_DK',
        'is_IS' => 'is_IS',
        'hu_HU' => 'hu_HU',
        'pl_PL' => 'pl_PL',
        'lv_LV' => 'lv_LV',
        'lt_LT' => 'lt_LT',
        'en' => 'en_US',
        'nl' => 'nl_NL',
        'fr' => 'fr_FR',
        'de' => 'de_DE',
        'es' => 'es_ES',
        'ca' => 'ca_ES',
        'pt' => 'pt_PT',
        'it' => 'it_IT',
        'sv' => 'sv_SE',
        'fi' => 'fi_FI',
        'da' => 'da_DK',
        'is' => 'is_IS',
        'hu' => 'hu_HU',
        'pl' => 'pl_PL',
        'lv' => 'lv_LV',
        'lt' => 'lt_LT',
    ];
}
