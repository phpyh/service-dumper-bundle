<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceFinder;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BasicServiceFinder::class)]
final class BasicServiceFinderTest extends TestCase
{
    public function testItFindsByFullMatch(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(['a', 'й'], 'й');

        self::assertSame(['й'], $foundId);
    }

    public function testItFindsNothing(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(['a', 'й'], 'c');

        self::assertSame([], $foundId);
    }

    public function testItFindsAllByPartialMatch(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(['a', 'й', 'aйc'], 'й');

        self::assertSame(['й', 'aйc'], $foundId);
    }

    public function testItFindsAllByPartialMatchCaseInsensitive(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(['A', 'Й', 'AЙC'], 'й');

        self::assertSame(['Й', 'AЙC'], $foundId);
    }

    public function testItFindsAllByPartialMatchCaseInsensitiveCaps(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(['a', 'й', 'aйc'], 'Й');

        self::assertSame(['й', 'aйc'], $foundId);
    }

    public function testItLimitsResultsTo20ByDefault(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(range('a', 'z'), '');

        self::assertSame(range('a', 't'), $foundId);
    }

    public function testItLimitsResults(): void
    {
        $finder = new BasicServiceFinder(maxResults: 1);

        $foundId = $finder->find(range('a', 'z'), '');

        self::assertSame(['a'], $foundId);
    }

    public function testItReturnsAllIdsForEmptySearch(): void
    {
        $finder = new BasicServiceFinder();

        $foundId = $finder->find(['a', 'й', 'aйc'], '');

        self::assertSame(['a', 'й', 'aйc'], $foundId);
    }
}
