<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Grid\Filter;

use Malina141\SyliusWishlistPlugin\Grid\Filter\ExistsFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\ExpressionBuilderInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

final class ExistsFilterTest extends TestCase
{
    private DataSourceInterface&MockObject $dataSource;

    private ExpressionBuilderInterface&MockObject $expressionBuilder;

    private ExistsFilter $sut;

    protected function setUp(): void
    {
        $this->expressionBuilder = $this->createMock(ExpressionBuilderInterface::class);
        $this->dataSource = $this->createMock(DataSourceInterface::class);
        $this->dataSource->method('getExpressionBuilder')->willReturn($this->expressionBuilder);
        $this->sut = new ExistsFilter();
    }

    #[Test]
    public function it_implements_filter_interface(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->sut);
    }

    #[Test]
    #[DataProvider('emptyDataProvider')]
    public function it_does_nothing_when_data_is_empty(mixed $data): void
    {
        $this->dataSource
            ->expects($this->never())
            ->method('restrict');

        $this->sut->apply($this->dataSource, 'field', $data, []);
    }

    public static function emptyDataProvider(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
    }

    #[Test]
    #[DataProvider('truthyDataProvider')]
    public function it_applies_is_not_null_expression_when_data_is_truthy(mixed $data): void
    {
        $expression = new \stdClass();

        $this->expressionBuilder
            ->expects($this->once())
            ->method('isNotNull')
            ->with('shareToken')
            ->willReturn($expression);

        $this->dataSource
            ->expects($this->once())
            ->method('restrict')
            ->with($expression);

        $this->sut->apply($this->dataSource, 'shareToken', $data, []);
    }

    public static function truthyDataProvider(): iterable
    {
        yield 'boolean true' => [true];
        yield 'string 1' => ['1'];
        yield 'integer 1' => [1];
    }

    #[Test]
    #[DataProvider('falsyDataProvider')]
    public function it_applies_is_null_expression_when_data_is_falsy(mixed $data): void
    {
        $expression = new \stdClass();

        $this->expressionBuilder
            ->expects($this->once())
            ->method('isNull')
            ->with('shareToken')
            ->willReturn($expression);

        $this->dataSource
            ->expects($this->once())
            ->method('restrict')
            ->with($expression);

        $this->sut->apply($this->dataSource, 'shareToken', $data, []);
    }

    public static function falsyDataProvider(): iterable
    {
        yield 'boolean false' => [false];
        yield 'string 0' => ['0'];
        yield 'integer 0' => [0];
    }

    #[Test]
    public function it_uses_field_option_when_provided(): void
    {
        $expression = new \stdClass();

        $this->expressionBuilder
            ->expects($this->once())
            ->method('isNotNull')
            ->with('customField')
            ->willReturn($expression);

        $this->sut->apply($this->dataSource, 'name', true, ['field' => 'customField']);
    }

    #[Test]
    public function it_falls_back_to_name_when_field_option_is_missing(): void
    {
        $expression = new \stdClass();

        $this->expressionBuilder
            ->expects($this->once())
            ->method('isNotNull')
            ->with('fallbackName')
            ->willReturn($expression);

        $this->sut->apply($this->dataSource, 'fallbackName', true, []);
    }
}
