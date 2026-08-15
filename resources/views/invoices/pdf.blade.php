<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1a1a1a; margin: 0; padding: 32px; font-size: 13px; }
        .header { border-bottom: 3px solid #1db954; padding-bottom: 16px; margin-bottom: 24px; }
        .logo { display: inline-block; width: 34px; height: 34px; background: #1db954; border-radius: 8px; color: #000; font-weight: bold; font-size: 16px; text-align: center; line-height: 34px; }
        .brand { font-size: 20px; font-weight: bold; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .meta { margin-bottom: 24px; }
        .meta td { padding: 4px 0; }
        .items { margin-top: 8px; }
        .items th { background: #f3f4f6; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #374151; }
        .items td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .items tr:last-child td { border-bottom: none; }
        .total-row td { padding: 12px; font-weight: bold; font-size: 15px; background: #f9fafb; }
        .status-paid { color: #047857; font-weight: bold; }
        .status-pending { color: #b45309; font-weight: bold; }
        .status-failed { color: #b91c1c; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: bold; }
        .badge-paid { background: #d1fae5; color: #047857; }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-failed { background: #fee2e2; color: #b91c1c; }
        .footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 12px; text-align: center; color: #9ca3af; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <span class="logo">&#9835;</span>
                    <span class="brand">&nbsp;Subscription Hub</span>
                </td>
                <td class="right">
                    <div style="font-size: 16px; font-weight: bold;">FACTURA</div>
                    <div class="muted">N&deg; INV-{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta">
        <tr>
            <td style="width:50%">
                <div class="muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.5px;">Facturado a</div>
                <div style="font-weight:bold; font-size:15px; margin-top:4px;">{{ $invoice->subscription->user->name }}</div>
                <div class="muted">{{ $invoice->subscription->user->email }}</div>
            </td>
            <td class="right">
                <table style="float:right; width:auto;">
                    <tr><td class="muted">Fecha de emision</td><td>{{ $invoice->created_at->format('d/m/Y') }}</td></tr>
                    <tr><td class="muted">Fecha de pago</td><td>{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y') : '-' }}</td></tr>
                    <tr><td class="muted">Estado</td><td><span class="badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <tr>
            <th style="width:12%">Aplicacion</th>
            <th style="width:18%">Plan</th>
            <th style="width:40%">Descripcion</th>
            <th class="right">Importe</th>
        </tr>
        <tr>
            <td>{{ $invoice->subscription->membershipPlan->application ?? '-' }}</td>
            <td>{{ $invoice->subscription->membershipPlan->name }}</td>
            <td class="muted">{{ $invoice->subscription->membershipPlan->description }}</td>
            <td class="right">${{ number_format($invoice->amount, 2) }}</td>
        </tr>
    </table>

    <table style="margin-top:16px;">
        <tr class="total-row">
            <td class="right">TOTAL</td>
            <td class="right" style="width:140px; color:#1db954;">${{ number_format($invoice->amount, 2) }}</td>
        </tr>
    </table>

    <table style="margin-top:24px;">
        <tr>
            <td class="muted">Referencia de pago</td>
            <td class="right">{{ $invoice->payment_reference ?? '-' }}</td>
        </tr>
    </table>

    <div class="footer">
        Subscription Hub &mdash; Factura generada automaticamente &middot; Pagos en modo simulacion
    </div>
</body>
</html>
