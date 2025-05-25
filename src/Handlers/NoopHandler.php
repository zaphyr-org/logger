<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Stringable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class NoopHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    protected function write(string $name, string $level, string|Stringable $message, array $context = []): void
    {
        // This is a no-operation handler, it does nothing.
    }
}
