<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Contracts;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface HandlerInterface
{
    /**
     * @param string                             $name
     * @param string                             $level
     * @param string                             $message
     * @param array<string, array<mixed>|object> $context
     */
    public function add(string $name, string $level, string $message, array $context = []): void;
}
