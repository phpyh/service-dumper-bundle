<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle\ServiceDumper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VarDumpServiceDumper::class)]
final class VarDumpServiceDumperTest extends TestCase
{
    public function testItDumpsIdsAndObjects(): void
    {
        $dumper = new VarDumpServiceDumper();
        $servicesById = [
            'service_a_id' => new \ArrayObject(['Something inside service a.']),
            'service_b_id' => new \ArrayObject(['Something inside service b.']),
        ];

        ob_start();
        $dumper->dump($servicesById);
        $output = ob_get_clean();

        self::assertStringContainsString('service_a_id', $output);
        self::assertStringContainsString('Something inside service a.', $output);
        self::assertStringContainsString('service_b_id', $output);
        self::assertStringContainsString('Something inside service b.', $output);
    }
}
