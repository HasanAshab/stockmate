<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: '.$this->product->name)
            ->line('The product "'.$this->product->name.'" (SKU: '.$this->product->sku.') has dropped below its reorder threshold.')
            ->line('Current Quantity: '.$this->product->quantity)
            ->line('Reorder Threshold: '.$this->product->reorder_threshold)
            ->action('View Product', url('/products/'.$this->product->id))
            ->line('Please restock this item soon.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'message' => 'Product '.$this->product->name.' is low on stock ('.$this->product->quantity.' remaining).',
        ];
    }
}
