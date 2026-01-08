@extends('admin.layouts.app')

@section('title', 'Détails de la Question')
@section('page-title', 'Détails de la Question')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">Question #{{ $question->id }}</h3>
            <p class="text-sm text-gray-500 mt-1">Créée le {{ $question->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.quiz.questions.edit', $question) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            <a href="{{ route('admin.quiz.questions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Retour à la liste
            </a>
        </div>
    </div>

    <!-- Question complète -->
    <div class="bg-white rounded-lg shadow p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Question</h4>
        <p class="text-gray-700 mb-6">{{ $question->question }}</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center p-3 rounded-lg border {{ $question->correct_answer == 'A' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $question->correct_answer == 'A' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600' }} font-bold mr-3">A</span>
                <span class="text-gray-700">{{ $question->option_a }}</span>
                @if($question->correct_answer == 'A')
                    <svg class="w-5 h-5 text-green-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </div>
            <div class="flex items-center p-3 rounded-lg border {{ $question->correct_answer == 'B' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $question->correct_answer == 'B' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600' }} font-bold mr-3">B</span>
                <span class="text-gray-700">{{ $question->option_b }}</span>
                @if($question->correct_answer == 'B')
                    <svg class="w-5 h-5 text-green-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </div>
            <div class="flex items-center p-3 rounded-lg border {{ $question->correct_answer == 'C' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $question->correct_answer == 'C' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600' }} font-bold mr-3">C</span>
                <span class="text-gray-700">{{ $question->option_c }}</span>
                @if($question->correct_answer == 'C')
                    <svg class="w-5 h-5 text-green-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </div>
            @if($question->option_d)
                <div class="flex items-center p-3 rounded-lg border {{ $question->correct_answer == 'D' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $question->correct_answer == 'D' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600' }} font-bold mr-3">D</span>
                    <span class="text-gray-700">{{ $question->option_d }}</span>
                    @if($question->correct_answer == 'D')
                        <svg class="w-5 h-5 text-green-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-6 flex items-center space-x-6">
            <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-2">Points:</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                    {{ $question->points }} pts
                </span>
            </div>
            <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-2">Ordre:</span>
                <span class="text-sm font-medium text-gray-900">{{ $question->order }}</span>
            </div>
            <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-2">Statut:</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $question->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $question->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Réponses</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_answers'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Correctes</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['correct_answers'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Incorrectes</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['wrong_answers'] }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Taux de réussite</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['accuracy_rate'] }}%</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution des réponses -->
    @if($distribution->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Distribution des Réponses</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach(['A', 'B', 'C', 'D'] as $option)
                    @php
                        $dist = $distribution->firstWhere('answer', $option);
                        $count = $dist->count ?? 0;
                        $percentage = $stats['total_answers'] > 0 ? round(($count / $stats['total_answers']) * 100, 1) : 0;
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-lg font-bold text-gray-900">Option {{ $option }}</span>
                            <span class="text-sm text-gray-500">{{ $count }} réponses</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600">{{ $percentage }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Réponses récentes -->
    @if($recentAnswers->isNotEmpty())
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">Réponses Récentes</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joueur</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Réponse</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Résultat</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentAnswers as $answer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $answer->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $answer->user->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-800 font-bold">
                                        {{ $answer->answer }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($answer->is_correct)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Correct
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Incorrect
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                                    {{ $answer->points_won }} pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                    {{ $answer->answered_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
