<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Models\Pronostic;
use App\Models\PrizeWinner;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculatePronosticWinners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pronostic:calculate-winners {--match= : ID du match spécifique à traiter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculer les gagnants pour les matchs terminés et envoyer les notifications';

    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        parent::__construct();
        $this->whatsapp = $whatsapp;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏆 Calcul des gagnants en cours...');

        // Si un match spécifique est fourni
        if ($matchId = $this->option('match')) {
            $matches = FootballMatch::where('id', $matchId)
                ->where('status', 'finished')
                ->get();

            if ($matches->isEmpty()) {
                $this->error("❌ Match #{$matchId} introuvable ou non terminé");
                return Command::FAILURE;
            }
        } else {
            // Récupérer tous les matchs terminés qui n'ont pas encore été traités
            $matches = FootballMatch::where('status', 'finished')
                ->whereNotNull('score_a')
                ->whereNotNull('score_b')
                ->where('winners_calculated', false)
                ->get();
        }

        if ($matches->isEmpty()) {
            $this->info('✅ Aucun match à traiter');
            return Command::SUCCESS;
        }

        $this->info("📊 {$matches->count()} match(s) à traiter");

        $totalWinners = 0;
        $totalPrizesAwarded = 0;

        foreach ($matches as $match) {
            $this->line("\n⚽ Traitement: {$match->team_a} vs {$match->team_b}");
            $this->line("   Score: {$match->score_a} - {$match->score_b}");

            // Trouver tous les pronostics pour ce match
            $allPronostics = Pronostic::where('match_id', $match->id)->get();

            if ($allPronostics->isEmpty()) {
                $this->warn("   ⚠️ Aucun pronostic pour ce match");
                $match->update(['winners_calculated' => true]);
                continue;
            }

            // Trouver les pronostics gagnants (score exact)
            $winningPronostics = $allPronostics->filter(function($prono) use ($match) {
                return $prono->predicted_score_a == $match->score_a
                    && $prono->predicted_score_b == $match->score_b;
            });

            $winnersCount = $winningPronostics->count();
            $participantsCount = $allPronostics->count();

            $this->info("   📈 {$participantsCount} participants, {$winnersCount} gagnant(s)");

            if ($winnersCount > 0) {
                // Marquer les pronostics comme gagnants
                foreach ($winningPronostics as $prono) {
                    $prono->update(['is_winner' => true]);
                }

                // Attribuer les prix si définis
                if ($match->prize_id) {
                    foreach ($winningPronostics as $prono) {
                        $prizeWinner = PrizeWinner::create([
                            'user_id' => $prono->user_id,
                            'prize_id' => $match->prize_id,
                            'match_id' => $match->id,
                        ]);

                        $totalPrizesAwarded++;

                        $this->line("   🎁 Prix attribué à {$prono->user->name}");
                    }
                }

                // Envoyer notifications WhatsApp aux gagnants
                foreach ($winningPronostics as $prono) {
                    try {
                        $this->sendWinnerNotification($prono->user, $match);
                        $this->line("   ✅ Notification envoyée à {$prono->user->name}");
                    } catch (\Exception $e) {
                        $this->error("   ❌ Erreur notification pour {$prono->user->name}: {$e->getMessage()}");
                        Log::error("Winner notification error", [
                            'user_id' => $prono->user_id,
                            'match_id' => $match->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $totalWinners += $winnersCount;
            }

            // Marquer le match comme traité
            $match->update(['winners_calculated' => true]);

            Log::info('Pronostic winners calculated', [
                'match_id' => $match->id,
                'participants' => $participantsCount,
                'winners' => $winnersCount,
            ]);
        }

        $this->newLine();
        $this->info("✅ Traitement terminé !");
        $this->info("🏆 Total gagnants: {$totalWinners}");
        $this->info("🎁 Total prix attribués: {$totalPrizesAwarded}");

        return Command::SUCCESS;
    }

    /**
     * Envoyer notification WhatsApp au gagnant
     */
    protected function sendWinnerNotification($user, $match)
    {
        $message = "🎉 *FÉLICITATIONS !* 🎉\n\n";
        $message .= "Tu as GAGNÉ ton pronostic !\n\n";
        $message .= "⚽ *Match:* {$match->team_a} vs {$match->team_b}\n";
        $message .= "📊 *Score final:* {$match->score_a} - {$match->score_b}\n\n";

        if ($match->prize_id) {
            $prize = $match->prize;
            $message .= "🎁 *Tu as gagné:* {$prize->name} !\n";
            $message .= "💰 Valeur: {$prize->value} {$prize->currency}\n\n";
            $message .= "📍 Pour récupérer ton prix, contacte-nous ou consulte les instructions dans l'app.\n\n";
        } else {
            $message .= "🏆 Continue comme ça pour gagner encore plus de prix !\n\n";
        }

        $message .= "💡 Envoie MENU pour faire d'autres pronostics !";

        $this->whatsapp->sendMessage($user->phone, $message);
    }
}
