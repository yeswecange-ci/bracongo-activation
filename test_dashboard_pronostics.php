<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pronostic;

echo "=== TEST DASHBOARD PRONOSTICS ===\n\n";

// Simuler la requête du contrôleur
$query = Pronostic::with(['user', 'match'])
    ->orderBy('created_at', 'desc');

$pronostics = $query->paginate(20);

echo "Total pronostics: " . $pronostics->total() . "\n";
echo "Page actuelle: " . $pronostics->currentPage() . "\n";
echo "Par page: " . $pronostics->perPage() . "\n";
echo "Nombre sur cette page: " . $pronostics->count() . "\n\n";

echo "Liste des pronostics sur la page 1:\n";
echo "====================================\n\n";

foreach ($pronostics as $prono) {
    echo "ID: {$prono->id}\n";
    echo "User: {$prono->user->name} ({$prono->user->phone})\n";
    echo "Match: {$prono->match->team_a} vs {$prono->match->team_b}\n";
    echo "Prediction: {$prono->prediction_text}\n";
    echo "Date: {$prono->created_at->format('d/m/Y H:i:s')}\n";
    echo "---\n";
}
