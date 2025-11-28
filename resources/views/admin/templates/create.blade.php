@extends('admin.layouts.app')

@section('title', 'Créer un Template')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.templates.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Créer un Template de Message</h1>
            <p class="text-gray-600 mt-1">Créer un template WhatsApp réutilisable</p>
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

        <form action="{{ route('admin.templates.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
            @csrf

            <!-- Informations de base -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de base</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du template *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: Notification Match du Jour">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie *</label>
                        <select name="category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Choisir --</option>
                            <option value="match_notification">Notification de Match</option>
                            <option value="prize_alert">Alerte Prix</option>
                            <option value="reminder">Rappel</option>
                            <option value="welcome">Bienvenue</option>
                            <option value="info">Information</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de template *</label>
                    <select name="type" id="templateType" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="text">Texte simple</option>
                        <option value="media">Media (image/vidéo) + texte</option>
                        <option value="button">Texte + boutons</option>
                        <option value="interactive">Media + texte + boutons</option>
                    </select>
                </div>
            </div>

            <!-- Header (optionnel) -->
            <div id="headerSection" class="mb-6 pb-6 border-b" style="display: none;">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">En-tête (Header)</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type d'en-tête</label>
                    <select name="header_type" id="headerType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Aucun</option>
                        <option value="text">Texte</option>
                        <option value="image">Image</option>
                        <option value="video">Vidéo</option>
                        <option value="document">Document</option>
                    </select>
                </div>

                <div id="headerTextSection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Texte de l'en-tête (max 60 caractères)</label>
                    <input type="text" name="header_text" maxlength="60"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: Nouveau Match Aujourd'hui">
                </div>

                <div id="headerMediaSection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fichier media</label>
                    <input type="file" name="header_media" accept="image/*,video/*,application/pdf"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB. Formats: JPG, PNG, MP4, PDF</p>
                </div>
            </div>

            <!-- Body (obligatoire) -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Corps du message (Body) *</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message (max 1024 caractères)</label>
                    <textarea name="body" rows="6" required maxlength="1024"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: Bonjour {nom},&#10;&#10;Nouveau match aujourd'hui !&#10;{match} - {date} à {heure}&#10;&#10;Envoie ton pronostic maintenant !">{{ old('body') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Utilise des variables: {nom}, {village}, {match}, {date}, {heure}, {phone}</p>
                </div>
            </div>

            <!-- Footer (optionnel) -->
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pied de page (Footer)</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Texte du footer (max 60 caractères)</label>
                    <input type="text" name="footer" maxlength="60"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: CAN 2025 Kinshasa - Powered by Bracongo">
                </div>
            </div>

            <!-- Boutons (optionnel) -->
            <div id="buttonsSection" class="mb-6 pb-6 border-b" style="display: none;">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Boutons (max 3)</h2>

                <div id="buttonsList" class="space-y-3">
                    <!-- Les boutons seront ajoutés ici par JavaScript -->
                </div>

                <button type="button" id="addButton" class="mt-3 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm">
                    + Ajouter un bouton
                </button>

                <input type="hidden" name="buttons" id="buttonsData">
            </div>

            <!-- Statut -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Template actif</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.templates.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Créer le Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateType = document.getElementById('templateType');
    const headerSection = document.getElementById('headerSection');
    const headerType = document.getElementById('headerType');
    const headerTextSection = document.getElementById('headerTextSection');
    const headerMediaSection = document.getElementById('headerMediaSection');
    const buttonsSection = document.getElementById('buttonsSection');
    const buttonsList = document.getElementById('buttonsList');
    const addButtonBtn = document.getElementById('addButton');
    const buttonsData = document.getElementById('buttonsData');

    let buttons = [];

    // Gérer l'affichage des sections selon le type
    templateType.addEventListener('change', function() {
        const type = this.value;

        if (type === 'media' || type === 'interactive') {
            headerSection.style.display = 'block';
        } else {
            headerSection.style.display = 'none';
        }

        if (type === 'button' || type === 'interactive') {
            buttonsSection.style.display = 'block';
        } else {
            buttonsSection.style.display = 'none';
        }
    });

    // Gérer l'affichage du header selon le type
    headerType.addEventListener('change', function() {
        const type = this.value;

        if (type === 'text') {
            headerTextSection.style.display = 'block';
            headerMediaSection.style.display = 'none';
        } else if (type === 'image' || type === 'video' || type === 'document') {
            headerTextSection.style.display = 'none';
            headerMediaSection.style.display = 'block';
        } else {
            headerTextSection.style.display = 'none';
            headerMediaSection.style.display = 'none';
        }
    });

    // Ajouter un bouton
    addButtonBtn.addEventListener('click', function() {
        if (buttons.length >= 3) {
            alert('Maximum 3 boutons autorisés');
            return;
        }

        const id = Date.now();
        buttons.push({ id, text: '', type: 'reply' });

        renderButtons();
    });

    // Render buttons
    function renderButtons() {
        buttonsList.innerHTML = buttons.map((btn, index) => `
            <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded">
                <input type="text" placeholder="Texte du bouton" value="${btn.text}"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    onchange="updateButton(${index}, 'text', this.value)">
                <select onchange="updateButton(${index}, 'type', this.value)"
                    class="px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                    <option value="reply" ${btn.type === 'reply' ? 'selected' : ''}>Réponse rapide</option>
                    <option value="url" ${btn.type === 'url' ? 'selected' : ''}>URL</option>
                </select>
                <button type="button" onclick="removeButton(${index})" class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    ×
                </button>
            </div>
        `).join('');

        buttonsData.value = JSON.stringify(buttons);
    }

    window.updateButton = function(index, field, value) {
        buttons[index][field] = value;
        buttonsData.value = JSON.stringify(buttons);
    };

    window.removeButton = function(index) {
        buttons.splice(index, 1);
        renderButtons();
    };
});
</script>
@endsection
