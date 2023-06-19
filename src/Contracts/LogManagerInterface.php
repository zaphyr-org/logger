<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Contracts;

use Zaphyr\Logger\Exceptions\LoggerException;
use Zaphyr\Logger\Logger;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface LogManagerInterface
{
    /**
     * @param string|null $logger
     *
     * @throws LoggerException
     * @return Logger
     */
    public function logger(string|null $logger = null): Logger;
}
