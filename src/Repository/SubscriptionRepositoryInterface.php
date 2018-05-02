<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Repository;

use BitBag\SyliusMolliePlugin\Entity\SubscriptionInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface SubscriptionRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $orderId
     *
     * @return SubscriptionInterface|null
     */
    public function findOneByOrderId($orderId): ?SubscriptionInterface;
}