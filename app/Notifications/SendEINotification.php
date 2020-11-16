<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEINotification extends Notification
{
    use Queueable;
    private $orderEcommerce;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($orderEcommerce)
    {
        //
        $this->orderEcommerce = $orderEcommerce;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        try {
            $emailResult =  (new MailMessage)
                ->subject('Comprobante de pago electrónico')
                ->line('¡Gracias por tu compra! Te enviamos tu comprobante de pago electrónico')
                ->action('Gracias', url("https://mehperu.com"))
                ->line('Gracias por tu preferencia')
                ->view('mails.invoice', [ "order" => $this->orderEcommerce]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
