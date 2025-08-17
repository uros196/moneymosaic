<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email notification containing a two-factor authentication code.
 */
class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new two-factor code email message instance.
     *
     * @param string $code The authentication code to be sent
     */
    public function __construct(public string $code) {}

    /**
     * Build the email message.
     *
     * @return self
     */
    public function build(): self
    {
        return $this->subject(__('Your authentication code'))
            ->view('emails.twofactor-code', [
                'code' => $this->code,
            ]);
    }
}
