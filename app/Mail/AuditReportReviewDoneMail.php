<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditReportReviewDoneMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fromName,
        public string $fromEmail,
        public string $subjectLine,
        public string $bodyText,
        public ?string $reportPdfBinary = null,
        public ?string $reportPdfFilename = null,
        public ?string $commentsPdfBinary = null,
        public ?string $commentsPdfFilename = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromEmail, $this->fromName),
            replyTo: [new Address($this->fromEmail, $this->fromName)],
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.audit-report-sent',
            text: 'emails.audit-report-sent-text',
            with: [
                'bodyText' => $this->bodyText,
                'fromName' => $this->fromName,
                'subjectLine' => $this->subjectLine,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $files = [];

        if ($this->reportPdfBinary !== null && $this->reportPdfBinary !== '') {
            $files[] = Attachment::fromData(
                fn () => $this->reportPdfBinary,
                $this->reportPdfFilename ?: 'audit-report.pdf'
            )->withMime('application/pdf');
        }

        if ($this->commentsPdfBinary !== null && $this->commentsPdfBinary !== '') {
            $files[] = Attachment::fromData(
                fn () => $this->commentsPdfBinary,
                $this->commentsPdfFilename ?: 'review-comments.pdf'
            )->withMime('application/pdf');
        }

        return $files;
    }
}
