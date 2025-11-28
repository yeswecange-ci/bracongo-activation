<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,media,button,list,interactive',
            'category' => 'required|string|max:100',
            'header_type' => 'nullable|in:text,image,video,document',
            'header_text' => 'nullable|string|max:60',
            'header_media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,pdf|max:5120',
            'body' => 'required|string|max:1024',
            'footer' => 'nullable|string|max:60',
            'buttons' => 'nullable|json',
            'variables' => 'nullable|array',
        ]);

        // Gestion du media header
        if ($request->hasFile('header_media')) {
            $path = $request->file('header_media')->store('templates/media', 'public');
            $validated['header_media_path'] = $path;
        }

        // Parser les variables depuis le body
        preg_match_all('/\{([a-z_]+)\}/', $validated['body'], $matches);
        $validated['variables'] = array_unique($matches[1]);

        $validated['is_active'] = $request->boolean('is_active', true);

        MessageTemplate::create($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template créé avec succès !');
    }

    public function show(MessageTemplate $template)
    {
        return view('admin.templates.show', compact('template'));
    }

    public function edit(MessageTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, MessageTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,media,button,list,interactive',
            'category' => 'required|string|max:100',
            'header_type' => 'nullable|in:text,image,video,document',
            'header_text' => 'nullable|string|max:60',
            'header_media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,pdf|max:5120',
            'body' => 'required|string|max:1024',
            'footer' => 'nullable|string|max:60',
            'buttons' => 'nullable|json',
            'variables' => 'nullable|array',
        ]);

        // Gestion du media header
        if ($request->hasFile('header_media')) {
            // Supprimer l'ancien media si existant
            if ($template->header_media_path) {
                Storage::disk('public')->delete($template->header_media_path);
            }
            $path = $request->file('header_media')->store('templates/media', 'public');
            $validated['header_media_path'] = $path;
        }

        // Parser les variables depuis le body
        preg_match_all('/\{([a-z_]+)\}/', $validated['body'], $matches);
        $validated['variables'] = array_unique($matches[1]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $template->update($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template mis à jour avec succès !');
    }

    public function destroy(MessageTemplate $template)
    {
        // Vérifier si le template est utilisé dans des campagnes
        if ($template->campaignMessages()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer un template utilisé dans des campagnes.');
        }

        // Supprimer le media si existant
        if ($template->header_media_path) {
            Storage::disk('public')->delete($template->header_media_path);
        }

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template supprimé avec succès !');
    }

    /**
     * Dupliquer un template
     */
    public function duplicate(MessageTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copie)';
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()->route('admin.templates.edit', $newTemplate)
            ->with('success', 'Template dupliqué avec succès !');
    }

    /**
     * Prévisualiser un template
     */
    public function preview(Request $request, MessageTemplate $template)
    {
        $sampleData = [
            'nom' => 'Jean Dupont',
            'prenom' => 'Jean',
            'village' => 'Gombe',
            'phone' => '+243812345678',
            'match' => 'RDC vs Maroc',
            'date' => '15 Janvier 2025',
            'heure' => '20h00',
        ];

        $renderedBody = $template->render($sampleData);

        return response()->json([
            'success' => true,
            'preview' => [
                'header_type' => $template->header_type,
                'header_text' => $template->header_text,
                'header_media_url' => $template->header_media_path
                    ? Storage::disk('public')->url($template->header_media_path)
                    : null,
                'body' => $renderedBody,
                'footer' => $template->footer,
                'buttons' => json_decode($template->buttons, true),
            ]
        ]);
    }
}
