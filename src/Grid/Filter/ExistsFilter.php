<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

final class ExistsFilter implements FilterInterface
{
    public const TRUE = true;

    public const FALSE = false;

    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (null === $data || '' === $data) {
            return;
        }

        /** @var string $field */
        $field = $options['field'] ?? $name;

        if (self::TRUE === (bool) $data) {
            $dataSource->restrict($dataSource->getExpressionBuilder()->isNotNull($field));

            return;
        }

        $dataSource->restrict($dataSource->getExpressionBuilder()->isNull($field));
    }
}
