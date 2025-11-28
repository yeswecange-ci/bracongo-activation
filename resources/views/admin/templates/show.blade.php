@extends('admin.layouts.app')

@section('title', 'Voir Template')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <a href="{{ route('admin.templates.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ $template->name }}</h1>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.templates.edit', $template) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-block">
                    Modifier
                </a>
            </div>
        </div>

        <!-- Prévisualisation -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Prévisualisation</h2>

            <div class="bg-gray-100 rounded-lg p-4 max-w-md mx-auto" style="font-family: system-ui, -apple-system, sans-serif;">
                <!-- WhatsApp-like preview -->
                <div class="bg-white rounded-lg shadow p-4">
                    @if($template->header_type === 'text' && $template->header_text)
                        <div class="font-semibold text-lg mb-2">{{ $template->header_text }}</div>
                    @elseif($template->header_type === 'image' && $template->header_media_path)
                        <img src="{{ Storage::disk('public')->url($template->header_media_path) }}" class="w-full rounded mb-2" alt="Header">
                    @endif

                    <div class="text-gray-800 whitespace-pre-wrap">{{ $template->body }}</div>

                    @if($template->footer)
                        <div class="text-xs text-gray-500 mt-3">{{ $template->footer }}</div>
                    @endif

                    @if($template->buttons && count($template->buttons) > 0)
                        <div class="mt-4 space-y-2">
                            @foreach($template->buttons as $button)
                                <button class="w-full py-2 text-blue-600 border border-blue-600 rounded hover:bg-blue-50">
                                    {{ $button['text'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Détails -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails du Template</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Type</span>
                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($template->type) }}</p>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-500">Catégorie</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $template->category ?? '-' }}</p>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-500">Statut</span>
                    <p class="mt-1">
                        @if($template->is_active)
                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full">Actif</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">Inactif</span>
                        @endif
                    </p>
                </div>

                <div>
                    <span class="text-sm font-medium text-gray-500">Variables disponibles</span>
                    <div class="mt-1">
                        @if($template->variables && count($template->variables) > 0)
                            @foreach($template->variables as $var)
                                <span class="inline-block px-2 py-1 text-xs bg-gray-100 rounded mr-1 mb-1">{{"{".$var."}"}}</span>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-900">-</p>
                        @endif
                    </div>
                </div>

                <div class="col-span-2">
                    <span class="text-sm font-medium text-gray-500">Créé le</span>
                    <p class="mt-1 text-sm text-gray-900">{{ $template->created_at->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
