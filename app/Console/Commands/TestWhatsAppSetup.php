<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use App\Models\Village;
use Illuminate\Console\Command;

class TestWhatsAppSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la configuration WhatsApp/Twilio';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsapp)
    {
        $this->info('🔍 Test de la configuration WhatsApp/Twilio...');
        $this->newLine();

        // 1. Vérifier les variables d'environnement
        $this->info('📋 Vérification des variables d\'environnement:');

        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.whatsapp_from');

        $this->checkEnv('TWILIO_ACCOUNT_SID', $accountSid);
        $this->checkEnv('TWILIO_AUTH_TOKEN', $authToken);
        $this->checkEnv('TWILIO_WHATSAPP_FROM', $from);

        $this->newLine();

        // 2. Vérifier le service WhatsApp
        $this->info('🔧 Vérification du service WhatsApp:');

        if ($whatsapp->isConfigured()) {
            $this->line('  <fg=green>✅</> Service configuré');
        } else {
            $this->line('  <fg=red>❌</> Service NON configuré - Vérifier les credentials Twilio');
            return Command::FAILURE;
        }

        $this->newLine();

        // 3. Vérifier les routes
        $this->info('🌐 Vérification des routes API:');

        $routes = [
            'api.whatsapp.webhook' => '/api/webhook/whatsapp',
            'api.whatsapp.status' => '/api/webhook/whatsapp/status',
        ];

        foreach ($routes as $name => $path) {
            if (route($name)) {
                $url = url($path);
                $this->line("  <fg=green>✅</> {$name}");
                $this->line("     {$url}");
            } else {
                $this->line("  <fg=red>❌</> {$name} - Route non trouvée");
            }
        }

        $this->newLine();

        // 4. Vérifier les villages
        $this->info('🏘️ Vérification des villages:');

        $villages = Village::where('is_active', true)->get();

        if ($villages->count() > 0) {
            $this->line("  <fg=green>✅</> {$villages->count()} village(s) actif(s):");
            foreach ($villages as $village) {
                $this->line("     • {$village->name}");
            }
        } else {
            $this->line('  <fg=yellow>⚠️</> Aucun village actif - Créer des villages dans /admin/villages');
        }

        $this->newLine();

        // 5. Test d'envoi (optionnel)
        $phone = $this->argument('phone');

        if ($phone) {
            $this->info('📱 Test d\'envoi de message:');

            $formattedPhone = WhatsAppService::formatPhoneNumber($phone);
            $this->line("  Numéro: {$formattedPhone}");

            if ($this->confirm('Envoyer un message de test à ce numéro ?', false)) {
                $message = "🧪 Test CAN 2025\n\nCeci est un message de test du système WhatsApp.\n\n✅ La configuration fonctionne !";

                if ($whatsapp->sendMessage($formattedPhone, $message)) {
                    $this->line('  <fg=green>✅</> Message envoyé avec succès !');
                } else {
                    $this->line('  <fg=red>❌</> Échec de l\'envoi - Vérifier les logs');
                }
            }
        } else {
            $this->info('💡 Astuce: Testez l\'envoi avec: php artisan whatsapp:test +243XXXXXXXXX');
        }

        $this->newLine();
        $this->info('✅ Test terminé !');
        $this->newLine();

        $this->comment('📖 Pour configurer Twilio, consultez: WHATSAPP_SETUP.md');

        return Command::SUCCESS;
    }

    protected function checkEnv($name, $value)
    {
        if ($value && $value !== '') {
            $masked = $this->maskSecret($value);
            $this->line("  <fg=green>✅</> {$name}: {$masked}");
        } else {
            $this->line("  <fg=red>❌</> {$name}: Non défini");
        }
    }

    protected function maskSecret($value)
    {
        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . str_repeat('*', strlen($value) - 8) . substr($value, -4);
    }
}
