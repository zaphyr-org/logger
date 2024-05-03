<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Formatters;

use Exception;
use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Formatters\LineFormatter;

class LineFormatterTest extends TestCase
{
    /* -------------------------------------------------
     * INTERPOLATE
     * -------------------------------------------------
     */

    /**
     * @dataProvider interpolateDataProvider
     *
     * @param string $name
     * @param string $level
     * @param string $message
     * @param array<string, mixed> $context
     * @param string $expected
     */
    public function testInterpolate(
        string $name,
        string $level,
        string $message,
        array $context,
        string $expected
    ): void {
        $formatter = $this->createPartialMock(LineFormatter::class, ['getTimestampFromImmutable']);
        $formatter->expects(self::once())
            ->method('getTimestampFromImmutable')
            ->willReturn('date');

        $output = $formatter->interpolate($name, $level, $message, $context);

        self::assertSame($expected, $output);
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
                '[date] name.INFO: Simple log message [] []',
            ],
            [
                'name',
                'info',
                'Log message with context',
                [
                    'foo' => 'bar',
                ],
                '[date] name.INFO: Log message with context [foo: "bar"] []',
            ],
            [
                'name',
                'info',
                'Log message with {replace} context',
                [
                    'replace' => 'replaced',
                ],
                '[date] name.INFO: Log message with replaced context [] []',
            ],
            [
                'name',
                'info',
                'Log message with {replace} context and non replaced context',
                [
                    'replace' => 'replaced',
                    'foo' => 'bar',
                ],
                '[date] name.INFO: Log message with replaced context and non replaced context [foo: "bar"] []',
            ],
            [
                'name',
                'info',
                'Log message with exception',
                [
                    'exception' => $exception,
                ],
                '[date] name.INFO: Log message with exception [] [Exception (code: 0) This is a test exception at ' . __DIR__ . '/LineFormatterTest.php:49]',
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
                '[date] name.INFO: Log message with all possible context [foo: "bar"] [Exception (code: 0) This is a test exception at ' . __DIR__ . '/LineFormatterTest.php:49]',
            ],
            [
                'name',
                'info',
                'Log message with previous exception',
                [
                    'exception' => $previousException,
                ],
                '[date] name.INFO: Log message with previous exception [] [Exception (code: 1) first at ' . __DIR__ . '/LineFormatterTest.php:50, Exception (code: 2) second at ' . __DIR__ . '/LineFormatterTest.php:50, Exception (code: 3) third at ' . __DIR__ . '/LineFormatterTest.php:50]',
            ],
        ];
    }

    public function testInterpolateCannotNormalizeContextValue(): void
    {
        $formatter = $this->createPartialMock(LineFormatter::class, ['getTimestampFromImmutable']);
        $formatter->expects(self::once())
            ->method('getTimestampFromImmutable')
            ->willReturn('date');

        $context = [
            'context' => fopen('php://memory', 'r'),
        ];

        $output = $formatter->interpolate('name', 'info', 'Log message', $context);

        self::assertSame('[date] name.INFO: Log message [context: [N/A]] []', $output);
    }

    /* -------------------------------------------------
     * DATE FORMAT
     * -------------------------------------------------
     */

    public function testWithCustomDateFormat(): void
    {
        $dateFormat = 'Y-m-d H:i:s';
        $date = date($dateFormat);

        $formatter = new LineFormatter($dateFormat);

        $output = $formatter->interpolate('name', 'info', 'message');

        self::assertSame('[' . $date . '] name.INFO: message [] []', $output);
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
        $formatter = new LineFormatter($dateFormat, 0);

        $output = $formatter->interpolate('name', 'info', 'message', [
            'exception' => $previousException,
        ]);

        self::assertSame(
            '[' . $date . '] name.INFO: message [] [Exception (code: 1) first at ' . __DIR__ . '/LineFormatterTest.php:160]',
            $output
        );
    }
}
