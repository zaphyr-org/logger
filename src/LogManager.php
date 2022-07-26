<?php

declare(strict_types=1);

namespace Zaphyr\Logger;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Contracts\LogManagerInterface;
use Zaphyr\Logger\Handlers\FileHandler;
use Zaphyr\Logger\Handlers\MailHandler;
use Zaphyr\Logger\Handlers\RotateHandler;
use Zaphyr\Utils\File;
use Zaphyr\Utils\Str;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class LogManager implements LogManagerInterface
{
    /**
     * @var string
     */
    protected $defaultLogger;

    /**
     * @var array<string, array<mixed>>
     */
    protected $loggers;

    /**
     * @var LoggerInterface[]
     */
    protected $cachedLoggers = [];

    /**
     * @param string                      $defaultLogger
     * @param array<string, array<mixed>> $loggers
     */
    public function __construct(string $defaultLogger, array $loggers)
    {
        $this->defaultLogger = $defaultLogger;
        $this->loggers = $loggers;
    }

    /**
     * {@inheritdoc}
     */
    public function logger(string $logger = null): LoggerInterface
    {
        $logger = $logger ?? $this->defaultLogger;

        return $this->cachedLoggers[$logger] ?? $this->createLogger($logger);
    }

    /**
     * @param string $logger
     *
     * @throws InvalidArgumentException
     *
     * @return LoggerInterface
     */
    protected function createLogger(string $logger): LoggerInterface
    {
        $config = $this->getConfigFor($logger);

        if (!isset($config['handlers'])) {
            throw new InvalidArgumentException('The logger "' . $logger . '" has no valid handlers configured');
        }

        $handlers = $this->resolveHandlers($logger, $config['handlers']);

        return $this->cachedLoggers[$logger] = new Logger($logger, $handlers);
    }

    /**
     * @param string $logger
     *
     * @throws InvalidArgumentException
     *
     * @return array<mixed>
     */
    protected function getConfigFor(string $logger): array
    {
        $config = $this->loggers[$logger] ?? null;

        if ($config === null) {
            throw new InvalidArgumentException('Logger "' . $logger . '" is not configured');
        }

        return $config;
    }

    /**
     * @param string               $logger
     * @param array<array<string>> $handlers
     *
     * @throws InvalidArgumentException
     *
     * @return HandlerInterface[]
     */
    protected function resolveHandlers(string $logger, array $handlers): array
    {
        $resolvedHandlers = [];

        foreach ($handlers as $handler => $config) {
            $resolvedHandlers[] = $this->createHandler($logger, $handler, $config);
        }

        return $resolvedHandlers;
    }

    /**
     * @param string   $logger
     * @param string   $handler
     * @param string[] $config
     *
     * @throws InvalidArgumentException
     *
     * @return HandlerInterface
     */
    protected function createHandler(string $logger, string $handler, array $config): HandlerInterface
    {
        $method = 'create' . Str::studly($handler) . 'Handler';

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException('The handler "' . $handler . '" is not supported');
        }

        return $this->{$method}($logger, $config);
    }

    /**
     * @param string   $logger
     * @param string[] $config
     *
     * @throws InvalidArgumentException
     *
     * @return FileHandler
     */
    protected function createFileHandler(string $logger, array $config): FileHandler
    {
        if (!isset($config['storagePath'])) {
            throw new InvalidArgumentException(
                'The file handler for logger "' . $logger . '" has no valid storage path'
            );
        }

        $path = $config['storagePath'] . DIRECTORY_SEPARATOR . $logger . '.log';

        return new FileHandler($path);
    }

    /**
     * @param string   $logger
     * @param string[] $config
     *
     * @throws InvalidArgumentException
     *
     * @return MailHandler
     */
    protected function createMailHandler(string $logger, array $config): MailHandler
    {
        $requiredConfig = ['dsn', 'from', 'to', 'subject'];

        foreach ($requiredConfig as $key) {
            if (!isset($config[$key])) {
                throw new InvalidArgumentException(
                    'The mail handler for logger "' . $logger . '" has no valid "' . $key . '" configuration'
                );
            }
        }

        $email = (new Email())->from($config['from'])->to($config['to'])->subject($config['subject']);

        return new MailHandler(new Mailer(Transport::fromDsn($config['dsn'])), $email);
    }

    /**
     * @param string   $logger
     * @param string[] $config
     *
     * @throws InvalidArgumentException
     *
     * @return RotateHandler
     */
    protected function createRotateHandler(string $logger, array $config): RotateHandler
    {
        if (!isset($config['storagePath'])) {
            throw new InvalidArgumentException(
                'The rotate handler for logger "' . $logger . '" has no valid storage path'
            );
        }

        $path = $config['storagePath'] . DIRECTORY_SEPARATOR . $logger;

        if (!File::isDirectory($path)) {
            File::createDirectory($path);
        }

        $interval = $config['interval'] ?? 'day';

        return new RotateHandler($path, $interval);
    }
}
