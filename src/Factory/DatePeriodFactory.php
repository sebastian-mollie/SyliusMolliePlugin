<?php
declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Factory;

use Webmozart\Assert\Assert;

final class DatePeriodFactory implements DatePeriodFactoryInterface
{
    public const READ_PATTERN = '/^(?<intervalValue>\d{1,})\s(?<intervalStep>months|weeks|days)$/';

    public function createForSubscriptionConfiguration(
        \DateTime $start,
        int $times,
        string $interval
    ): array
    {
        Assert::regex($interval, DatePeriodFactory::READ_PATTERN);

        $dates = [
            $start,
        ];

        for ($i = 1; $i < $times; $i++) {
            $dates[] = (clone $dates[$i-1])->modify(sprintf('+%s', $interval));
        }

        return $dates;
    }
}
