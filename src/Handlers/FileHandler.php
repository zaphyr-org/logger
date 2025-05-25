<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Stringable;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Exceptions\LoggerException;
use Zaphyr\Logger\Formatters\LineFormatter;
use Zaphyr\Logger\Level;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class FileHandler extends AbstractFileHandler
{
    /**
     * @param string             $filename
     * @param FormatterInterface $formatter
     * @param Level              $level
     */
    public function __construct(
        protected string $filename,
        FormatterInterface $formatter = new LineFormatter(),
        Level $level = Level::DEBUG
    ) {
        parent::__construct($formatter, $level);
    }

    /**
     * {@inheritdoc}
     *
     * @throws LoggerException if the log directory cannot be created
     */
    protected function write(string $name, string $level, string|Stringable $message, array $context = []): void
    {
        $data = $this->formatter->interpolate($name, $level, $message, $context);
        $this->createMissingLogDirectory($this->filename);

        file_put_contents($this->filename, $data . PHP_EOL, FILE_APPEND);
    }
}
