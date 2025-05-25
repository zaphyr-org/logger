<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Exceptions\LoggerException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
abstract class AbstractFileHandler implements HandlerInterface
{
    /**
     * @param string $filename
     *
     * @throws LoggerException if the log directory cannot be created
     * @return void
     */
    protected function createMissingLogDirectory(string $filename): void
    {
        $directory = dirname($filename);

        if (!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new LoggerException("Log file directory $directory could not be created");
        }
    }
}
