<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LckCartSession;
use App\Models\LckCategory;
use App\Models\LckOrder;
use App\Models\LckOrderItem;
use App\Models\LckProduct;
use App\Services\LckNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LckController extends Controller
{
    public function __construct(protected LckNotificationService $notifications) {}
    // ─────────────────────────────────────────────────────────────
    // Phase 2B — POST /api/lck/cart/create
    // Appelé par le site WordPress quand le client clique "Commander"
    // ─────────────────────────────────────────────────────────────
    public function createCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer',
            'items.*.quantity'     => 'required|integer|min:1|max:100',
            'items.*.name'         => 'nullable|string|max:200',
            'items.*.price'        => 'nullable|numeric|min:0',
            'items.*.category'     => 'nullable|string|max:100',
            'customer_phone'       => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $cartItems = [];
            $total     = 0;

            foreach ($request->items as $item) {
                $wooId   = (int) $item['product_id'];
                $product = $this->syncProductFromWooCommerce($wooId, $item);

                if (!$product || !$product->is_available || !$product->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le produit \"{$product?->name}\" n'est plus disponible.",
                    ], 422);
                }

                $qty      = (int) $item['quantity'];
                // Prix prioritaire : celui de WooCommerce si fourni, sinon celui en base
                $price    = isset($item['price']) ? (float) $item['price'] : (float) $product->price;
                $subtotal = $price * $qty;
                $total   += $subtotal;

                $cartItems[] = [
                    'product_id' => $product->id,
                    'name'       => $product->whatsapp_label,
                    'category'   => $product->category?->name,
                    'unit_price' => $price,
                    'quantity'   => $qty,
                    'subtotal'   => (float) $subtotal,
                ];
            }

            $token = LckCartSession::generateToken();

            $cart = LckCartSession::create([
                'token'          => $token,
                'customer_phone' => $request->customer_phone,
                'items'          => $cartItems,
                'total'          => $total,
                'status'         => 'pending',
                'source'         => 'website',
                'expires_at'     => now()->addHours(24),
            ]);

            $whatsappNumber = ltrim(config('services.twilio.whatsapp_from', '+243841622222'), 'whatsapp:+');
            $message        = urlencode("CMD-{$token}");
            $whatsappLink   = "https://wa.me/{$whatsappNumber}?text={$message}";

            Log::info('LCK Cart created', ['token' => $token, 'total' => $total, 'items' => count($cartItems)]);

            return response()->json([
                'success'        => true,
                'token'          => $token,
                'whatsapp_link'  => $whatsappLink,
                'total'          => number_format($total, 2),
                'items_count'    => count($cartItems),
                'expires_at'     => $cart->expires_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('LCK createCart error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'input'   => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur.',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Phase 2C — GET /api/lck/cart/{token}
    // Appelé par Twilio Studio pour afficher le récapitulatif panier
    // ─────────────────────────────────────────────────────────────
    public function getCart(string $token): JsonResponse
    {
        $cart = LckCartSession::where('token', $token)->first();

        if (!$cart) {
            return response()->json([
                'success'      => false,
                'valid'        => false,
                'message'      => 'Panier introuvable.',
                'summary_text' => "❌ Ce lien de commande est invalide. Veuillez recommencer depuis le site.",
            ], 404);
        }

        if ($cart->isExpired()) {
            $cart->update(['status' => 'expired']);
            return response()->json([
                'success'      => false,
                'valid'        => false,
                'message'      => 'Panier expiré.',
                'summary_text' => "⏰ Votre panier a expiré (validité 24h). Veuillez recommencer votre sélection sur le site.",
            ], 410);
        }

        if ($cart->status !== 'pending') {
            return response()->json([
                'success'      => false,
                'valid'        => false,
                'message'      => "Ce panier a déjà été {$cart->status}.",
                'summary_text' => "ℹ️ Ce panier a déjà été traité. Rendez-vous sur le site pour passer une nouvelle commande.",
            ], 409);
        }

        return response()->json([
            'success'      => true,
            'valid'        => true,
            'token'        => $cart->token,
            'items'        => $cart->items,
            'total'        => number_format($cart->total, 2),
            'items_count'  => count($cart->items),
            'summary_text' => $cart->summary_text,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Phase 2D — POST /api/lck/order/confirm
    // Appelé par Twilio Studio quand le client répond OUI
    // ─────────────────────────────────────────────────────────────
    public function confirmOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token'          => 'required|string',
            'customer_phone' => 'required|string|max:50',
            'customer_name'  => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Twilio envoie "whatsapp:+243XXXXXXXXX" — on garde uniquement le numéro
        $phone = preg_replace('/^whatsapp:/i', '', $request->customer_phone);

        $cart = LckCartSession::where('token', $request->token)->first();

        if (!$cart || !$cart->isUsable()) {
            return response()->json([
                'success' => false,
                'message' => 'Panier invalide ou expiré.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $cart->update([
                'customer_phone' => $phone,
                'customer_name'  => $request->customer_name,
                'status'         => 'confirmed',
            ]);

            // Générer la référence de commande
            $orderRef = LckOrder::generateRef();

            // Créer la commande
            $order = LckOrder::create([
                'order_ref'       => $orderRef,
                'cart_session_id' => $cart->id,
                'customer_phone'  => $request->customer_phone,
                'customer_name'   => $request->customer_name,
                'total'           => $cart->total,
                'status'          => LckOrder::STATUS_RECEIVED,
            ]);

            // Créer les lignes de commande
            foreach ($cart->items as $item) {
                LckOrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $item['product_id'],
                    'product_name'     => $item['name'],
                    'product_category' => $item['category'] ?? null,
                    'unit_price'       => $item['unit_price'],
                    'quantity'         => $item['quantity'],
                    'subtotal'         => $item['subtotal'],
                ]);
            }

            DB::commit();

            Log::info('LCK Order confirmed', [
                'order_ref' => $orderRef,
                'phone'     => $request->customer_phone,
                'total'     => $cart->total,
            ]);

            // Notifier les commercantes
            $this->notifications->notifyCommercanteNewOrder($order);

            $confirmText = "✅ *Commande confirmée!*\n\n"
                . "Référence: *{$orderRef}*\n"
                . "Total: *" . number_format($cart->total, 2) . " \$*\n\n"
                . "Notre équipe prépare votre commande. Vous recevrez un message dès qu'elle sera prête. 🍷";

            return response()->json([
                'success'        => true,
                'order_ref'      => $orderRef,
                'status'         => LckOrder::STATUS_RECEIVED,
                'confirm_text'   => $confirmText,
                'total'          => number_format($cart->total, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('LCK confirmOrder error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création de la commande.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Phase 2D — POST /api/lck/order/cancel
    // Appelé par Twilio Studio quand le client répond NON
    // ─────────────────────────────────────────────────────────────
    public function cancelOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token'          => 'required|string',
            'customer_phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $cart = LckCartSession::where('token', $request->token)->first();

        if ($cart && $cart->status === 'pending') {
            $cart->update(['status' => 'cancelled']);
        }

        Log::info('LCK Order cancelled by client', ['token' => $request->token]);

        return response()->json([
            'success'      => true,
            'cancel_text'  => "❌ Commande annulée.\n\nN'hésitez pas à revenir sur notre site pour passer une nouvelle commande. À bientôt! 🍷\n\nlacledeschateaux.ywcdigital.com",
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Phase 2E — PUT /api/lck/order/{ref}/status
    // Appelé depuis le dashboard commercante pour changer le statut
    // ─────────────────────────────────────────────────────────────
    public function updateOrderStatus(Request $request, string $ref): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,preparing,ready,delivered,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $order = LckOrder::where('order_ref', $ref)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Commande introuvable.'], 404);
        }

        $newStatus = $request->status;
        $updates   = ['status' => $newStatus];

        if ($request->notes) {
            $updates['notes'] = $request->notes;
        }

        // Horodater les transitions clés
        match ($newStatus) {
            'confirmed' => $updates['confirmed_at'] = now(),
            'ready'     => $updates['ready_at']     = now(),
            'delivered' => $updates['delivered_at'] = now(),
            default     => null,
        };

        $order->update($updates);

        Log::info('LCK Order status updated', ['order_ref' => $ref, 'status' => $newStatus]);

        // Notifier le client selon le nouveau statut
        if ($newStatus === 'ready') {
            $this->notifications->notifyCustomerOrderReady($order);
        } elseif ($newStatus === 'cancelled') {
            $this->notifications->notifyCustomerOrderCancelled($order, $request->notes ?? '');
        }

        return response()->json([
            'success'      => true,
            'order_ref'    => $ref,
            'status'       => $newStatus,
            'status_label' => LckOrder::STATUS_LABELS[$newStatus],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Phase 2F — GET /api/lck/products
    // Appelé par Twilio Studio pour afficher le catalogue dans le bot
    // ─────────────────────────────────────────────────────────────
    public function getProducts(Request $request): JsonResponse
    {
        $categorySlug = $request->query('category');

        $query = LckProduct::with('category')->available();

        if ($categorySlug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        $products = $query->orderBy('sort_order')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'success'    => false,
                'message'    => 'Aucun produit disponible.',
                'bot_text'   => "😔 Aucun produit disponible dans cette catégorie pour le moment.",
                'products'   => [],
            ]);
        }

        // Format pour le bot: liste numérotée
        $lines = [];
        foreach ($products as $index => $product) {
            $num     = $index + 1;
            $lines[] = "{$num}. {$product->bot_label}";
        }

        return response()->json([
            'success'    => true,
            'products'   => $products->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->whatsapp_label,
                'category'    => $p->category?->name,
                'price'       => (float) $p->price,
                'price_fmt'   => $p->formatted_price,
                'bot_label'   => $p->bot_label,
            ]),
            'bot_text'   => implode("\n", $lines),
            'count'      => $products->count(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/lck/categories
    // Liste des catégories pour la navigation dans le bot
    // ─────────────────────────────────────────────────────────────
    public function getCategories(): JsonResponse
    {
        $categories = LckCategory::active()->withCount(['availableProducts'])->get();

        $lines = [];
        foreach ($categories as $index => $cat) {
            if ($cat->available_products_count > 0) {
                $num     = $index + 1;
                $lines[] = "{$num}. {$cat->display_name}";
            }
        }

        return response()->json([
            'success'    => true,
            'categories' => $categories->map(fn($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'slug'           => $c->slug,
                'display_name'   => $c->display_name,
                'products_count' => $c->available_products_count,
            ]),
            'bot_text'   => implode("\n", $lines),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Upsert automatique d'un produit WooCommerce dans lck_products
    // Crée le produit s'il n'existe pas, met à jour nom/prix sinon.
    // Ne touche jamais à is_available / is_active (géré par l'équipe).
    // ─────────────────────────────────────────────────────────────
    private function syncProductFromWooCommerce(int $wooId, array $item): ?LckProduct
    {
        // Sans données WooCommerce : cherche uniquement en base
        if (empty($item['name']) || !isset($item['price'])) {
            return LckProduct::where('wordpress_product_id', $wooId)->first()
                ?? LckProduct::find($wooId);
        }

        // Résolution de la catégorie — toujours garantir un category_id valide
        // car la colonne est NOT NULL en base.
        $catName = !empty($item['category']) ? trim($item['category']) : 'Général';
        $catSlug = \Illuminate\Support\Str::slug($catName) ?: 'general';

        $category = LckCategory::firstOrCreate(
            ['slug' => $catSlug],
            ['name' => $catName, 'is_active' => true, 'sort_order' => 99]
        );

        $name = trim($item['name']);
        $price = (float) $item['price'];

        // Cherche le produit existant par woo_id
        $product = LckProduct::where('wordpress_product_id', $wooId)->first();

        if ($product) {
            // Met à jour nom, prix, catégorie — sans toucher is_available/is_active
            $product->update([
                'name'           => $name,
                'whatsapp_label' => $name,
                'price'          => $price,
                'category_id'    => $category->id,
            ]);
        } else {
            // Slug unique : nom + woo_id pour éviter toute collision
            $slug = \Illuminate\Support\Str::slug($name . '-woo-' . $wooId);

            $product = LckProduct::create([
                'wordpress_product_id' => $wooId,
                'name'                 => $name,
                'slug'                 => $slug,
                'whatsapp_label'       => $name,
                'price'                => $price,
                'category_id'          => $category->id,
                'is_available'         => true,
                'is_active'            => true,
                'sort_order'           => 0,
            ]);

            Log::info('LCK Product auto-created from WooCommerce', [
                'woo_id' => $wooId,
                'name'   => $name,
                'price'  => $price,
            ]);
        }

        return $product;
    }
}
