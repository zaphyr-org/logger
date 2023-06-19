<?php

declare(strict_types=1);

namespace Zaphyr\Logger;

use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Contracts\LogManagerInterface;
use Zaphyr\Logger\Exceptions\LoggerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class LogManager implements LogManagerInterface
{
    /**
     * @var Logger[]
     */
    protected array $cachedLoggers = [];

    /**
     * @param string                            $defaultLogger
     * @param array<string, HandlerInterface[]> $logHandlers
     */
    public function __construct(
        protected string $defaultLogger,
        protected array $logHandlers,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function logger(string $logger = null): Logger
    {
        $logger = $logger ?? $this->defaultLogger;

        return $this->cachedLoggers[$logger] ?? $this->createLogger($logger);
    }

    /**
     * @param string $logger
     *
     * @throws LoggerException
     * @return Logger
     */
    protected function createLogger(string $logger): Logger
    {
        if (!isset($this->logHandlers[$logger])) {
            throw new LoggerException('Logger "' . $logger . '" not found');
        }

        if (count($this->logHandlers[$logger]) === 0) {
            throw new LoggerException('Logger "' . $logger . '" has no log handlers');
        }

        return $this->cachedLoggers[$logger] = new Logger($logger, $this->logHandlers[$logger]);
    }
}
