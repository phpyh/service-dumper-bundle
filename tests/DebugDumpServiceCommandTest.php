<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \PHPyh\ServiceDumperBundle\DebugDumpServiceCommand
 */
final class DebugDumpServiceCommandTest extends TestCase
{
    public function testItThrowsIfNoIdPassedInNonInteractiveMode(): void
    {
        $tester = new CommandTester(new DebugDumpServiceCommand());

        $this->expectExceptionObject(new RuntimeException('Not enough arguments (missing: "ids").'));

        $tester->execute([], ['interactive' => false]);
    }
}
