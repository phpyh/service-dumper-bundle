<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;

#[CoversClass(DebugDumpServiceCommand::class)]
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
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(
            serviceIds: $serviceIds,
            serviceFinder: $this->createMock(ServiceFinder::class),
            selectServiceIds: $select,
            inputIds: [],
        );

        self::assertSame(['a'], $resolvedIds);
    }

    public function testItReturnsExactServiceId(): void
    {
        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(
            serviceIds: ['a', 'b'],
            serviceFinder: $this->createMock(ServiceFinder::class),
            selectServiceIds: $this->createMock(InvokableSelectServiceIds::class),
            inputIds: ['a'],
        );

        self::assertSame(['a'], $resolvedIds);
    }

    public function testItThrowsIfNoServiceMatches(): void
    {
        $this->expectExceptionObject(new \RuntimeException('No services matching "c" found.'));

        /** @psalm-suppress InvalidArgument */
        DebugDumpServiceCommand::interactivelyResolveServiceIds(
            serviceIds: ['a', 'b'],
            serviceFinder: $this->createMock(ServiceFinder::class),
            selectServiceIds: $this->createMock(InvokableSelectServiceIds::class),
            inputIds: ['c'],
        );
    }

    public function testItReturnsSingleMatchingServiceId(): void
    {
        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(
            serviceIds: ['abc', 'def'],
            serviceFinder: new BasicServiceFinder(),
            selectServiceIds: $this->createMock(InvokableSelectServiceIds::class),
            inputIds: ['b'],
        );

        self::assertSame(['abc'], $resolvedIds);
    }

    public function testItSelectsFromMultipleMatchingServiceIds(): void
    {
        $select = $this->createMock(InvokableSelectServiceIds::class);
        $select->expects(self::once())->method('__invoke')
            ->with('Select service(s), matching "b"', ['abc', 'bb'])
            ->willReturn(['bb']);

        /** @psalm-suppress InvalidArgument */
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(
            serviceIds: ['abc', 'a', 'bb', 'c'],
            serviceFinder: new BasicServiceFinder(),
            selectServiceIds: $select,
            inputIds: ['b'],
        );

        self::assertSame(['bb'], $resolvedIds);
    }

    public function testItResolvesMultipleInputIds(): void
    {
        $resolvedIds = DebugDumpServiceCommand::interactivelyResolveServiceIds(
            serviceIds: ['abc', 'aa', 'bb', 'cc'],
            serviceFinder: new BasicServiceFinder(),
            selectServiceIds: static fn (): array => ['aa', 'cc'],
            inputIds: ['abc', 'bb', '', 'a'],
        );

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
