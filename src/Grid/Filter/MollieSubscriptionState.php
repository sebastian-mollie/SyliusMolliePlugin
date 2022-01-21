<?php
declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

final class MollieSubscriptionState implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (false === array_key_exists('state', $data) || [] === $data['state']) {
            return;
        }

        $dataSource->restrict($dataSource->getExpressionBuilder()->in('state', $data['state']));
    }
}
