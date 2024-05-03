<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Zaphyr\Logger\Contracts\HandlerInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class NoopHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function add(string $name, string $level, string $message, array $context = []): void
    {
        // This is a no operation handler, it does nothing.
    }
}
