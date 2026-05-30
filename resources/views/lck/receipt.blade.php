<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de commande {{ $order->order_ref }} — La Clé des Châteaux</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background: #F8F6F2; color: #1A1A1A; -webkit-font-smoothing: antialiased; }
        .page { max-width: 480px; margin: 0 auto; padding: 24px 16px 48px; }

        /* Header */
        .header { text-align: center; padding-bottom: 24px; border-bottom: 2px solid #1A1A1A; margin-bottom: 24px; }
        .header img { height: 48px; object-fit: contain; margin-bottom: 8px; }
        .header-sub { font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #9CA3AF; }

        /* Status banner */
        .status-banner { padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; }
        .status-ready { background: #ECFDF5; border: 1px solid #6EE7B7; }
        .status-default { background: #F3F4F6; border: 1px solid #E5E7EB; }
        .status-label { font-size: 14px; font-weight: 700; }
        .status-ref { font-family: monospace; font-size: 20px; font-weight: 900; letter-spacing: -0.5px; }

        /* Sections */
        .section { background: #fff; border-radius: 14px; border: 1px solid rgba(0,0,0,0.06); padding: 18px; margin-bottom: 12px; }
        .section-title { font-size: 10px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: #9CA3AF; margin-bottom: 12px; }
        .info-row { display: flex; justify-content: space-between; align-items: baseline; padding: 6px 0; border-bottom: 1px solid #F3F4F6; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 12px; color: #9CA3AF; font-weight: 500; }
        .info-value { font-size: 13px; font-weight: 600; text-align: right; max-width: 60%; }

        /* Items */
        .item-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid #F3F4F6; }
        .item-row:last-child { border-bottom: none; }
        .item-name { font-size: 13px; font-weight: 600; }
        .item-sub { font-size: 11px; color: #9CA3AF; margin-top: 2px; }
        .item-price { font-size: 14px; font-weight: 700; flex-shrink: 0; }

        /* Total */
        .total-row { display: flex; justify-content: space-between; align-items: center; padding: 16px 18px; background: #1A1A1A; border-radius: 14px; margin-bottom: 12px; }
        .total-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6B6B6B; }
        .total-amount { font-size: 26px; font-weight: 900; color: #fff; }

        /* Payment badge */
        .payment-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 100px; font-size: 12px; font-weight: 700; }
        .cash { background: #ECFDF5; color: #065F46; }
        .online-paid { background: #EFF6FF; color: #1D4ED8; }
        .online-pending { background: #FEF3C7; color: #92400E; }

        /* Pickup box */
        .pickup-box { background: #fff; border: 2px solid #C9A84C; border-radius: 14px; padding: 18px; margin-bottom: 12px; }
        .pickup-title { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #C9A84C; margin-bottom: 12px; }
        .pickup-name { font-size: 15px; font-weight: 800; margin-bottom: 4px; }
        .pickup-address { font-size: 13px; color: #6B6B6B; line-height: 1.6; }
        .pickup-deadline { margin-top: 14px; padding: 10px 14px; background: #FEF3C7; border-radius: 10px; font-size: 12px; font-weight: 600; color: #92400E; }

        /* Footer */
        .footer { text-align: center; padding-top: 24px; font-size: 11px; color: #D1D5DB; }

        @media print {
            body { background: #fff; }
            .no-print { display: none; }
            .page { padding: 16px; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <img src="{{ asset('images/lck-logo.jpeg') }}" alt="La Clé des Châteaux">
        <p class="header-sub">Bon de commande</p>
    </div>

    {{-- Statut + ref --}}
    <div class="status-banner {{ $order->status === 'ready' ? 'status-ready' : 'status-default' }}">
        <p class="status-label">
            @if($order->status === 'ready') ✅ Commande prête à retirer
            @elseif($order->status === 'delivered') ✅ Commande livrée
            @elseif($order->status === 'cancelled') ❌ Commande annulée
            @else 🔄 {{ $order->status_label }}
            @endif
        </p>
        <p class="status-ref">{{ $order->order_ref }}</p>
        <p style="font-size: 12px; color: #6B6B6B; margin-top: 4px">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
    </div>

    {{-- Client --}}
    <div class="section">
        <p class="section-title">Informations client</p>
        <div class="info-row">
            <span class="info-label">Nom</span>
            <span class="info-value">{{ $order->customer_name ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Téléphone</span>
            <span class="info-value">{{ $order->customer_phone }}</span>
        </div>
        @if($order->customer_location)
        <div class="info-row">
            <span class="info-label">Zone</span>
            <span class="info-value">{{ $order->customer_location }}</span>
        </div>
        @endif
    </div>

    {{-- Articles --}}
    <div class="section">
        <p class="section-title">Articles commandés</p>
        @foreach($order->items as $item)
        <div class="item-row">
            <div style="flex: 1; min-width: 0; padding-right: 12px">
                <p class="item-name">{{ $item->product_name }}</p>
                @if($item->product_category)
                <p class="item-sub">{{ $item->product_category }}</p>
                @endif
                <p class="item-sub">{{ number_format($item->unit_price, 2) }} $ × {{ $item->quantity }}</p>
            </div>
            <p class="item-price">{{ number_format($item->subtotal, 2) }} $</p>
        </div>
        @endforeach
    </div>

    {{-- Total --}}
    <div class="total-row">
        <span class="total-label" style="color: #fff">Total</span>
        <span class="total-amount">{{ number_format($order->total, 2) }} $</span>
    </div>

    {{-- Paiement --}}
    <div style="text-align: center; margin-bottom: 16px">
        @if($order->payment_method === 'cash_on_delivery')
        <span class="payment-badge cash">💵 Paiement à la livraison</span>
        @elseif($order->isPaid())
        <span class="payment-badge online-paid">✅ Paiement reçu — {{ number_format($order->amount_paid, 2) }} $</span>
        @else
        <span class="payment-badge online-pending">⏳ Paiement Mobile Money en attente</span>
        @endif
    </div>

    {{-- Point de retrait --}}
    @if($order->status === 'ready')
    <div class="pickup-box">
        <p class="pickup-title">📍 Point de retrait</p>
        <p class="pickup-name">{{ $settings['pickup_name'] ?? 'La Clé des Châteaux' }}</p>
        <p class="pickup-address">
            {{ str_replace("\n", "\n", $settings['pickup_address'] ?? 'Boulevard du 30 Juin') }}<br>
            {{ $settings['pickup_city'] ?? 'Kinshasa, RDC' }}
        </p>
        @if(!empty($settings['pickup_phone']))
        <p class="pickup-address" style="margin-top: 8px">📞 {{ $settings['pickup_phone'] }}</p>
        @endif
        <p class="pickup-address" style="margin-top: 4px">🕐 {{ $settings['pickup_hours'] ?? 'Lun–Sam 9h–19h' }}</p>
        <div class="pickup-deadline">
            ⚠️ Vous avez <strong>{{ $settings['pickup_deadline'] ?? 5 }} jours ouvrés</strong> pour récupérer votre colis.
            Passé ce délai, la commande pourra être remise en stock.
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($order->notes)
    <div class="section">
        <p class="section-title">Notes</p>
        <p style="font-size: 13px; color: #6B6B6B; line-height: 1.6">{{ $order->notes }}</p>
    </div>
    @endif

    {{-- Bouton impression --}}
    <div class="no-print" style="text-align: center; margin-top: 24px">
        <button onclick="window.print()"
            style="background: #1A1A1A; color: #fff; border: none; padding: 14px 32px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit">
            🖨️ Imprimer
        </button>
    </div>

    <div class="footer">
        La Clé des Châteaux — Kinshasa, RDC<br>
        Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

</div>
</body>
</html>
