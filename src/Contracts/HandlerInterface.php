<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Contracts;

use Stringable;
use Zaphyr\Logger\Exceptions\LoggerException;
use Zaphyr\Logger\Level;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface HandlerInterface
{
    /**
     * @param string               $name
     * @param string               $level
     * @param string|Stringable    $message
     * @param array<string, mixed> $context
     */
    public function add(string $name, string $level, string|Stringable $message, array $context = []): void;

    /**
     * @return Level
     */
    public function getLevel(): Level;

    /**
     * @param string $level
     *
     * @throws LoggerException if the level string is invalid
     * @return bool
     */
    public function hasLevel(string $level): bool;
}
