<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category',
        'header_type',
        'header_text',
        'header_media_path',
        'body',
        'footer',
        'buttons',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'buttons' => 'array',
        'is_active' => 'boolean',
    ];

    // Relations
    public function campaignMessages()
    {
        return $this->hasMany(CampaignMessage::class, 'template_id');
    }

    // Méthode pour remplacer les variables dans le template
    public function render($data = [])
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        return $body;
    }

    // Méthode pour obtenir l'URL du media header
    public function getHeaderMediaUrlAttribute()
    {
        if (!$this->header_media_path) {
            return null;
        }

        return \Storage::disk('public')->url($this->header_media_path);
    }
}
