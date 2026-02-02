<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Formatters;

use Exception;
use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Formatters\HtmlFormatter;

class HtmlFormatterTest extends TestCase
{
    /* -------------------------------------------------
     * INTERPOLATE
     * -------------------------------------------------
     */

    /**
     * @dataProvider interpolateDataProvider
     *
     * @param string               $name
     * @param string               $level
     * @param string               $message
     * @param array<string, mixed> $context
     * @param string               $expected
     */
    public function testInterpolate(
        string $name,
        string $level,
        string $message,
        array $context,
        string $expected
    ): void {
        $formatter = $this->createPartialMock(HtmlFormatter::class, ['getTimestampFromImmutable']);
        $formatter->expects(self::once())
            ->method('getTimestampFromImmutable')
            ->willReturn('date');

        $output = $formatter->interpolate($name, $level, $message, $context);

        self::assertStringContainsString($expected, $output);
    }

    /**
     * @return array<int, array<mixed>>
     */
    public static function interpolateDataProvider(): array
    {
        $exception = new Exception('This is a test exception');
        $previousException = new Exception('first', 1, new Exception('second', 2, new Exception('third', 3)));

        return [
            [
                'name',
                'info',
                'Simple log message',
                [],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Simple log message</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr></table>',
            ],
            [
                'name',
                'info',
                'Log message with context',
                [
                    'foo' => 'bar',
                ],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Log message with context</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Context:</th><td style="padding:10px"><table style="margin-left:-10px;border-spacing:0"><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">foo:</th><td style="padding:10px">&quot;bar&quot;</td></tr></table></td></tr></table>',
            ],
            [
                'name',
                'info',
                'Log message with {replace} context',
                [
                    'replace' => 'replaced',
                ],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Log message with replaced context</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr></table>',
            ],
            [
                'name',
                'info',
                'Log message with {replace} context and non replaced context',
                [
                    'replace' => 'replaced',
                    'foo' => 'bar',
                ],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Log message with replaced context and non replaced context</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Context:</th><td style="padding:10px"><table style="margin-left:-10px;border-spacing:0"><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">foo:</th><td style="padding:10px">&quot;bar&quot;</td></tr></table></td></tr></table>',
            ],
            [
                'name',
                'info',
                'Log message with exception',
                [
                    'exception' => $exception,
                ],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Log message with exception</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Exceptions:</th><td style="padding:10px"><table style="margin-left:-10px;border-spacing:0"><tr style="color:white;background:#666">',
            ],
            [
                'name',
                'info',
                'Log message with all {replace} context',
                [
                    'foo' => 'bar',
                    'replace' => 'possible',
                    'exception' => $exception,
                ],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Log message with all possible context</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Context:</th><td style="padding:10px"><table style="margin-left:-10px;border-spacing:0"><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">foo:</th><td style="padding:10px">&quot;bar&quot;</td></tr></table></td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Exceptions:</th><td style="padding:10px"><table style="margin-left:-10px;border-spacing:0"><tr style="color:white;background:#666">',
            ],
            [
                'name',
                'info',
                'Log message with previous exception',
                [
                    'exception' => $previousException,
                ],
                '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">Log message with previous exception</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">date</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Exceptions:</th><td style="padding:10px">',
            ],
        ];
    }

    /* -------------------------------------------------
     * DATE FORMAT
     * -------------------------------------------------
     */

    public function testWithCustomDateFormat(): void
    {
        $dateFormat = 'Y-m-d H:i:s';
        $date = date($dateFormat);

        $formatter = new HtmlFormatter($dateFormat);

        $output = $formatter->interpolate('name', 'info', 'message');

        self::assertStringContainsString(
            '<th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">' . $date . '</td>',
            $output
        );
    }

    /* -------------------------------------------------
     * MAX EXCEPTION DEPTH
     * -------------------------------------------------
     */

    public function testWithCustomMaxExceptionDepth(): void
    {
        $previousException = new Exception('first', 1, new Exception('second', 2, new Exception('third', 3)));

        $dateFormat = 'Y-m-d H:i:s';
        $date = date($dateFormat);
        $formatter = new HtmlFormatter($dateFormat, 0);

        $output = $formatter->interpolate('name', 'info', 'message', [
            'exception' => $previousException,
        ]);

        self::assertStringContainsString(
            '<table style="width:100%;font-family:sans-serif"><tr style="color:white;background:#17a2b8;text-align:left"><th colspan="2" style="padding:10px">INFO</th></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Name:</th><td style="padding:10px">name</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Message:</th><td style="padding:10px">message</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Time:</th><td style="padding:10px">' . $date . '</td></tr><tr style="color:white;background:#666"><th style="padding:10px;text-align:left">Exceptions:</th><td style="padding:10px"><table style="margin-left:-10px;border-spacing:0"><tr style="color:white;background:#666"><td style="padding:10px">Exception (code: 1) first at',
            $output
        );
    }
}
