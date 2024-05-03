<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Handlers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Handlers\NoopHandler;

class NoopHandlerTest extends TestCase
{
    /* -------------------------------------------------
     * ADD
     * -------------------------------------------------
     */

    public function testAdd(): void
    {
        self::assertNull((new NoopHandler())->add('app', 'INFO', 'This is a test log'));
    }
}
