<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Formatters;

use Psr\Log\LogLevel;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class HtmlFormatter extends AbstractFormatter
{
    /**
     * @param string|null $dateFormat
     * @param int|null    $maxPreviousExceptionDepth
     */
    public function __construct(string|null $dateFormat = null, int|null $maxPreviousExceptionDepth = null)
    {
        parent::__construct($dateFormat, $maxPreviousExceptionDepth);
    }

    /**
     * @var string[]
     */
    protected array $levelColors = [
        LogLevel::EMERGENCY => '#dc3545',
        LogLevel::ALERT => '#dc3545',
        LogLevel::CRITICAL => '#dc3545',
        LogLevel::ERROR => '#dc3545',
        LogLevel::WARNING => '#ffc107',
        LogLevel::NOTICE => '#17a2b8',
        LogLevel::INFO => '#17a2b8',
        LogLevel::DEBUG => '#343a40',
    ];

    /**
     * {@inheritdoc}
     */
    public function interpolate(string $name, string $level, string $message, array $context = []): string
    {
        $normalized = $this->normalize($message, $context);

        $html = $this->title($level);
        $html .= $this->tr($this->th('Name') . $this->td($name));
        $html .= $this->tr($this->th('Message') . $this->td($normalized['message']));
        $html .= $this->tr($this->th('Time') . $this->td($this->getTimestampFromImmutable()));

        if (count($normalized['context']) > 0) {
            $contextHtml = '';

            foreach ($normalized['context'] as $key => $value) {
                $contextHtml .= $this->tr(
                    $this->th((string)$key) . $this->td($this->normalizeValue($value))
                );
            }

            $html .= $this->tr($this->th('Context') . $this->td($this->tableEmbedded($contextHtml), false));
        }

        if (count($normalized['exceptions']) > 0) {
            $exceptionHtml = '';

            foreach (explode(', ', $this->getExceptionsAsString($normalized['exceptions'])) as $exception) {
                $exceptionHtml .= $this->tr($this->td($exception));
            }

            $html .= $this->tr($this->th('Exceptions') . $this->td($this->tableEmbedded($exceptionHtml), false));
        }

        return $this->table($html);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function table(string $content): string
    {
        return '<table style="width:100%;font-family:sans-serif">' . $content . '</table>';
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function tableEmbedded(string $content): string
    {
        return '<table style="margin-left:-10px;border-spacing:0">' . $content . '</table>';
    }

    /**
     * @param string $level
     *
     * @return string
     */
    protected function title(string $level): string
    {
        $html = '<th colspan="2" style="padding:10px">' . $this->escapeValue(strtoupper($level)) . '</th>';

        return '<tr style="color:white;background:' . $this->levelColors[$level] . ';text-align:left">' . $html . '</tr>';
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function tr(string $content): string
    {
        return '<tr style="color:white;background:#666">' . $content . '</tr>';
    }

    /**
     * @param string $title
     *
     * @return string
     */
    protected function th(string $title): string
    {
        return '<th style="padding:10px;text-align:left">' . $this->escapeValue($title) . ':</th>';
    }

    /**
     * @param string $content
     * @param bool   $escapeContent
     *
     * @return string
     */
    protected function td(string $content, bool $escapeContent = true): string
    {
        if ($escapeContent) {
            $content = $this->escapeValue($content);
        }

        return '<td style="padding:10px">' . $content . '</td>';
    }
}
