<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Stringable;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Formatters\LineFormatter;
use Zaphyr\Logger\Level;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class MailHandler extends AbstractHandler
{
    /**
     * @param MailerInterface    $mailer
     * @param Email              $email
     * @param FormatterInterface $formatter
     * @param Level              $level
     */
    public function __construct(
        protected MailerInterface $mailer,
        protected Email $email,
        FormatterInterface $formatter = new LineFormatter(),
        Level $level = Level::DEBUG
    ) {
        parent::__construct($formatter, $level);
    }

    /**
     * {@inheritdoc}
     *
     * @throws TransportExceptionInterface if the email cannot be sent
     */
    protected function write(string $name, string $level, string|Stringable $message, array $context = []): void
    {
        $message = $this->formatter->interpolate($name, $level, $message, $context);
        $email = $this->isHtml($message) ? $this->email->html($message) : $this->email->text($message);

        $this->mailer->send($email);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    protected function isHtml(string $message): bool
    {
        return $message !== strip_tags($message);
    }
}
