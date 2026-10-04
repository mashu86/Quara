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
        'image_mime', 'image_blob', 'mobile_image_mime', 'mobile_image_blob', 'sort_order', 'status', 'heading_size', 'subheading_font',
        'subheading_size', 'button_text', 'link_url', 'text_x', 'text_y', 'heading_x', 'heading_y',
        'subheading_x', 'subheading_y', 'button_x', 'button_y', 'overlay_items',
    ];

    public function getImageUrlAttribute(): string
    {
        $v = $this->updated_at ? $this->updated_at->timestamp : time();
        return route('home_carousel.image', ['slide' => $this->id, 'v' => $v]);
    }

    public function getHasMobileImageAttribute(): bool
    {
        return !empty($this->mobile_image_mime) || !empty($this->mobile_image_blob);
    }

    public function getMobileImageUrlAttribute(): string
    {
        if (!$this->has_mobile_image) {
            return $this->image_url;
        }
        $v = $this->updated_at ? $this->updated_at->timestamp : time();
        return route('home_carousel.mobile_image', ['slide' => $this->id, 'v' => $v]);
    }
}
