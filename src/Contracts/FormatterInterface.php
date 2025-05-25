<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Contracts;

use Stringable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface FormatterInterface
{
    /**
     * @param string               $name
     * @param string               $level
     * @param string|Stringable    $message
     * @param array<string, mixed> $context
     *
     * @return string
     */
    public function interpolate(string $name, string $level, string|Stringable $message, array $context = []): string;
}
