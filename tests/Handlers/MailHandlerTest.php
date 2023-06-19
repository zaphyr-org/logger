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

    public function testAddWithTextEmail(): void
    {
        $email = (new Email())->text($content = 'This is a test log');

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

        self::assertSame($content, $email->getTextBody());
        self::assertNull($email->getHtmlBody());
    }

    public function testAddWithHtmlEmail(): void
    {
        $email = (new Email())->html($content = '<p>This is a test log</p>');

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

        self::assertSame($content, $email->getHtmlBody());
        self::assertNull($email->getTextBody());
    }
}
