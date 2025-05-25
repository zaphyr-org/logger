<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Formatters;

use DateTimeImmutable;
use JsonException;
use Stringable;
use Throwable;
use Zaphyr\Logger\Contracts\FormatterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected string $dateFormat = 'Y-m-d\TH:i:s.uP';

    /**
     * @var int
     */
    protected int $maxPreviousExceptionDepth = 10;

    /**
     * @param string|null $dateFormat
     * @param int|null    $maxPreviousExceptionDepth
     */
    public function __construct(?string $dateFormat, ?int $maxPreviousExceptionDepth)
    {
        if ($dateFormat !== null) {
            $this->dateFormat = $dateFormat;
        }

        if ($maxPreviousExceptionDepth !== null) {
            $this->maxPreviousExceptionDepth = $maxPreviousExceptionDepth;
        }
    }

    /**
     * @param string|Stringable    $message
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    protected function normalize(string|Stringable $message, array $context = []): array
    {
        $depth = 0;
        $exceptions = [];

        foreach ($context as $key => $value) {
            if ($key === 'exception' && $value instanceof Throwable) {
                /** @var object $value */
                while ($previous ?? true) {
                    $exceptions[] = [
                        'class' => get_class($value),
                        'code' => $value->getCode(),
                        'message' => $value->getMessage(),
                        'file' => $value->getFile(),
                        'line' => $value->getLine(),
                    ];

                    $depth++;

                    if ($depth > $this->maxPreviousExceptionDepth || (!$value = $value->getPrevious())) {
                        $previous = false;
                    }
                }

                unset($context[$key]);
                continue;
            }

            if (
                mb_strpos((string)$message, '{' . $key . '}') !== false
                && ((is_object($value) && method_exists($value, '__toString')) || !is_array($value))
            ) {
                $message = str_replace('{' . $key . '}', "$value", (string)$message);
                unset($context[$key]);
            }
        }

        return [
            'message' => $message,
            'context' => $context,
            'exceptions' => $exceptions,
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return string
     */
    protected function getContextAsString(array $context): string
    {
        $output = '';

        if (count($context) > 0) {
            foreach ($context as $key => $value) {
                $output .= $key . ': ' . $this->normalizeValue($value) . ', ';
            }
        }

        return rtrim($output, ', ');
    }

    /**
     * @param array<string, mixed> $exceptions
     *
     * @return string
     */
    protected function getExceptionsAsString(array $exceptions): string
    {
        $output = '';

        if (count($exceptions) > 0) {
            foreach ($exceptions as $exception) {
                $output .= $exception['class'] . ' ';
                $output .= '(code: ' . $exception['code'] . ') ';
                $output .= $exception['message'] . ' ';
                $output .= 'at ' . $exception['file'] . ':';
                $output .= $exception['line'] . ', ';
            }
        }

        return rtrim($output, ', ');
    }

    /**
     * @return string
     */
    protected function getTimestampFromImmutable(): string
    {
        return (new DateTimeImmutable('now'))->format($this->dateFormat);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function normalizeValue(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '[N/A]';
        }
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function escapeValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
