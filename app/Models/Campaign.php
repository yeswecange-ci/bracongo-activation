<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'target_segment',
        'village_id',
        'scheduled_at',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relations
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function messages()
    {
        return $this->hasMany(CampaignMessage::class);
    }

    public function logs()
    {
        return $this->hasMany(MessageLog::class);
    }

    // Accessors
    public function getSuccessRateAttribute()
    {
        if ($this->total_recipients == 0) {
            return 0;
        }
        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }
}
