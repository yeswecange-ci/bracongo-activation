@extends('admin.layouts.app')

@section('title', 'Créer une Campagne')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.campaigns.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Créer une Campagne WhatsApp</h1>
            <p class="text-gray-600 mt-1">Envoyer un message à un groupe d'utilisateurs</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.campaigns.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
            @csrf

            <!-- Informations de base -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de base</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la campagne *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: Match du Jour - 15 Janvier">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Template de message</label>
                    <select name="template_id" id="templateSelect"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Saisie manuelle (sans template) --</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" data-body="{{ $template->body }}" data-variables="{{ json_encode($template->variables) }}">
                                {{ $template->name }} ({{ $template->category }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Optionnel : Utiliser un template pré-enregistré</p>
                </div>
            </div>

            <!-- Match (optionnel) -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Match associé (optionnel)</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionner un match</label>
                    <select name="match_id" id="matchSelect"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Aucun match --</option>
                        @foreach($matches as $match)
                            <option value="{{ $match->id }}"
                                data-team-a="{{ $match->team_a }}"
                                data-team-b="{{ $match->team_b }}"
                                data-date="{{ $match->match_date->format('d/m/Y à H:i') }}">
                                {{ $match->team_a }} vs {{ $match->team_b }} - {{ $match->match_date->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Variables disponibles si match sélectionné : {match_equipe_a}, {match_equipe_b}, {match_date}</p>
                </div>
            </div>

            <!-- Audience -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Audience cible</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type d'audience *</label>
                    <select name="audience_type" id="audienceType" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">Tous les utilisateurs</option>
                        <option value="village">Par village</option>
                        <option value="status">Par statut</option>
                    </select>
                </div>

                <div id="villageSection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Village</label>
                    <select name="village_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Choisir un village --</option>
                        @foreach($villages as $village)
                            <option value="{{ $village->id }}">{{ $village->name }} ({{ $village->users_count }} utilisateurs)</option>
                        @endforeach
                    </select>
                </div>

                <div id="statusSection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="audience_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Choisir --</option>
                        <option value="REGISTERED">Inscrits</option>
                        <option value="OPT_IN">Opt-in confirmés</option>
                        <option value="ACTIVE">Actifs (avec pronostics)</option>
                    </select>
                </div>

                <div class="mt-3 p-3 bg-blue-50 rounded">
                    <p class="text-sm text-blue-800">
                        <strong>Destinataires estimés :</strong>
                        <span id="recipientCount">Calcul en cours...</span>
                    </p>
                </div>
            </div>

            <!-- Message -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Contenu du message</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                    <textarea name="message" id="messageBody" rows="8" required maxlength="1600"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: Bonjour {nom},&#10;&#10;Nouveau match aujourd'hui !&#10;{match_equipe_a} vs {match_equipe_b}&#10;Le {match_date}&#10;&#10;Envoie ton pronostic maintenant !">{{ old('message') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Variables disponibles : {nom}, {prenom}, {village}, {phone}, {match_equipe_a}, {match_equipe_b}, {match_date}</p>
                    <p class="text-xs text-gray-400 mt-1">Caractères : <span id="charCount">0</span>/1600</p>
                </div>

                <div id="variablesHelp" class="mt-3 p-3 bg-yellow-50 rounded" style="display: none;">
                    <p class="text-sm text-yellow-800">
                        <strong>Variables détectées :</strong>
                        <span id="detectedVars"></span>
                    </p>
                </div>
            </div>

            <!-- Programmation -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Programmation</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date et heure d'envoi</label>
                    <input type="datetime-local" name="scheduled_at"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        min="{{ now()->format('Y-m-d\TH:i') }}">
                    <p class="text-xs text-gray-500 mt-1">Laisser vide pour un envoi immédiat (statut brouillon)</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.campaigns.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Créer la Campagne
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const audienceType = document.getElementById('audienceType');
    const villageSection = document.getElementById('villageSection');
    const statusSection = document.getElementById('statusSection');
    const messageBody = document.getElementById('messageBody');
    const charCount = document.getElementById('charCount');
    const templateSelect = document.getElementById('templateSelect');
    const variablesHelp = document.getElementById('variablesHelp');
    const detectedVars = document.getElementById('detectedVars');

    // Gérer l'affichage de l'audience
    audienceType.addEventListener('change', function() {
        villageSection.style.display = this.value === 'village' ? 'block' : 'none';
        statusSection.style.display = this.value === 'status' ? 'block' : 'none';
        updateRecipientCount();
    });

    // Charger un template
    templateSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            const body = option.dataset.body;
            const variables = JSON.parse(option.dataset.variables || '[]');

            messageBody.value = body;
            updateCharCount();

            if (variables.length > 0) {
                variablesHelp.style.display = 'block';
                detectedVars.textContent = variables.map(v => `{${v}}`).join(', ');
            }
        }
    });

    // Compteur de caractères
    messageBody.addEventListener('input', updateCharCount);

    function updateCharCount() {
        charCount.textContent = messageBody.value.length;
    }

    function updateRecipientCount() {
        // Simulation - dans une vraie app, faire un appel AJAX
        document.getElementById('recipientCount').textContent = 'Tous les utilisateurs actifs';
    }

    updateCharCount();
});
</script>
@endsection
