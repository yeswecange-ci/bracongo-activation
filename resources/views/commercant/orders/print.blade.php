<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de commande {{ $order->order_ref }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 13px; color: #111; background: #fff; }
        .page { max-width: 380px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 16px; }
        .logo { font-size: 20px; font-weight: 900; letter-spacing: -0.5px; }
        .subtitle { font-size: 11px; color: #555; margin-top: 2px; }
        .ref { font-family: monospace; font-size: 18px; font-weight: 900; text-align: center;
               background: #f5f5f5; padding: 8px; border-radius: 4px; margin: 12px 0; }
        .section { margin-bottom: 14px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase;
                         letter-spacing: 0.5px; color: #666; border-bottom: 1px solid #eee;
                         padding-bottom: 4px; margin-bottom: 8px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .info-label { color: #666; }
        .info-value { font-weight: 600; text-align: right; max-width: 60%; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { text-align: left; font-size: 10px; text-transform: uppercase;
                           color: #666; padding: 4px 0; border-bottom: 1px solid #ddd; }
        .items-table td { padding: 6px 0; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        .items-table td:last-child { text-align: right; font-weight: 700; }
        .total-row { display: flex; justify-content: space-between; align-items: center;
                     margin-top: 12px; padding-top: 10px; border-top: 2px solid #111; }
        .total-label { font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .total-amount { font-size: 22px; font-weight: 900; }
        .payment-badge { display: inline-block; padding: 3px 10px; border-radius: 20px;
                         font-size: 11px; font-weight: 700; margin-top: 8px; }
        .cash { background: #e8f5e9; color: #2e7d32; }
        .online { background: #e3f2fd; color: #1565c0; }
        .footer { text-align: center; margin-top: 20px; padding-top: 12px;
                  border-top: 1px dashed #ccc; font-size: 11px; color: #888; }
        .qr-placeholder { text-align: center; margin: 12px 0;
                           font-size: 11px; color: #aaa; font-style: italic; }
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="logo">🍷 La Clé des Châteaux</div>
        <div class="subtitle">Bon de commande</div>
    </div>

    <div class="ref">{{ $order->order_ref }}</div>

    <div class="section">
        <div class="section-title">Client</div>
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

    <div class="section">
        <div class="section-title">Commande</div>
        <div class="info-row">
            <span class="info-label">Date</span>
            <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut</span>
            <span class="info-value">{{ $order->status_label }}</span>
        </div>
        @if($order->commercant)
        <div class="info-row">
            <span class="info-label">Traité par</span>
            <span class="info-value">{{ $order->commercant->name }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Articles</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th style="text-align:center">Qté</th>
                    <th style="text-align:right">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}
                        @if($item->product_category)
                        <br><small style="color:#999">{{ $item->product_category }}</small>
                        @endif
                    </td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td>{{ number_format($item->subtotal, 2) }} $</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-row">
            <span class="total-label">Total</span>
            <span class="total-amount">{{ number_format($order->total, 2) }} $</span>
        </div>

        <div style="text-align:center; margin-top:8px">
            <span class="payment-badge {{ $order->payment_method === 'cash_on_delivery' ? 'cash' : 'online' }}">
                {{ $order->payment_method_label }} — {{ $order->payment_status_label }}
            </span>
        </div>
    </div>

    @if($order->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <p style="color:#555; font-style:italic">{{ $order->notes }}</p>
    </div>
    @endif

    <div class="footer">
        La Clé des Châteaux — Kinshasa, RDC<br>
        Imprimé le {{ now()->format('d/m/Y à H:i') }}
    </div>

</div>

{{-- Bouton impression --}}
<div class="no-print" style="text-align:center; padding: 16px">
    <button onclick="window.print()"
        style="background:#b45309; color:#fff; border:none; padding:12px 32px; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer">
        🖨️ Imprimer
    </button>
    <a href="{{ route('commercant.orders.show', $order->order_ref) }}"
       style="display:inline-block; margin-left:12px; color:#666; text-decoration:none; font-size:13px">
        ← Retour
    </a>
</div>

</body>
</html>
