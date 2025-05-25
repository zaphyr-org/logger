<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Handlers;

use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Handlers\NoopHandler;
use Zaphyr\Logger\Level;

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

    /* -------------------------------------------------
     * GET LEVEL
     * -------------------------------------------------
     */

    public function testGetLevel(): void
    {
        $handler = new NoopHandler();

        self::assertSame(Level::DEBUG, $handler->getLevel());
    }

    /* -------------------------------------------------
     * HAS LEVEL
     * -------------------------------------------------
     */

    public function testHasLevel(): void
    {
        $handler = new NoopHandler(level: Level::ERROR);

        self::assertTrue($handler->hasLevel('ERROR'));
        self::assertFalse($handler->hasLevel('DEBUG'));
    }
}
