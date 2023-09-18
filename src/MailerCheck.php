<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class MailerCheck extends Check
{
    /**
     * @var string
     */
    protected string $mailTo = 'laravel-health@example.net';

    /**
     * @var string|null
     */
    protected ?string $mailer = null;

    /**
     * @param string $mailTo
     * @return self
     */
    public function mailTo(string $mailTo): self
    {
        $this->mailTo = $mailTo;

        return $this;
    }

    /**
     * @param ?string $mailer
     * @return self
     */
    public function mailer(?string $mailer): self
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        try {
            \Mail::mailer($this->mailer ?? config('mail.default'))
                ->to($this->mailTo)
                ->send(new \AnourValar\LaravelHealth\Mail\MailerCheckMail());
        } catch (\Throwable $e) {
            return $result->failed($e->getMessage());
        }

        return $result->ok();
    }
}
