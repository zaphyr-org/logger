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
class RotateHandler extends AbstractFileHandler
{
    /**
     * @const string
     */
    public const INTERVAL_HOUR = 'hour';

    /**
     * @const string
     */
    public const INTERVAL_DAY = 'day';

    /**
     * @const string
     */
    public const INTERVAL_WEEK = 'week';

    /**
     * @const string
     */
    public const INTERVAL_MONTH = 'month';

    /**
     * @const string
     */
    public const INTERVAL_YEAR = 'year';

    /**
     * @param string             $dir
     * @param self::INTERVAL_*   $interval
     * @param FormatterInterface $formatter
     * @param Level              $level
     */
    public function __construct(
        protected string $dir,
        protected string $interval = 'day',
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
    public function write(string $name, string $level, string|Stringable $message, array $context = []): void
    {
        $data = $this->formatter->interpolate($name, $level, $message, $context);
        $filename = $this->dir . $this->getIntervalFilename() . '.log';
        $this->createMissingLogDirectory($filename);

        file_put_contents($filename, $data . PHP_EOL, FILE_APPEND);
    }

    /**
     * @throws LoggerException
     * @return string
     */
    protected function getIntervalFilename(): string
    {
        $method = 'create' . ucfirst(strtolower($this->interval)) . 'IntervalFilename';

        if (!method_exists($this, $method)) {
            throw new LoggerException('Log interval "' . strtolower($this->interval) . '" is not valid');
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
