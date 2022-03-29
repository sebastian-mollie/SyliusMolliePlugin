<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Entity;

class MollieSubscriptionSchedule implements MollieSubscriptionScheduleInterface
{
    protected ?int $id = null;

    protected MollieSubscriptionInterface $mollieSubscription;

    protected \DateTime $scheduledDate;

    protected ?\DateTimeInterface $fulfilledDate = null;

    protected int $scheduleIndex = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMollieSubscription(): MollieSubscriptionInterface
    {
        return $this->mollieSubscription;
    }

    public function getScheduledDate(): \DateTime
    {
        return $this->scheduledDate;
    }

    public function getFulfilledDate(): ?\DateTimeInterface
    {
        return $this->fulfilledDate;
    }

    public function setMollieSubscription(MollieSubscriptionInterface $mollieSubscription): void
    {
        $this->mollieSubscription = $mollieSubscription;
    }

    public function setScheduledDate(\DateTime $scheduledDate): void
    {
        $this->scheduledDate = $scheduledDate;
    }

    public function setFulfilledDate(?\DateTimeInterface $fulfilledDate): void
    {
        $this->fulfilledDate = $fulfilledDate;
    }

    public function getScheduleIndex(): int
    {
        return $this->scheduleIndex;
    }

    public function setScheduleIndex(int $scheduleIndex): void
    {
        $this->scheduleIndex = $scheduleIndex;
    }

    public function isFulfilled(): bool
    {
        return null !== $this->fulfilledDate;
    }
}
