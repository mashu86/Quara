<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content_html',
        'custom_css',
        'image_position',
        'image_mime',
        'image_blob',
        'status',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_mime && ($this->image_blob || $this->id)) {
            return route('home_content.image', $this->id);
        }
        return null;
    }
}
