<?php

namespace App\Console\Commands;

use App\Models\ConversationSession;
use Illuminate\Console\Command;

class CleanExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoyer les sessions de conversation expirées (plus de 24h sans utilisateur)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Nettoyage des sessions expirées...');

        $count = ConversationSession::cleanExpired();

        $this->info("✅ {$count} session(s) nettoyée(s) avec succès.");

        return Command::SUCCESS;
    }
}
