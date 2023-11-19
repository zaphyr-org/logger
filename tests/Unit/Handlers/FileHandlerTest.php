<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Handlers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Handlers\FileHandler;
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

    public static function setUpBeforeClass(): void
    {
        File::createDirectory(static::$tempLogDir);
    }

    public static function tearDownAfterClass(): void
    {
        File::deleteDirectory(static::$tempLogDir);
    }

    protected function setUp(): void
    {
        $this->formatterMock = $this->createMock(FormatterInterface::class);
        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->willReturn('This is a test log');
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
     * @dataProvider logDataProvider
     *
     * @param string $expectedContent
     * @param string $filename
     */
    public function testAdd(string $expectedContent, string $filename): void
    {
        $fileHandler = new FileHandler($filename, $this->formatterMock);
        $fileHandler->add('app', 'INFO', $expectedContent);

        self::assertEquals($expectedContent, file_get_contents($filename));
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
}
