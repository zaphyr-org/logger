<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Stringable;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Formatters\LineFormatter;
use Zaphyr\Logger\Level;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @param FormatterInterface $formatter
     * @param Level              $level
     */
    public function __construct(
        protected FormatterInterface $formatter = new LineFormatter(),
        protected Level $level = Level::DEBUG
    ) {
    }

    /**
     * @param string               $name
     * @param string               $level
     * @param string|Stringable    $message
     * @param array<string, mixed> $context
     */
    abstract protected function write(
        string $name,
        string $level,
        string|Stringable $message,
        array $context = []
    ): void;

    /**
     * {@inheritdoc}
     */
    public function add(string $name, string $level, string|Stringable $message, array $context = []): void
    {
        if ($this->hasLevel($level)) {
            $this->write($name, $level, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): Level
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLevel(string $level): bool
    {
        return Level::fromName($level)->value >= $this->level->value;
    }
}
