<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Handlers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Handlers\MailHandler;

class MailHandlerTest extends TestCase
{
    /* -------------------------------------------------
     * ADD
     * -------------------------------------------------
     */

    public function testAdd(): void
    {
        $email = $this->createMock(Email::class);
        $email->expects(self::once())
            ->method('text')
            ->with($content = 'This is a test log')
            ->willReturn($email);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with($email);

        $formatter = $this->createMock(FormatterInterface::class);
        $formatter->expects($this->once())
            ->method('interpolate')
            ->with($name = 'app', $level = 'INFO')
            ->willReturn($content);

        $mailHandler = new MailHandler($mailer, $email, $formatter);
        $mailHandler->add($name, $level, $content);
    }
}
