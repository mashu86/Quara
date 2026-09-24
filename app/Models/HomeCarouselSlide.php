<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeCarouselSlide extends Model
{
    protected function casts(): array
    {
        return ['overlay_items' => 'array'];
    }

    protected $fillable = [
        'section_key', 'heading', 'heading_color', 'heading_font', 'subheading', 'subheading_color',
        'image_mime', 'image_blob', 'sort_order', 'status', 'heading_size', 'subheading_font',
        'subheading_size', 'button_text', 'link_url', 'text_x', 'text_y', 'heading_x', 'heading_y',
        'subheading_x', 'subheading_y', 'button_x', 'button_y', 'overlay_items',
    ];

    public function getImageUrlAttribute(): string
    {
        return route('home_carousel.image', $this->id);
    }
}
