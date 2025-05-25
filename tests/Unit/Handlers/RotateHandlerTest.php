<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Handlers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Exceptions\LoggerException;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Utils\File;

class RotateHandlerTest extends TestCase
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
    #[DataProvider('logHourlyDataProvider')]
    public function testAddWithHourlyInterval(string $expectedContent, string $filename): void
    {
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');

        $rotateHandler = new RotateHandler(static::$tempLogDir, RotateHandler::INTERVAL_HOUR, $this->formatterMock);
        $rotateHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function logHourlyDataProvider(): array
    {
        return [
            [
                'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'h' . date('H_Y_m_d') . '.log',
            ],
            [
                'This is a test log' . PHP_EOL . 'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'h' . date('H_Y_m_d') . '.log',
            ],
        ];
    }

    /**
     * @param string $expectedContent
     * @param string $filename
     */
    #[DataProvider('logDayDataProvider')]
    public function testAddWithDayInterval(string $expectedContent, string $filename): void
    {
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');

        $rotateHandler = new RotateHandler(static::$tempLogDir, RotateHandler::INTERVAL_DAY, $this->formatterMock);
        $rotateHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function logDayDataProvider(): array
    {
        return [
            [
                'This is a test log' . PHP_EOL,
                static::$tempLogDir . date('Y_m_d') . '.log',
            ],
            [
                'This is a test log' . PHP_EOL . 'This is a test log' . PHP_EOL,
                static::$tempLogDir . date('Y_m_d') . '.log',
            ],
        ];
    }

    /**
     * @param string $expectedContent
     * @param string $filename
     */
    #[DataProvider('logWeekDataProvider')]
    public function testAddWithWeekInterval(string $expectedContent, string $filename): void
    {
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');

        $rotateHandler = new RotateHandler(static::$tempLogDir, RotateHandler::INTERVAL_WEEK, $this->formatterMock);
        $rotateHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function logWeekDataProvider(): array
    {
        return [
            [
                'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'w' . date('W_Y') . '.log',
            ],
            [
                'This is a test log' . PHP_EOL . 'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'w' . date('W_Y') . '.log',
            ],
        ];
    }

    /**
     * @param string $expectedContent
     * @param string $filename
     */
    #[DataProvider('logMonthDataProvider')]
    public function testAddWithMonthInterval(string $expectedContent, string $filename): void
    {
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');

        $rotateHandler = new RotateHandler(static::$tempLogDir, RotateHandler::INTERVAL_MONTH, $this->formatterMock);
        $rotateHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function logMonthDataProvider(): array
    {
        return [
            [
                'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'm' . date('m_Y') . '.log',
            ],
            [
                'This is a test log' . PHP_EOL . 'This is a test log' . PHP_EOL,
                static::$tempLogDir . 'm' . date('m_Y') . '.log',
            ],
        ];
    }

    /**
     * @param string $expectedContent
     * @param string $filename
     */
    #[DataProvider('logYearDataProvider')]
    public function testAddWithYearInterval(string $expectedContent, string $filename): void
    {
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');

        $rotateHandler = new RotateHandler(static::$tempLogDir, RotateHandler::INTERVAL_YEAR, $this->formatterMock);
        $rotateHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function logYearDataProvider(): array
    {
        return [
            [
                'This is a test log' . PHP_EOL,
                static::$tempLogDir . date('Y') . '.log',
            ],
            [
                'This is a test log' . PHP_EOL . 'This is a test log' . PHP_EOL,
                static::$tempLogDir . date('Y') . '.log',
            ],
        ];
    }

    public function testAddThrowsExceptionOnInvalidInterval(): void
    {
        $this->expectException(LoggerException::class);

        $rotateHandler = new RotateHandler(static::$tempLogDir, 'decade');
        $rotateHandler->add('app', 'INFO', 'Something went wrong');
    }
}
