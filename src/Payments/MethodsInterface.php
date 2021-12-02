<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Payments;

use BitBag\SyliusMolliePlugin\Payments\Methods\ApplePay;
use BitBag\SyliusMolliePlugin\Payments\Methods\Bancontact;
use BitBag\SyliusMolliePlugin\Payments\Methods\BankTransfer;
use BitBag\SyliusMolliePlugin\Payments\Methods\Belfius;
use BitBag\SyliusMolliePlugin\Payments\Methods\CreditCard;
use BitBag\SyliusMolliePlugin\Payments\Methods\Eps;
use BitBag\SyliusMolliePlugin\Payments\Methods\GiftCard;
use BitBag\SyliusMolliePlugin\Payments\Methods\Giropay;
use BitBag\SyliusMolliePlugin\Payments\Methods\Ideal;
use BitBag\SyliusMolliePlugin\Payments\Methods\Kbc;
use BitBag\SyliusMolliePlugin\Payments\Methods\Klarnapaylater;
use BitBag\SyliusMolliePlugin\Payments\Methods\KlarnaPayNow;
use BitBag\SyliusMolliePlugin\Payments\Methods\Klarnasliceit;
use BitBag\SyliusMolliePlugin\Payments\Methods\MealVoucher;
use BitBag\SyliusMolliePlugin\Payments\Methods\MyBank;
use BitBag\SyliusMolliePlugin\Payments\Methods\PayPal;
use BitBag\SyliusMolliePlugin\Payments\Methods\Przelewy24;
use BitBag\SyliusMolliePlugin\Payments\Methods\SofortBanking;

interface MethodsInterface
{
    public const GATEWAYS = [
        ApplePay::class,
        Bancontact::class,
        BankTransfer::class,
        Belfius::class,
        CreditCard::class,
        Eps::class,
        GiftCard::class,
        Giropay::class,
        Ideal::class,
        Kbc::class,
        Klarnapaylater::class,
        Klarnasliceit::class,
        KlarnaPayNow::class,
        MyBank::class,
        PayPal::class,
        Przelewy24::class,
        SofortBanking::class,
        MealVoucher::class,
    ];
}
