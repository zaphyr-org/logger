<?php

declare(strict_types=1);

namespace Zaphyr\Logger;

use Psr\Log\LogLevel;
use Zaphyr\Logger\Exceptions\LoggerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
enum Level: int
{
    case EMERGENCY = 800;
    case ALERT = 700;
    case CRITICAL = 600;
    case ERROR = 500;
    case WARNING = 400;
    case NOTICE = 300;
    case INFO = 200;
    case DEBUG = 100;

    /**
     * @param string $name
     *
     * @th rows LoggerException if the log level is invalid
     * @return self
     */
    public static function fromName(string $name): self
    {
        return match (strtolower($name)) {
            LogLevel::EMERGENCY => self::EMERGENCY,
            LogLevel::ALERT => self::ALERT,
            LogLevel::CRITICAL => self::CRITICAL,
            LogLevel::ERROR => self::ERROR,
            LogLevel::WARNING => self::WARNING,
            LogLevel::NOTICE => self::NOTICE,
            LogLevel::INFO => self::INFO,
            LogLevel::DEBUG => self::DEBUG,
            default => throw new LoggerException("Invalid log level: $name"),
        };
    }
}
