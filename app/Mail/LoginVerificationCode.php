<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class LoginVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $code;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Build the message.
     */
    public function build(): Mailable
    {
        return $this->subject('Your Login Verification Code - eReligiousServices')
            ->view('emails.login-verification-code')
            ->with([
                'user' => $this->user,
                'code' => $this->code,
            ]);
    }
}
