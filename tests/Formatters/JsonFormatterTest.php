<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Formatters;

use Exception;
use PHPUnit\Framework\TestCase;
use Zaphyr\Logger\Formatters\JsonFormatter;

class JsonFormatterTest extends TestCase
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
        $formatter = $this->createPartialMock(JsonFormatter::class, ['getTimestampFromImmutable']);
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
                '{"level":"INFO","name":"name","message":"Simple log message","time":"date"}',
            ],
            [
                'name',
                'info',
                'Log message with context',
                [
                    'foo' => 'bar',
                ],
                '{"level":"INFO","name":"name","message":"Log message with context","time":"date","context":{"foo":"bar"}}',
            ],
            [
                'name',
                'info',
                'Log message with {replace} context',
                [
                    'replace' => 'replaced',
                ],
                '{"level":"INFO","name":"name","message":"Log message with replaced context","time":"date"}',
            ],
            [
                'name',
                'info',
                'Log message with {replace} context and non replaced context',
                [
                    'replace' => 'replaced',
                    'foo' => 'bar',
                ],
                '{"level":"INFO","name":"name","message":"Log message with replaced context and non replaced context","time":"date","context":{"foo":"bar"}}',
            ],
            [
                'name',
                'info',
                'Log message with exception',
                [
                    'exception' => $exception,
                ],
                '{"level":"INFO","name":"name","message":"Log message with exception","time":"date","exceptions":["Exception (code: 0) This is a test exception at \/Users\/merloxx\/PhpstormProjects\/zaphyr\/repositories\/logger\/tests\/Formatters\/JsonFormatterTest.php:49"]}',
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
                '{"level":"INFO","name":"name","message":"Log message with all possible context","time":"date","context":{"foo":"bar"},"exceptions":["Exception (code: 0) This is a test exception at \/Users\/merloxx\/PhpstormProjects\/zaphyr\/repositories\/logger\/tests\/Formatters\/JsonFormatterTest.php:49"]}',
            ],
            [
                'name',
                'info',
                'Log message with previous exception',
                [
                    'exception' => $previousException,
                ],
                '{"level":"INFO","name":"name","message":"Log message with previous exception","time":"date","exceptions":["Exception (code: 1) first at \/Users\/merloxx\/PhpstormProjects\/zaphyr\/repositories\/logger\/tests\/Formatters\/JsonFormatterTest.php:50","Exception (code: 2) second at \/Users\/merloxx\/PhpstormProjects\/zaphyr\/repositories\/logger\/tests\/Formatters\/JsonFormatterTest.php:50","Exception (code: 3) third at \/Users\/merloxx\/PhpstormProjects\/zaphyr\/repositories\/logger\/tests\/Formatters\/JsonFormatterTest.php:50"]}',
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

        $formatter = new JsonFormatter($dateFormat);

        $output = $formatter->interpolate('name', 'info', 'message');

        self::assertStringContainsString(
            '"time":"' . $date . '"',
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
        $formatter = new JsonFormatter($dateFormat, 0);

        $output = $formatter->interpolate('name', 'info', 'message', [
            'exception' => $previousException,
        ]);

        self::assertSame(
            '{"level":"INFO","name":"name","message":"message","time":"' . $date . '","exceptions":["Exception (code: 1) first at ' . str_replace('/', '\/', __DIR__) . '\/JsonFormatterTest.php:147"]}',
            $output
        );
    }
}
