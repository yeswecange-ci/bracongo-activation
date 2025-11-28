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
        'match_id',
        'scheduled_at',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'audience_type',
        'audience_status',
        'message',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Relations
    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function match()
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
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
