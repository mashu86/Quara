<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;

    public string $email;

    public string $resetUrl;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
        $this->resetUrl = route('admin.password.reset', ['token' => $token, 'email' => $email]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Request - '.Setting::get('site_name', config('app.name')).' Admin',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_reset_password',
        );
    }
}
