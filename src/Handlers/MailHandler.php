<?php

declare(strict_types=1);

namespace Zaphyr\Logger\Handlers;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Contracts\HandlerInterface;
use Zaphyr\Logger\Formatters\LineFormatter;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class MailHandler implements HandlerInterface
{
    /**
     * @param MailerInterface    $mailer
     * @param Email              $email
     * @param FormatterInterface $formatter
     */
    public function __construct(
        protected MailerInterface $mailer,
        protected Email $email,
        protected FormatterInterface $formatter = new LineFormatter()
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws TransportExceptionInterface
     */
    public function add(string $name, string $level, string $message, array $context = []): void
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
