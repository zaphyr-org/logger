<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Handlers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Level;
use Zaphyr\Utils\File;

class FileHandlerTest extends TestCase
{
    /**
     * @var string
     */
    protected static string $tempLogDir = __DIR__ . '/log/';

    /**
     * @var FormatterInterface&MockObject
     */
    protected FormatterInterface&MockObject $formatterMock;

    public static function tearDownAfterClass(): void
    {
        File::deleteDirectory(static::$tempLogDir);
    }

    protected function setUp(): void
    {
        $this->formatterMock = $this->createMock(FormatterInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->formatterMock);
    }

    /* -------------------------------------------------
     * ADD
     * -------------------------------------------------
     */

    /**
     * @param string $expectedContent
     * @param string $filename
     */
    #[DataProvider('logDataProvider')]
    public function testAdd(string $expectedContent, string $filename): void
    {
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');

        $fileHandler = new FileHandler($filename, $this->formatterMock);
        $fileHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
    }

    public function testAddWithLowerLevel(): void
    {
        $this->formatterMock->expects(self::never())->method('interpolate');

        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, Level::ERROR);
        $fileHandler->add('app', 'debug', 'This is a test log');
    }

    public function testAddWithHigherLevel(): void
    {
        $name = 'app';
        $level = 'warning';
        $message = 'This is a test log';

        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->with($name, $level, $message)
            ->willReturn($message);

        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, Level::DEBUG);
        $fileHandler->add($name, $level, $message);
    }

    public function testAddWithEqualLevel(): void
    {
        $name = 'app';
        $level = 'info';
        $message = 'This is a test log';

        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->with($name, $level, $message)
            ->willReturn($message);

        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, Level::INFO);
        $fileHandler->add($name, $level, $message);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function logDataProvider(): array
    {
        return [
            [
                'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'test.log',
            ],
            [
                'This is a test log' . PHP_EOL . 'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'test.log',
            ],
        ];
    }

    /* -------------------------------------------------
     * GET LEVEL
     * -------------------------------------------------
     */

    public function testGetLevelWithDefaultLevel(): void
    {
        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock);

        self::assertEquals(Level::DEBUG->value, $fileHandler->getLevel()->value);
    }

    public function testGetLevelWithCustomLevel(): void
    {
        $customLevel = Level::fromName(LogLevel::WARNING);
        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, $customLevel);

        self::assertEquals($customLevel->value, $fileHandler->getLevel()->value);
    }

    /* -------------------------------------------------
     * HAS LEVEL
     * -------------------------------------------------
     */

    public function testHasLevelWithDefaultLevel(): void
    {
        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock);

        self::assertTrue($fileHandler->hasLevel(LogLevel::DEBUG));
    }

    public function testHasLevelWithHigherLevel(): void
    {
        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, Level::WARNING);

        self::assertTrue($fileHandler->hasLevel(LogLevel::ERROR));
    }

    public function testHasLevelWithEqualLevel(): void
    {
        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, Level::INFO);

        self::assertTrue($fileHandler->hasLevel(LogLevel::INFO));
    }

    public function testHasLevelWithLowerLevel(): void
    {
        $fileHandler = new FileHandler(static::$tempLogDir . 'test.log', $this->formatterMock, Level::ERROR);

        self::assertFalse($fileHandler->hasLevel(LogLevel::DEBUG));
    }
}
