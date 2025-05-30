<?php

declare(strict_types=1);

namespace Zaphyr\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Stringable;
use Zaphyr\Logger\Contracts\HandlerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Logger extends AbstractLogger
{
    /**
     * @var HandlerInterface[]
     */
    protected array $handlers;

    /**
     * @var string[]
     */
    protected static array $levels = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    /**
     * @param string             $name
     * @param HandlerInterface[] $handlers
     */
    public function __construct(protected string $name, array $handlers)
    {
        $this->setHandlers($handlers);
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * @param HandlerInterface $handler
     */
    public function setHandler(HandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param HandlerInterface[] $handlers
     */
    public function setHandlers(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->setHandler($handler);
        }
    }

    /**
     * {@inheritdoc}
     * @param array<string, mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!in_array($level, self::$levels, true)) {
            throw new InvalidArgumentException(
                'Log level must be one of the constants contained by the "' . LogLevel::class . '" class'
            );
        }

        foreach ($this->handlers as $handler) {
            $handler->add($this->name, $level, $message, $context);
        }
    }
}
