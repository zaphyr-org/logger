<?php

declare(strict_types=1);

namespace Zaphyr\LoggerTests\Unit\Handlers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Zaphyr\Logger\Contracts\FormatterInterface;
use Zaphyr\Logger\Handlers\MailHandler;
use Zaphyr\Logger\Level;

class MailHandlerTest extends TestCase
{
    /**
     * @var FormatterInterface&MockObject
     */
    protected FormatterInterface&MockObject $formatterMock;

    /**
     * @var MailerInterface&MockObject
     */
    protected MailerInterface&MockObject $mailerMock;

    protected function setUp(): void
    {
        $this->formatterMock = $this->createMock(FormatterInterface::class);
        $this->mailerMock = $this->createMock(MailerInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->formatterMock, $this->mailerMock);
    }

    /* -------------------------------------------------
     * ADD
     * -------------------------------------------------
     */

    public function testAddWithTextEmail(): void
    {
        $email = (new Email())->text($content = 'This is a test log');

        $this->mailerMock->expects(self::once())
            ->method('send')
            ->with($email);

        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->with($name = 'app', $level = 'INFO')
            ->willReturn($content);

        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock);
        $mailHandler->add($name, $level, $content);

        self::assertSame($content, $email->getTextBody());
        self::assertNull($email->getHtmlBody());
    }

    public function testAddWithHtmlEmail(): void
    {
        $email = (new Email())->html($content = '<p>This is a test log</p>');

        $this->mailerMock->expects(self::once())
            ->method('send')
            ->with($email);

        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->with($name = 'app', $level = 'INFO')
            ->willReturn($content);

        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock);
        $mailHandler->add($name, $level, $content);

        self::assertSame($content, $email->getHtmlBody());
        self::assertNull($email->getTextBody());
    }

    public function testAddWithLowerLevel(): void
    {
        $email = new Email();

        $this->mailerMock->expects(self::never())->method('send');
        $this->formatterMock->expects(self::never())->method('interpolate');

        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, Level::CRITICAL);
        $mailHandler->add('app', 'debug', 'This log should not be sent');
    }

    public function testAddWithHigherLevel(): void
    {
        $email = new Email();
        $name = 'app';
        $level = 'warning';
        $message = 'This is a test log';

        $this->mailerMock->expects(self::once())
            ->method('send')
            ->with($email);

        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->with($name, $level, $message)
            ->willReturn($message);

        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, Level::DEBUG);
        $mailHandler->add($name, $level, $message);
    }

    public function testWithEqualLevel(): void
    {
        $email = new Email();
        $name = 'app';
        $level = 'CRITICAL';
        $message = 'This is a test log';

        $this->mailerMock->expects(self::once())
            ->method('send')
            ->with($email);

        $this->formatterMock->expects(self::once())
            ->method('interpolate')
            ->with($name, $level, $message)
            ->willReturn($message);

        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, Level::CRITICAL);
        $mailHandler->add($name, $level, $message);
    }

    /* -------------------------------------------------
     * GET LEVEL
     * -------------------------------------------------
     */

    public function testGetLevelWithDefaultLevel(): void
    {
        $email = new Email();
        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock);

        self::assertEquals(Level::DEBUG->value, $mailHandler->getLevel()->value);
    }

    public function testGetLevelWithCustomLevel(): void
    {
        $email = new Email();
        $customLevel = Level::fromName(LogLevel::NOTICE);
        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, $customLevel);

        self::assertEquals($customLevel->value, $mailHandler->getLevel()->value);
    }

    /* -------------------------------------------------
     * HAS LEVEL
     * -------------------------------------------------
     */

    public function testHasLevelWithDefaultLevel(): void
    {
        $email = new Email();
        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock);

        self::assertTrue($mailHandler->hasLevel(LogLevel::DEBUG));
    }

    public function testHasLevelWithLowerLevel(): void
    {
        $email = new Email();
        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, Level::ERROR);

        self::assertFalse($mailHandler->hasLevel(LogLevel::DEBUG));
    }

    public function testHasLevelWithHigherLevel(): void
    {
        $email = new Email();
        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, Level::WARNING);

        self::assertTrue($mailHandler->hasLevel(LogLevel::ERROR));
    }

    public function testHasLevelWithEqualLevel(): void
    {
        $email = new Email();
        $mailHandler = new MailHandler($this->mailerMock, $email, $this->formatterMock, Level::INFO);

        self::assertTrue($mailHandler->hasLevel(LogLevel::INFO));
    }
}
