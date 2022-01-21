<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Repository;

use BitBag\SyliusMolliePlugin\Entity\SubscriptionInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/** @deprecated  */
interface SubscriptionRepositoryInterface extends RepositoryInterface
{
    public function findOneByOrderId($orderId): ?SubscriptionInterface;
}
