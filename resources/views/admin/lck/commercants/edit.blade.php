@extends('admin.layouts.app')
@section('title', 'LCK — Modifier compte')
@section('page-title', 'Modifier compte commercante')

@section('content')
<div class="space-y-6 max-w-xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.lck.commercants.index') }}" class="text-yellow-600 hover:text-yellow-800 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Équipe
        </a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600 font-medium">{{ $commercant->name }}</span>
    </div>

    @if($errors->any())
    <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-900 px-6 py-4 rounded-lg shadow-sm">
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.lck.commercants.update', $commercant) }}"
          class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="font-bold text-gray-800">Informations du compte</h2>
        </div>

        <div class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom complet *</label>
                <input type="text" name="name" value="{{ old('name', $commercant->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email *</label>
                <input type="email" name="email" value="{{ old('email', $commercant->email) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $commercant->phone) }}" placeholder="+243..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rôle *</label>
                <select name="role" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                    <option value="commercial" {{ old('role', $commercant->role) === 'commercial' ? 'selected' : '' }}>Commercial(e) — gestion des commandes uniquement</option>
                    <option value="caviste" {{ old('role', $commercant->role) === 'caviste' ? 'selected' : '' }}>Caviste — commandes + gestion du catalogue</option>
                </select>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <p class="text-sm font-medium text-gray-700 mb-3">
                    Changer le mot de passe
                    <span class="text-gray-400 font-normal text-xs ml-1">(laisser vide pour ne pas modifier)</span>
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Nouveau mot de passe</label>
                        <input type="password" name="password" minlength="8"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Confirmation</label>
                        <input type="password" name="password_confirmation" minlength="8"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $commercant->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 accent-yellow-600 rounded">
                <span class="text-sm text-gray-700">Compte actif</span>
            </label>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white px-6 py-2.5 rounded-lg hover:from-yellow-700 hover:to-yellow-800 transition-all shadow-sm text-sm font-medium">
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.lck.commercants.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Annuler</a>
        </div>
    </form>
</div>
@endsection
