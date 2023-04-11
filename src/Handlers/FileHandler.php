<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Formatters\DefaultFormatter;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class FileHandler implements HandlerInterface
{
    /**
     * FileHandler constructor.
     *
     * @param string             $filename
     * @param FormatterInterface $formatter
     */
    public function __construct(
        protected string $filename,
        protected FormatterInterface $formatter = new DefaultFormatter()
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $name, string $level, string $message, array $context = []): void
    {
        $data = $this->formatter->interpolate($name, $level, $message, $context);

        file_put_contents($this->filename, $data . PHP_EOL, FILE_APPEND);
    }
}
