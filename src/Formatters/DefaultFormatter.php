<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Formatters;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Zaphyr\Logger\Contracts\FormatterInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class DefaultFormatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected $template = '[{date}] {name}.{level}: {message} [{context}] [{exception}]';

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @param string $dateFormat
     * @param string $timezone
     */
    public function __construct(string $dateFormat = 'Y-m-d H:i:s', string $timezone = 'utc')
    {
        $this->dateFormat = $dateFormat;
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function interpolate(string $name, string $level, string $message, array $context = []): string
    {
        foreach ($context as $key => $value) {
            if ($key === 'exception' && $value instanceof Exception) {
                $exceptionTrace = '';

                /** @var object $value */
                while ($previous ?? true) {
                    $exceptionTrace .= '(' . get_class($value) . ' ';
                    $exceptionTrace .= '(code: ' . $value->getCode() . ') ';
                    $exceptionTrace .= $value->getMessage();
                    $exceptionTrace .= ' at ' . $value->getFile() . ':';
                    $exceptionTrace .= $value->getLine() . ')';

                    if (!$value = $value->getPrevious()) {
                        $previous = false;
                    }
                }

                $exceptionTrace = str_replace(PHP_EOL, ' ', rtrim($exceptionTrace, ','));

                unset($context[$key]);
                continue;
            }

            if (
                mb_strpos($message, '{' . $key . '}') !== false
                && ((is_object($value) && method_exists($value, '__toString')) || !is_array($value))
            ) {
                $message = str_replace('{' . $key . '}', "$value", $message);
                unset($context[$key]);
            }
        }

        $context = count($context) > 0 ? json_encode($context) : '';
        $exceptionTrace = $exceptionTrace ?? '';

        return str_replace(
            ['{date}', '{name}', '{level}', '{message}', '{context}', '{exception}'],
            [$this->getTimestampFromImmutable(), $name, $level, $message, $context, $exceptionTrace],
            $this->template
        );
    }

    /**
     * @return string
     */
    protected function getTimestampFromImmutable(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone($this->timezone)))->format($this->dateFormat);
    }
}
