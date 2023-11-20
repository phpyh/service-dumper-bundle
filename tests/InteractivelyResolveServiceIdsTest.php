<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPUnit\Framework\TestCase;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;

/**
 * @covers \PHPyh\ServiceDumperBundle\DebugDumpServiceCommand
 */
final class InteractivelyResolveServiceIdsTest extends TestCase
{
    public function testItSelectsFromAllServicesWhenInputEmpty(): void
    {
        $serviceIds = ['a', 'b'];
        $select = $this->createMock(InvokableSelectServiceIds::class);
        $select->expects(self::once())->method('__invoke')
            ->with('Select service(s)', $serviceIds)
            ->willReturn(['a']);

        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds($serviceIds, $this->createMock(ServiceFinder::class), $select, []);

        self::assertSame(['a'], $resolvedIds);
    }

    public function testItReturnsExactServiceId(): void
    {
        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(['a', 'b'], $this->createMock(ServiceFinder::class), $this->createMock(InvokableSelectServiceIds::class), ['a']);

        self::assertSame(['a'], $resolvedIds);
    }

    public function testItThrowsIfNoServiceMatches(): void
    {
        $this->expectExceptionObject(new \RuntimeException('No services matching "c" found.'));

        /** @psalm-suppress InvalidArgument */
        DebugDumpServiceCommand::interactivelyResolveServiceIds(['a', 'b'], $this->createMock(ServiceFinder::class), $this->createMock(InvokableSelectServiceIds::class), ['c']);
    }

    public function testItReturnsSingleMatchingServiceId(): void
    {
        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(['abc', 'def'], new BasicServiceFinder(), $this->createMock(InvokableSelectServiceIds::class), ['b']);

        self::assertSame(['abc'], $resolvedIds);
    }

    public function testItSelectsFromMultipleMatchingServiceIds(): void
    {
        $select = $this->createMock(InvokableSelectServiceIds::class);
        $select->expects(self::once())->method('__invoke')
            ->with('Select service(s), matching "b"', ['abc', 'bb'])
            ->willReturn(['bb']);

        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(['abc', 'a', 'bb', 'c'], new BasicServiceFinder(), $select, ['b']);

        self::assertSame(['bb'], $resolvedIds);
    }

    public function testItResolvesMultipleInputIds(): void
    {
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(['abc', 'aa', 'bb', 'cc'], new BasicServiceFinder(), static function () : array {
            return ['aa', 'cc'];
        }, ['abc', 'bb', '', 'a']);

        self::assertSame(['abc', 'bb', 'aa', 'cc', 'aa', 'cc'], $resolvedIds);
    }
}

interface InvokableSelectServiceIds
{
    /**
     * @param non-empty-list<string> $options
     * @return non-empty-list<string>
     */
    public function __invoke(string $title, array $options): array;
}
