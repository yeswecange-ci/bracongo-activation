<?php

namespace App\Console\Commands;

use App\Models\Commercant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LckDailyReset extends Command
{
    protected $signature   = 'lck:daily-reset';
    protected $description = 'Remet tous les commercants LCK offline à minuit (reset session WhatsApp)';

    public function handle(): void
    {
        $count = Commercant::where('is_online', true)->count();
        Commercant::where('is_online', true)->update(['is_online' => false]);

        Log::info("LCK Daily Reset: {$count} commercant(s) remis offline.");
        $this->info("✅ {$count} commercant(s) remis offline.");
    }
}
