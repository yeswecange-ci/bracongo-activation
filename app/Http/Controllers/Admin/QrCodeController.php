<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    public function index()
    {
        $qrCodes = QrCode::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.qrcodes.index', compact('qrCodes'));
    }

    public function create()
    {
        return view('admin.qrcodes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
        ]);

        // Générer un code unique (avec vérification d'unicité)
        do {
            $code = strtoupper(Str::random(10));
        } while (QrCode::where('code', $code)->exists());

        // URL de scan qui redirigera vers WhatsApp (permet de tracker les scans)
        $scanUrl = url("/qr/{$code}");

        // Générer le QR Code avec la nouvelle API Endroid v6.0
        try {
            $builder = new Builder(
                writer: new PngWriter(),
                writerOptions: [],
                validateResult: false,
                data: $scanUrl,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 500,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            );

            $result = $builder->build();

            // Sauvegarder l'image
            $filename = 'qr-' . $code . '.png';
            $path = 'qrcodes/' . $filename;

            Storage::disk('public')->put($path, $result->getString());

            $validated['code']          = $code;
            $validated['qr_image_path'] = $path;
        } catch (\Exception $e) {
            Log::error('QR Code generation error: ' . $e->getMessage(), [
                'code' => $code,
                'source' => $validated['source']
            ]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la génération du QR Code : ' . $e->getMessage()]);
        }

        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['scan_count'] = 0;

        QrCode::create($validated);

        return redirect()->route('admin.qrcodes.index')
            ->with('success', 'QR Code créé avec succès !');
    }

    public function show(QrCode $qrcode)
    {
        return view('admin.qrcodes.show', compact('qrcode'));
    }

    public function edit(QrCode $qrcode)
    {
        return view('admin.qrcodes.edit', compact('qrcode'));
    }

    public function update(Request $request, QrCode $qrcode)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $qrcode->update($validated);

        return redirect()->route('admin.qrcodes.index')
            ->with('success', 'QR Code mis à jour avec succès !');
    }

    public function destroy(QrCode $qrcode)
    {
        // Supprimer l'image
        if ($qrcode->qr_image_path) {
            Storage::disk('public')->delete($qrcode->qr_image_path);
        }

        $qrcode->delete();

        return redirect()->route('admin.qrcodes.index')
            ->with('success', 'QR Code supprimé avec succès !');
    }

    public function download(QrCode $qrcode)
    {
        if (! $qrcode->qr_image_path || ! Storage::disk('public')->exists($qrcode->qr_image_path)) {
            return redirect()->back()->with('error', 'Image QR Code introuvable');
        }

        return Storage::disk('public')->download($qrcode->qr_image_path, 'qrcode-' . $qrcode->source . '.png');
    }

    /**
     * Endpoint public pour scanner un QR code (incrémente le compteur)
     * Route: GET /qr/{code}
     */
    public function scan($code)
    {
        $qrCode = QrCode::where('code', strtoupper($code))->first();

        if (! $qrCode) {
            abort(404, 'QR Code invalide');
        }

        // Incrémenter le compteur uniquement si le QR code est actif
        if ($qrCode->is_active) {
            $qrCode->incrementScan();
        }

        // Récupérer le numéro WhatsApp
        $whatsappNumber = config('services.whatsapp.number', env('WHATSAPP_NUMBER', 'YOUR_WHATSAPP_NUMBER'));
        $message = urlencode("Je veux m'inscrire à CAN2025 avec le code: {$code}");

        // Rediriger vers WhatsApp
        return redirect("https://wa.me/{$whatsappNumber}?text={$message}");
    }
}
