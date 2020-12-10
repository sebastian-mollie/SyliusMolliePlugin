<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Factory;

use BitBag\SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use BitBag\SyliusMolliePlugin\Payments\Methods\MethodInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;

interface MollieGatewayConfigFactoryInterface
{
    public function create(MethodInterface $method, GatewayConfigInterface $gateway): MollieGatewayConfigInterface;
}
