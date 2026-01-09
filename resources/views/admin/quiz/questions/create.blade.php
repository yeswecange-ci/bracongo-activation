@extends('admin.layouts.app')

@section('title', 'Créer une Question Quiz')
@section('page-title', 'Créer une Question Quiz')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.quiz.questions.store') }}" method="POST">
            @csrf

            <!-- Question -->
            <div class="mb-6">
                <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                    Question <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="question"
                    id="question"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('question') border-red-500 @enderror"
                    placeholder="Posez votre question ici..."
                    required
                >{{ old('question') }}</textarea>
                @error('question')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Option 1 -->
                <div>
                    <label for="option_a" class="block text-sm font-medium text-gray-700 mb-2">
                        Option 1 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="option_a"
                        id="option_a"
                        value="{{ old('option_a') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('option_a') border-red-500 @enderror"
                        placeholder="Première option"
                        required
                    >
                    @error('option_a')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option 2 -->
                <div>
                    <label for="option_b" class="block text-sm font-medium text-gray-700 mb-2">
                        Option 2 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="option_b"
                        id="option_b"
                        value="{{ old('option_b') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('option_b') border-red-500 @enderror"
                        placeholder="Deuxième option"
                        required
                    >
                    @error('option_b')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option 3 -->
                <div>
                    <label for="option_c" class="block text-sm font-medium text-gray-700 mb-2">
                        Option 3 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="option_c"
                        id="option_c"
                        value="{{ old('option_c') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('option_c') border-red-500 @enderror"
                        placeholder="Troisième option"
                        required
                    >
                    @error('option_c')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option 4 (optionnelle) -->
                <div>
                    <label for="option_d" class="block text-sm font-medium text-gray-700 mb-2">
                        Option 4 (optionnelle)
                    </label>
                    <input
                        type="text"
                        name="option_d"
                        id="option_d"
                        value="{{ old('option_d') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('option_d') border-red-500 @enderror"
                        placeholder="Quatrième option (facultatif)"
                    >
                    @error('option_d')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Réponse correcte -->
                <div>
                    <label for="correct_answer" class="block text-sm font-medium text-gray-700 mb-2">
                        Réponse Correcte <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="correct_answer"
                        id="correct_answer"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('correct_answer') border-red-500 @enderror"
                        required
                    >
                        <option value="">Sélectionnez...</option>
                        <option value="A" {{ old('correct_answer') == 'A' ? 'selected' : '' }}>1</option>
                        <option value="B" {{ old('correct_answer') == 'B' ? 'selected' : '' }}>2</option>
                        <option value="C" {{ old('correct_answer') == 'C' ? 'selected' : '' }}>3</option>
                        <option value="D" {{ old('correct_answer') == 'D' ? 'selected' : '' }}>4</option>
                    </select>
                    @error('correct_answer')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Points -->
                <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-2">
                        Points
                    </label>
                    <input
                        type="number"
                        name="points"
                        id="points"
                        value="{{ old('points', 10) }}"
                        min="1"
                        max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('points') border-red-500 @enderror"
                    >
                    @error('points')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ordre -->
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                        Ordre d'affichage
                    </label>
                    <input
                        type="number"
                        name="order"
                        id="order"
                        value="{{ old('order', 0) }}"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 @error('order') border-red-500 @enderror"
                    >
                    @error('order')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Question Active -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                    >
                    <span class="ml-2 text-sm text-gray-700">Question active (visible pour les joueurs)</span>
                </label>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.quiz.questions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Créer la Question
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
