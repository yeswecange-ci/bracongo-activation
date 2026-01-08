<?php

/**
 * Script de vérification des matchs nuls dans la base de données
 *
 * Ce script vérifie si les pronostics pour les matchs nuls (1-1, 2-2, etc.)
 * ont été correctement évalués
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FootballMatch;
use App\Models\Pronostic;

echo "🔍 VÉRIFICATION DES MATCHS NULS DANS LA BASE DE DONNÉES\n";
echo str_repeat("=", 70) . "\n\n";

// Trouver tous les matchs terminés avec des scores égalitaires (matchs nuls)
$drawMatches = FootballMatch::where('status', 'finished')
    ->whereNotNull('score_a')
    ->whereNotNull('score_b')
    ->whereRaw('score_a = score_b')
    ->orderBy('match_date', 'desc')
    ->get();

if ($drawMatches->isEmpty()) {
    echo "ℹ️  Aucun match nul terminé trouvé dans la base de données.\n";
    exit(0);
}

echo "📊 Nombre de matchs nuls trouvés: {$drawMatches->count()}\n\n";

$totalProblems = 0;
$totalPronostics = 0;
$totalCorrectlyMarked = 0;
$totalIncorrectlyMarked = 0;

foreach ($drawMatches as $match) {
    echo "⚽ Match #{$match->id}: {$match->team_a} vs {$match->team_b}\n";
    echo "   📅 Date: " . $match->match_date->format('d/m/Y H:i') . "\n";
    echo "   📊 Score: {$match->score_a}-{$match->score_b} (MATCH NUL)\n";

    // Trouver tous les pronostics pour ce match
    $pronostics = Pronostic::where('match_id', $match->id)->get();

    if ($pronostics->isEmpty()) {
        echo "   ⚠️  Aucun pronostic pour ce match\n\n";
        continue;
    }

    echo "   👥 Nombre de pronostics: {$pronostics->count()}\n";

    // Vérifier chaque pronostic
    $drawPronostics = $pronostics->filter(function($prono) {
        // Vérifier si c'est un pronostic "match nul"
        if ($prono->prediction_type === 'draw') {
            return true;
        }

        // Ou si les scores prédits sont égaux
        if ($prono->predicted_score_a !== null &&
            $prono->predicted_score_b !== null &&
            $prono->predicted_score_a == $prono->predicted_score_b) {
            return true;
        }

        return false;
    });

    echo "   🎯 Pronostics 'match nul': {$drawPronostics->count()}\n";

    $problems = [];

    foreach ($drawPronostics as $prono) {
        $totalPronostics++;

        $pronoDisplay = $prono->prediction_type === 'draw'
            ? "draw ({$prono->predicted_score_a}-{$prono->predicted_score_b})"
            : "{$prono->predicted_score_a}-{$prono->predicted_score_b}";

        // Ces pronostics DEVRAIENT être gagnants
        $shouldBeWinner = true;

        // Vérifier s'ils sont correctement marqués
        if ($prono->is_winner) {
            $totalCorrectlyMarked++;
            echo "      ✅ User #{$prono->user_id} - Pronostic: {$pronoDisplay} - Gagnant: OUI ({$prono->points_won} pts)\n";
        } else {
            $totalIncorrectlyMarked++;
            $totalProblems++;
            echo "      ❌ User #{$prono->user_id} - Pronostic: {$pronoDisplay} - Gagnant: NON (PROBLÈME!)\n";

            $problems[] = [
                'pronostic_id' => $prono->id,
                'user_id' => $prono->user_id,
                'prediction' => $pronoDisplay,
                'is_winner' => $prono->is_winner,
                'points_won' => $prono->points_won,
            ];
        }
    }

    if (!empty($problems)) {
        echo "\n   🚨 PROBLÈMES DÉTECTÉS POUR CE MATCH:\n";
        foreach ($problems as $problem) {
            echo "      - Pronostic #{$problem['pronostic_id']} (User #{$problem['user_id']})\n";
            echo "        Prédiction: {$problem['prediction']}\n";
            echo "        is_winner: " . ($problem['is_winner'] ? 'true' : 'false') . "\n";
            echo "        points_won: {$problem['points_won']}\n";
        }
    }

    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "📈 RÉSUMÉ\n";
echo "   🎯 Total pronostics 'match nul' vérifiés: {$totalPronostics}\n";
echo "   ✅ Correctement marqués comme gagnants: {$totalCorrectlyMarked}\n";
echo "   ❌ Incorrectement marqués (non gagnants): {$totalIncorrectlyMarked}\n";
echo "   🚨 Total de problèmes: {$totalProblems}\n\n";

if ($totalProblems > 0) {
    echo "⚠️  ATTENTION: {$totalProblems} pronostic(s) 'match nul' n'ont pas été correctement évalués!\n";
    echo "💡 Solution: Exécutez la commande suivante pour recalculer:\n";
    echo "   php artisan pronostic:recalculate-all --force\n\n";
} else {
    echo "🎉 PARFAIT: Tous les pronostics 'match nul' ont été correctement évalués!\n\n";
}

// Vérifier aussi les pronostics qui ne sont PAS "match nul" mais qui sont marqués comme gagnants
echo str_repeat("=", 70) . "\n";
echo "🔍 VÉRIFICATION INVERSE: Pronostics NON 'match nul' marqués comme gagnants\n\n";

$inverseProblems = 0;

foreach ($drawMatches as $match) {
    $pronostics = Pronostic::where('match_id', $match->id)
        ->where('is_winner', true)
        ->get();

    foreach ($pronostics as $prono) {
        // Vérifier si c'est un pronostic qui N'EST PAS "match nul"
        $isDrawPrediction = false;

        if ($prono->prediction_type === 'draw') {
            $isDrawPrediction = true;
        } elseif ($prono->predicted_score_a !== null &&
                  $prono->predicted_score_b !== null &&
                  $prono->predicted_score_a == $prono->predicted_score_b) {
            $isDrawPrediction = true;
        }

        // Si ce n'est pas un pronostic "match nul" mais qu'il est marqué comme gagnant, c'est un problème
        if (!$isDrawPrediction) {
            $inverseProblems++;
            echo "❌ Match #{$match->id}: User #{$prono->user_id} - Pronostic: {$prono->predicted_score_a}-{$prono->predicted_score_b} (type: {$prono->prediction_type})\n";
            echo "   Score final: {$match->score_a}-{$match->score_b} (match nul)\n";
            echo "   Ce pronostic NE devrait PAS être gagnant!\n\n";
        }
    }
}

if ($inverseProblems > 0) {
    echo "⚠️  ATTENTION: {$inverseProblems} pronostic(s) NON 'match nul' ont été incorrectement marqués comme gagnants!\n\n";
} else {
    echo "✅ Aucun pronostic incorrect marqué comme gagnant.\n\n";
}

echo str_repeat("=", 70) . "\n";
echo "🏁 Vérification terminée.\n";
