<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The invoice instance.
     *
     * @var mixed
     */
    public $invoice;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $invoice
     * @return void
     */
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
        $this->afterCommit();

        // Optional: Customizing the queue connection for this notification
        // $this->onConnection('redis');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  object  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Determine channels based on notifiable preferences
        // return $notifiable->prefers_sms ? ['vonage'] : ['mail', 'database'];
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  object  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The invoice has been paid.')
                    ->action('View Invoice', url('/invoices/' . data_get($this->invoice, 'id', '')))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => data_get($this->invoice, 'id'),
            'amount' => data_get($this->invoice, 'amount', 0),
        ];
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @param  object  $notifiable
     * @return array<string, \Illuminate\Support\Carbon>
     */
    public function withDelay(object $notifiable): array
    {
        return [
            'mail' => now()->addMinutes(5),
        ];
    }

    /**
     * Determine if the notification should be sent.
     *
     * @param  object  $notifiable
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        // Ensure invoice is an object and check isPaid condition safely
        return is_object($this->invoice) && (! method_exists($this->invoice, 'isPaid') || $this->invoice->isPaid());
    }

    /**
     * Handle the notification after it has been sent.
     *
     * @param  object  $notifiable
     * @param  string  $channel
     * @param  mixed  $response
     * @return void
     */
    public function afterSending(object $notifiable, string $channel, mixed $response): void
    {
        //
    }
}