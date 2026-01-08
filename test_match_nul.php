<?php

/**
 * Script de test pour vérifier la logique des matchs nuls
 *
 * Ce script teste si les pronostics "match nul" sont correctement évalués
 * quand le score final est égalitaire (1-1, 2-2, 3-3, etc.)
 */

echo "🧪 TEST DE LA LOGIQUE DES MATCHS NULS\n";
echo str_repeat("=", 60) . "\n\n";

/**
 * Simuler la méthode getMatchResult des commandes
 */
function getMatchResult($scoreA, $scoreB) {
    if ($scoreA > $scoreB) {
        return 'team_a_win';
    } elseif ($scoreB > $scoreA) {
        return 'team_b_win';
    } else {
        return 'draw';
    }
}

/**
 * Simuler la méthode getResultFromScores des commandes
 */
function getResultFromScores($scoreA, $scoreB) {
    if ($scoreA > $scoreB) {
        return 'team_a_win';
    } elseif ($scoreB > $scoreA) {
        return 'team_b_win';
    } else {
        return 'draw';
    }
}

/**
 * Simuler la méthode checkPronostic des commandes
 */
function checkPronostic($prono, $match, $matchResult) {
    // Mode 1: Pronostic avec scores
    if ($prono['predicted_score_a'] !== null && $prono['predicted_score_b'] !== null) {
        // Score exact ?
        if ($prono['predicted_score_a'] == $match['score_a'] && $prono['predicted_score_b'] == $match['score_b']) {
            return 'exact';
        }

        // Bon résultat (victoire/nul) ?
        $pronoResult = getResultFromScores($prono['predicted_score_a'], $prono['predicted_score_b']);
        if ($pronoResult === $matchResult) {
            return 'good_result';
        }

        return 'wrong';
    }

    // Mode 2: Pronostic simple (prediction_type)
    if ($prono['prediction_type']) {
        if ($prono['prediction_type'] === $matchResult) {
            return 'good_result';
        }

        return 'wrong';
    }

    return 'wrong';
}

// Scénarios de test
$scenarios = [
    [
        'description' => "Match nul 0-0, pronostic 'draw' (0-0)",
        'match' => ['score_a' => 0, 'score_b' => 0],
        'prono' => ['predicted_score_a' => 0, 'predicted_score_b' => 0, 'prediction_type' => 'draw'],
        'expected' => 'exact',
    ],
    [
        'description' => "Match nul 1-1, pronostic 'draw' (0-0)",
        'match' => ['score_a' => 1, 'score_b' => 1],
        'prono' => ['predicted_score_a' => 0, 'predicted_score_b' => 0, 'prediction_type' => 'draw'],
        'expected' => 'good_result',
    ],
    [
        'description' => "Match nul 2-2, pronostic 'draw' (0-0)",
        'match' => ['score_a' => 2, 'score_b' => 2],
        'prono' => ['predicted_score_a' => 0, 'predicted_score_b' => 0, 'prediction_type' => 'draw'],
        'expected' => 'good_result',
    ],
    [
        'description' => "Match nul 3-3, pronostic 'draw' (0-0)",
        'match' => ['score_a' => 3, 'score_b' => 3],
        'prono' => ['predicted_score_a' => 0, 'predicted_score_b' => 0, 'prediction_type' => 'draw'],
        'expected' => 'good_result',
    ],
    [
        'description' => "Match nul 1-1, pronostic 1-1 (score exact)",
        'match' => ['score_a' => 1, 'score_b' => 1],
        'prono' => ['predicted_score_a' => 1, 'predicted_score_b' => 1, 'prediction_type' => null],
        'expected' => 'exact',
    ],
    [
        'description' => "Match nul 2-2, pronostic 1-1 (bon résultat)",
        'match' => ['score_a' => 2, 'score_b' => 2],
        'prono' => ['predicted_score_a' => 1, 'predicted_score_b' => 1, 'prediction_type' => null],
        'expected' => 'good_result',
    ],
    [
        'description' => "Match victoire 2-1, pronostic 'draw' (mauvais)",
        'match' => ['score_a' => 2, 'score_b' => 1],
        'prono' => ['predicted_score_a' => 0, 'predicted_score_b' => 0, 'prediction_type' => 'draw'],
        'expected' => 'wrong',
    ],
    [
        'description' => "Match nul 1-1, pronostic 'team_a_win' (mauvais)",
        'match' => ['score_a' => 1, 'score_b' => 1],
        'prono' => ['predicted_score_a' => 1, 'predicted_score_b' => 0, 'prediction_type' => 'team_a_win'],
        'expected' => 'wrong',
    ],
];

$testsPassed = 0;
$testsFailed = 0;

foreach ($scenarios as $index => $scenario) {
    $testNumber = $index + 1;
    echo "📋 Test #{$testNumber}: {$scenario['description']}\n";
    echo "   Match: {$scenario['match']['score_a']}-{$scenario['match']['score_b']}\n";
    echo "   Pronostic: ";

    if ($scenario['prono']['prediction_type']) {
        echo "{$scenario['prono']['prediction_type']} ({$scenario['prono']['predicted_score_a']}-{$scenario['prono']['predicted_score_b']})\n";
    } else {
        echo "{$scenario['prono']['predicted_score_a']}-{$scenario['prono']['predicted_score_b']}\n";
    }

    $matchResult = getMatchResult($scenario['match']['score_a'], $scenario['match']['score_b']);
    $result = checkPronostic($scenario['prono'], $scenario['match'], $matchResult);

    echo "   Résultat obtenu: $result\n";
    echo "   Résultat attendu: {$scenario['expected']}\n";

    if ($result === $scenario['expected']) {
        echo "   ✅ PASS\n\n";
        $testsPassed++;
    } else {
        echo "   ❌ FAIL\n\n";
        $testsFailed++;
    }
}

echo str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ\n";
echo "   ✅ Tests réussis: $testsPassed\n";
echo "   ❌ Tests échoués: $testsFailed\n";
echo "   📈 Total: " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n🎉 TOUS LES TESTS SONT PASSÉS ! La logique des matchs nuls fonctionne correctement.\n";
} else {
    echo "\n⚠️  ATTENTION : Certains tests ont échoué. Il y a un problème dans la logique.\n";
}
