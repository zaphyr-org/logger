<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Logger;

class LoggerTest extends TestCase
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var HandlerInterface&MockObject
     */
    protected HandlerInterface&MockObject $handlerMock;

    /**
     * @var Logger
     */
    protected Logger $logger;

    protected function setUp(): void
    {
        $this->handlerMock = $this->createMock(HandlerInterface::class);
        $this->logger = new Logger($this->name = 'test', [$this->handlerMock]);
    }

    protected function tearDown(): void
    {
        unset($this->handlerMock, $this->logger);
    }

    /* -------------------------------------------------
     * HANDLER
     * -------------------------------------------------
     */

    public function testGetHandlers(): void
    {
        self::assertEquals([$this->handlerMock], $this->logger->getHandlers());
    }

    public function testSetHandler(): void
    {
        $handler = $this->createMock(HandlerInterface::class);
        $this->logger->setHandler($handler);

        self::assertEquals([$this->handlerMock, $handler], $this->logger->getHandlers());
    }

    public function testSetHandlers(): void
    {
        $handler1 = $this->createMock(HandlerInterface::class);
        $handler2 = $this->createMock(HandlerInterface::class);
        $this->logger->setHandlers([$handler1, $handler2]);

        self::assertEquals([$this->handlerMock, $handler1, $handler2], $this->logger->getHandlers());
    }

    /* -------------------------------------------------
     * LOG
     * -------------------------------------------------
     */

    public function testLog(): void
    {
        $this->handlerMock->expects(self::once())
            ->method('add')
            ->with($this->name, $level = LogLevel::ALERT, $message = 'An error occurred', []);

        $this->logger->log($level, $message);
    }

    /**
     * @dataProvider validLogLevelsDataProvider
     *
     * @param string $level
     */
    public function testValidLogLevels(string $level): void
    {
        $this->handlerMock->expects(self::once())
            ->method('add')
            ->with($this->name, $level = LogLevel::ALERT, $message = 'foo', []);

        $this->logger->log($level, $message);
    }

    /**
     * @return array<string[]>
     */
    public static function validLogLevelsDataProvider(): array
    {
        return [
            ['log'],
            ['emergency'],
            ['alert'],
            ['critical'],
            ['error'],
            ['warning'],
            ['notice'],
            ['info'],
            ['debug'],
        ];
    }

    public function testLogThrowsExceptionOnInvalidLogLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->logger->log('nope', 'An error occured');
    }
}
