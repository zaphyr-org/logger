<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Formatters;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class LineFormatter extends AbstractFormatter
{
    /**
     * @param string|null $dateFormat
     * @param int|null    $maxPreviousExceptionDepth
     */
    public function __construct(string|null $dateFormat = null, int|null $maxPreviousExceptionDepth = null)
    {
        parent::__construct($dateFormat, $maxPreviousExceptionDepth);
    }

    /**
     * {@inheritdoc}
     */
    public function interpolate(string $name, string $level, string $message, array $context = []): string
    {
        $normalized = $this->normalize($message, $context);

        return '[' . $this->getTimestampFromImmutable() . '] '
            . $name . '.'
            . strtoupper($level) . ': '
            . $normalized['message']
            . ' [' . $this->getContextAsString($normalized['context']) . ']'
            . ' [' . $this->getExceptionsAsString($normalized['exceptions']) . ']';
    }
}
