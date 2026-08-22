<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $trackingUrl;

    public function __construct(Order $order)
    {
        $this->order = $order->load('items');
        $this->trackingUrl = route('order-tracking', [
            'order_number' => $order->order_number,
            'phone' => $order->customer_phone,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Cancellation Notice - {$this->order->order_number} | QUARA WALDROP",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_cancelled',
        );
    }
}
