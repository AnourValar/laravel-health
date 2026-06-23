<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\MailerCheck;

class MailerCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'array',
            'mail.mailers.array' => ['transport' => 'array'],
        ]);
    }

    /**
     * Sending through the in-memory "array" transport succeeds.
     *
     * @return void
     */
    public function test_ok_when_mail_can_be_sent(): void
    {
        $result = (new MailerCheck())->run();

        $this->assertCheckOk($result);
    }

    /**
     * A custom mailer name is honoured.
     *
     * @return void
     */
    public function test_ok_with_explicit_mailer(): void
    {
        $result = (new MailerCheck())->mailer('array')->mailTo('someone@example.net')->run();

        $this->assertCheckOk($result);
    }

    /**
     * An undefined mailer makes the check fail with the thrown message.
     *
     * @return void
     */
    public function test_fails_for_undefined_mailer(): void
    {
        $result = (new MailerCheck())->mailer('does_not_exist')->run();

        $this->assertCheckFailed($result);
        $this->assertStringContainsString('does_not_exist', $result->notificationMessage);
    }
}
