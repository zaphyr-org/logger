<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Contracts;

use Psr\Log\LoggerInterface;

/**
 * Interface LogManagerInterface.
 *
 * @author merloxx <merloxx@zaphyr.org>
 */
interface LogManagerInterface
{
    /**
     * @param string|null $logger
     *
     * @return LoggerInterface
     */
    public function logger(string $logger = null): LoggerInterface;
}
