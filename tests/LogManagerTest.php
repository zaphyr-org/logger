<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Zaphyr\Logger\Exceptions\LoggerException;
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
        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'file' => [
                            'storagePath' => $this->tempLogDir,
                        ],
                    ],
                ],
            ]
        );

        self::assertInstanceOf(LoggerInterface::class, $logManager->logger());
    }

    public function testLoggerWithNonDefaultLogger(): void
    {
        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'file' => [
                            'storagePath' => $this->tempLogDir,
                        ],
                    ],
                ],
                'debug' => [
                    'handlers' => [
                        'rotate' => [
                            'interval' => 'day',
                            'storagePath' => $this->tempLogDir,
                        ],
                    ],
                ],
            ]
        );

        self::assertInstanceOf(LoggerInterface::class, $logManager->logger('debug'));
    }

    public function testLoggerWithMailHander(): void
    {
        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'mail' => [
                            'dsn' => 'smtp://user:pass@smtp.example.com:25',
                            'from' => 'from@smtp.example.com',
                            'to' => 'to@smtp.example.com',
                            'subject' => 'An error occurred in your application'
                        ],
                    ],
                ],
            ]
        );

        self::assertInstanceOf(LoggerInterface::class, $logManager->logger());
    }

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

    public function testLoggerThrowsExceptionOnInvalidHandler(): void
    {
        $this->expectException(LoggerException::class);

        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'nope' => [],
                    ],
                ],
            ]
        );

        $logManager->logger();
    }

    public function testLoggerThrowsExceptionOnMisconfiguredFileHandler(): void
    {
        $this->expectException(LoggerException::class);

        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'file' => [],
                    ],
                ],
            ]
        );

        $logManager->logger();
    }

    public function testLoggerThrowsExceptionOnMisconfiguredMailHandler(): void
    {
        $this->expectException(LoggerException::class);

        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'mail' => [],
                    ],
                ],
            ]
        );

        $logManager->logger();
    }

    public function testLoggerThrowsExceptionOnMisconfiguredRotateHandler(): void
    {
        $this->expectException(LoggerException::class);

        $logManager = new LogManager(
            'app',
            [
                'app' => [
                    'handlers' => [
                        'rotate' => [],
                    ],
                ],
            ]
        );

        $logManager->logger();
    }
}
