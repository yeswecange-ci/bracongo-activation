<?php

namespace App\Console\Commands;

use App\Models\Commercant;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LckMorningReminder extends Command
{
    protected $signature   = 'lck:morning-reminder';
    protected $description = 'Envoie un rappel WhatsApp+email aux commercants pour activer leur disponibilité';

    public function handle(WhatsAppService $whatsapp): void
    {
        $botNumber  = preg_replace('/^whatsapp:\+?/', '', config('services.twilio.whatsapp_from', '243841622222'));
        $waLink     = 'https://wa.me/' . $botNumber . '?text=ONLINE';
        $dashLink   = rtrim(config('app.url'), '/') . '/commercant/dashboard';

        $commercants = Commercant::where('is_active', true)->whereNotNull('phone')->get();

        foreach ($commercants as $c) {
            // WhatsApp reminder (fonctionne si session encore active de la veille)
            $whatsapp->sendMessage($c->phone,
                "☀️ *Bonjour {$c->name} !*\n\n"
                . "N'oubliez pas de vous connecter pour recevoir les commandes d'aujourd'hui.\n\n"
                . "👉 Tapez *ONLINE* ici ou appuyez sur ce lien :\n{$waLink}"
            );

            // Email de rappel (toujours livré, pas de session requise)
            try {
                $html = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;background:#f8f6f2;padding:32px'>"
                    . "<div style='max-width:480px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden'>"
                    . "<div style='background:#1a1a1a;padding:24px;text-align:center'>"
                    . "<p style='color:#c9a84c;font-size:18px;font-weight:900;margin:0'>☀️ Rappel du matin</p>"
                    . "<p style='color:#fff;opacity:0.5;font-size:12px;margin:4px 0 0'>La Clé des Châteaux</p></div>"
                    . "<div style='padding:28px'>"
                    . "<p style='font-size:15px;color:#1a1a1a'>Bonjour <strong>{$c->name}</strong>,</p>"
                    . "<p style='color:#555;font-size:14px;line-height:1.6'>Activez votre disponibilité pour commencer à recevoir les commandes du jour.</p>"
                    . "<div style='text-align:center;margin:28px 0'>"
                    . "<a href='{$waLink}' style='display:inline-block;background:#25D366;color:#fff;text-decoration:none;"
                    . "padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px'>💬 S'activer sur WhatsApp</a></div>"
                    . "<p style='text-align:center;font-size:12px;color:#aaa'>Ou connectez-vous au <a href='{$dashLink}' style='color:#c9a84c'>tableau de bord</a></p>"
                    . "</div></div></body></html>";

                Mail::html($html, fn($m) => $m
                    ->to($c->email, $c->name)
                    ->subject("☀️ Activez vos notifications — La Clé des Châteaux")
                );
            } catch (\Exception $e) {
                Log::error("LCK Morning Reminder email failed for {$c->email}: " . $e->getMessage());
            }
        }

        $this->info("✅ Rappel envoyé à {$commercants->count()} commercant(s).");
        Log::info("LCK Morning Reminder: rappel envoyé à {$commercants->count()} commercant(s).");
    }
}
