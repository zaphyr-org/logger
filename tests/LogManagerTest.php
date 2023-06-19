<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests;

use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Exceptions\LoggerException;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Logger\LogManager;
use Zaphyr\Utils\File;

class LogManagerTest extends TestCase
{
    /**
     * @var string
     */
    protected string $tempLogDir = __DIR__ . '/log';

    public function setUp(): void
    {
        File::createDirectory($this->tempLogDir);
    }

    public function tearDown(): void
    {
        File::deleteDirectory($this->tempLogDir);
    }

    /* -------------------------------------------------
     * LOGGER
     * -------------------------------------------------
     */

    public function testLoggerWithDefaultLogger(): void
    {
        $handlers = [new FileHandler($this->tempLogDir . '/app.log')];
        $logManager = new LogManager('app', ['app' => $handlers]);

        self::assertSame($handlers, $logManager->logger()->getHandlers());
    }

    public function testLoggerWithNonDefaultLogger(): void
    {
        $appHandlers = [new RotateHandler($this->tempLogDir, RotateHandler::INTERVAL_DAY)];
        $debugHandlers = [new FileHandler($this->tempLogDir . '/debug.log')];

        $logManager = new LogManager(
            'app', [
                'app' => $appHandlers,
                'debug' => $debugHandlers,
            ]
        );

        self::assertSame($debugHandlers, $logManager->logger('debug')->getHandlers());
    }

    /* -------------------------------------------------
     * EXCEPTIONS
     * -------------------------------------------------
     */

    public function testLoggerThrowsExceptionWhenNoValidHandlerIsConfigured(): void
    {
        $this->expectException(LoggerException::class);

        $logManager = new LogManager('app', ['app' => []]);

        $logManager->logger('app');
    }

    public function testLoggerThrowsExceptionWhenLoggerIsNotConfigured(): void
    {
        $this->expectException(LoggerException::class);

        (new LogManager('app', []))->logger('nope');
    }
}
