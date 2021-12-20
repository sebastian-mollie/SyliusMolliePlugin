<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Entity;

use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

interface OrderInterface extends BaseOrderInterface
{
    public function isAbandonedEmail(): bool;

    public function setAbandonedEmail(bool $abandonedEmail): void;

    public function isRecurring(): bool;

    public function setRecurring(bool $recurring): void;

    public function getRecurringSequenceIndex(): ?int;

    public function setRecurringSequenceIndex(int $recurringSequenceIndex): void;

    public function getSubscription(): ?MollieSubscriptionInterface;

    public function setSubscription(MollieSubscriptionInterface $subscription): void;
}
