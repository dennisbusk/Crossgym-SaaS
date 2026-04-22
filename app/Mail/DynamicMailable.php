<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DynamicMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailTemplate $template,
        public array $data = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template->renderSubject($this->data),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->template->render($this->data),
        );
    }
}
