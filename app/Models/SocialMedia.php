<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory;

    protected $table = 'social_medias';

    protected $fillable = [
        'type',
        'country_code',
        'phone_number',
        'url',
        'status',
        'sort_order',
    ];

    public function getFormattedLinkAttribute(): string
    {
        if ($this->type === 'whatsapp') {
            $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone_number);
            $cleanCountry = preg_replace('/[^0-9]/', '', $this->country_code);
            return 'https://wa.me/' . $cleanCountry . $cleanPhone;
        }

        return $this->url ?? '#';
    }
}
