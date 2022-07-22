<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Formatters;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Zaphyr\Logger\Formatters\DefaultFormatter;

class DefaultFormatterTest extends TestCase
{
    /* -------------------------------------------------
     * CONSTRUCTOR
     * -------------------------------------------------
     */

    public function testConstructorSetsDateFormat(): void
    {
        $formatter = new DefaultFormatter();

        $reflection = new ReflectionObject($formatter);
        $defaultTime = $reflection->getProperty('dateFormat');
        $defaultTime->setAccessible(true);

        self::assertSame('Y-m-d H:i:s', $defaultTime->getValue($formatter));
    }

    public function testConstructorSetsTimezone(): void
    {
        $formatter = new DefaultFormatter();

        $reflection = new ReflectionObject($formatter);
        $timezone = $reflection->getProperty('timezone');
        $timezone->setAccessible(true);

        self::assertSame('utc', $timezone->getValue($formatter));
    }

    /* -------------------------------------------------
     * INTERPOLATION
     * -------------------------------------------------
     */

    /**
     * @dataProvider messageDataProvider
     *
     * @param string                      $name
     * @param string                      $level
     * @param string                      $message
     * @param array<string, array|object> $context
     * @param string                      $regex
     */
    public function testInterpolation(string $name, string $level, string $message, array $context, string $regex): void
    {
        $formatter = $this->createPartialMock(DefaultFormatter::class, ['getTimestampFromImmutable']);
        $formatter->expects(self::once())
            ->method('getTimestampFromImmutable')
            ->willReturn('DATA');

        $interpolated = $formatter->interpolate($name, $level, $message, $context);
        $interpolated = str_replace(realpath(__DIR__) ?: '', '', $interpolated);

        self::assertMatchesRegularExpression($regex, $interpolated);
    }

    /**
     * @return array<array>
     */
    public function messageDataProvider(): array
    {
        $exception = new Exception('first', 1, new Exception('second', 2, new Exception('third', 3)));

        return [
            ['name', 'level', 'message', [], '#\[DATA\] (\w+).(\w+): (.)+ (\[\]) \[\]#'],
            [
                'name',
                'level',
                'Custom message: {message}',
                ['message' => 'hello'],
                '#\[DATA\] (\w+).(\w+): Custom message: hello (\[\]) \[\]#',
            ],
            [
                'name',
                'level',
                'fake exception',
                ['exception' => 'fake'],
                '#\[DATA\] (\w+).(\w+): (.)+ \[({"exception":"fake"})\] \[\]#',
            ],
            [
                'name',
                'level',
                'true exception',
                ['exception' => $exception],
                '#\[DATA\] (\w+).(\w+): (.)+ (\[\]) \[(,?\(Exception \(code: [123]\) (first|second|third) at ([\w/]+).php:\d+\)){3}\]#',
            ],
        ];
    }

    /* -------------------------------------------------
     * TIMESTAMP
     * -------------------------------------------------
     */

    public function testGetTimestampFromImmutable(): void
    {
        $formatter = new DefaultFormatter('i');

        $reflection = new ReflectionObject($formatter);
        $timestamp = $reflection->getMethod('getTimestampFromImmutable');
        $timestamp->setAccessible(true);

        self::assertMatchesRegularExpression('/^[0-5][0-9]$/', $timestamp->invoke($formatter));
    }
}
