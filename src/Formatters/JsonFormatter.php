<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Formatters;

use Stringable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class JsonFormatter extends AbstractFormatter
{
    /**
     * @param string|null $dateFormat
     * @param int|null    $maxPreviousExceptionDepth
     */
    public function __construct(?string $dateFormat = null, ?int $maxPreviousExceptionDepth = null)
    {
        parent::__construct($dateFormat, $maxPreviousExceptionDepth);
    }

    /**
     * {@inheritdoc}
     */
    public function interpolate(string $name, string $level, string|Stringable $message, array $context = []): string
    {
        $normalized = $this->normalize($message, $context);

        $payload = [
            'level' => strtoupper($level),
            'name' => $name,
            'message' => $normalized['message'],
            'time' => $this->getTimestampFromImmutable(),
        ];

        if (count($normalized['context']) > 0) {
            $payload['context'] = $normalized['context'];
        }

        if (count($normalized['exceptions']) > 0) {
            $payload['exceptions'] = explode(', ', $this->getExceptionsAsString($normalized['exceptions']));
        }

        return $this->normalizeValue($payload);
    }
}
