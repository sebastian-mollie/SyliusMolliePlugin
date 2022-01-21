<?php
declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface MollieSubscriptionScheduleInterface extends ResourceInterface
{
    public function getMollieSubscription(): MollieSubscriptionInterface;

    public function getScheduledDate(): \DateTime;

    public function getFulfilledDate(): ?\DateTime;

    public function setMollieSubscription(MollieSubscriptionInterface $mollieSubscription): void;

    public function setScheduledDate(\DateTime $scheduledDate): void;

    public function setFulfilledDate(?\DateTime $fulfilledDate): void;

    public function setScheduleIndex(int $scheduleIndex): void;

    public function getScheduleIndex(): int;
}
