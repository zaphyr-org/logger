<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Psr\Log\InvalidArgumentException;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Formatters\DefaultFormatter;
use Zaphyr\Utils\Str;

/**
 * Class FileRotateHandler.
 *
 * @author merloxx <merloxx@zaphyr.org>
 */
class RotateHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    protected $interval;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @param string                  $dir
     * @param string                  $interval
     * @param FormatterInterface|null $formatter
     */
    public function __construct(
        string $dir,
        string $interval = 'day',
        FormatterInterface $formatter = null
    ) {
        $this->dir = $dir;
        $this->interval = strtolower($interval);
        $this->formatter = $formatter ?: new DefaultFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $name, string $level, string $message, array $context = []): void
    {
        $data = $this->formatter->interpolate($name, $level, $message, $context);
        $filename = $this->dir . DIRECTORY_SEPARATOR . $this->getIntervalFilename() . '.log';

        file_put_contents($filename, $data . PHP_EOL, FILE_APPEND);
    }

    /**
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function getIntervalFilename(): string
    {
        $method = 'create' . Str::studly($this->interval) . 'IntervalFilename';

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException('The interval "' . $this->interval . '" is not valid');
        }

        return $this->{$method}();
    }

    /**
     * @return string
     */
    protected function createHourIntervalFilename(): string
    {
        return 'h' . date('H_Y_m_d');
    }

    /**
     * @return string
     */
    protected function createDayIntervalFilename(): string
    {
        return date('Y_m_d');
    }

    /**
     * @return string
     */
    protected function createWeekIntervalFilename(): string
    {
        return 'w' . date('W_Y');
    }

    /**
     * @return string
     */
    protected function createMonthIntervalFilename(): string
    {
        return 'm' . date('m_Y');
    }

    /**
     * @return string
     */
    protected function createYearIntervalFilename(): string
    {
        return date('Y');
    }
}
