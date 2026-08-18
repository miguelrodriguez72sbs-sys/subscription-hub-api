<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura SH-{{ str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 34px 40px 44px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #1e293b;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }
        table { width: 100%; border-collapse: collapse; }
        .header { margin-bottom: 30px; }
        .brand { width: 58%; vertical-align: top; }
        .brand-mark {
            display: inline-block;
            width: 38px;
            height: 38px;
            margin-right: 10px;
            border-radius: 9px;
            background: #4f46e5;
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            line-height: 38px;
            text-align: center;
            vertical-align: middle;
        }
        .brand-copy { display: inline-block; vertical-align: middle; }
        .brand-name { color: #111827; font-size: 20px; font-weight: bold; line-height: 1.1; }
        .brand-tag { margin-top: 3px; color: #64748b; font-size: 9px; }
        .document-title { width: 42%; text-align: right; vertical-align: top; }
        .document-title h1 { margin: 0; color: #111827; font-size: 25px; line-height: 1; }
        .folio { margin-top: 8px; color: #4f46e5; font-size: 11px; font-weight: bold; }
        .status {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .7px;
            text-transform: uppercase;
        }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .panel-row { margin-bottom: 24px; }
        .panel { width: 49%; padding: 15px 16px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .panel-spacer { width: 2%; }
        .eyebrow { margin-bottom: 8px; color: #4f46e5; font-size: 8px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
        .customer-name { margin-bottom: 4px; color: #111827; font-size: 13px; font-weight: bold; }
        .muted { color: #64748b; }
        .meta td { padding: 2px 0; }
        .meta-label { width: 44%; color: #64748b; }
        .meta-value { color: #111827; font-weight: bold; text-align: right; }
        .items { margin-top: 4px; }
        .items th {
            padding: 10px 9px;
            background: #111827;
            color: #ffffff;
            font-size: 8px;
            letter-spacing: .4px;
            text-align: left;
            text-transform: uppercase;
        }
        .items td { padding: 13px 9px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .items .numeric { text-align: right; white-space: nowrap; }
        .item-title { margin-bottom: 3px; color: #111827; font-size: 11px; font-weight: bold; }
        .summary-wrap { margin-top: 14px; }
        .payment-box { width: 58%; padding: 13px 14px; background: #eef2ff; vertical-align: top; }
        .totals-spacer { width: 4%; }
        .totals { width: 38%; vertical-align: top; }
        .totals td { padding: 5px 0; }
        .totals .label { color: #64748b; }
        .totals .value { color: #111827; font-weight: bold; text-align: right; }
        .totals .grand td { padding-top: 9px; border-top: 2px solid #111827; color: #111827; font-size: 15px; font-weight: bold; }
        .notice { margin-top: 26px; padding: 12px 14px; border-left: 4px solid #4f46e5; background: #f8fafc; }
        .notice strong { color: #111827; }
        .footer {
            position: fixed;
            right: 0;
            bottom: -25px;
            left: 0;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $folio = 'SH-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
    $statusLabel = ['paid' => 'Pagada', 'pending' => 'Pendiente', 'failed' => 'Fallida'][$invoice->status] ?? ucfirst($invoice->status);
    $statusClass = ['paid' => 'paid', 'pending' => 'pending', 'failed' => 'failed'][$invoice->status] ?? 'pending';
    $amount = number_format((float) $invoice->amount, 2, '.', ',');
@endphp

<table class="header">
    <tr>
        <td class="brand">
            <span class="brand-mark">S</span>
            <span class="brand-copy">
                <span class="brand-name">Subscription Hub</span><br>
                <span class="brand-tag">Suscripciones y cobros recurrentes</span>
            </span>
        </td>
        <td class="document-title">
            <h1>FACTURA</h1>
            <div class="folio">{{ $folio }}</div>
            <span class="status status-{{ $statusClass }}">{{ $statusLabel }}</span>
        </td>
    </tr>
</table>

<table class="panel-row">
    <tr>
        <td class="panel">
            <div class="eyebrow">Facturar a</div>
            <div class="customer-name">{{ $customer->name }}</div>
            <div class="muted">{{ $customer->email }}</div>
            <div class="muted">Cliente #{{ str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT) }}</div>
        </td>
        <td class="panel-spacer"></td>
        <td class="panel">
            <div class="eyebrow">Información de la factura</div>
            <table class="meta">
                <tr><td class="meta-label">Emisión</td><td class="meta-value">{{ $invoice->created_at?->format('d/m/Y') ?? '-' }}</td></tr>
                <tr><td class="meta-label">Fecha de pago</td><td class="meta-value">{{ $invoice->paid_at?->format('d/m/Y H:i') ?? 'No pagada' }}</td></tr>
                <tr><td class="meta-label">Suscripción</td><td class="meta-value">#{{ $subscription->id }}</td></tr>
                <tr><td class="meta-label">Moneda</td><td class="meta-value">{{ $currency }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width: 48%">Descripción</th>
            <th style="width: 22%">Periodo</th>
            <th class="numeric" style="width: 12%">Cantidad</th>
            <th class="numeric" style="width: 18%">Importe</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="item-title">Plan {{ $plan->name }}</div>
                <div class="muted">{{ $plan->description ?: 'Servicio de membresía por suscripción.' }}</div>
            </td>
            <td>
                {{ $subscription->starts_at?->format('d/m/Y') ?? '-' }}<br>
                <span class="muted">al {{ $subscription->ends_at?->format('d/m/Y') ?? '-' }}</span>
            </td>
            <td class="numeric">1</td>
            <td class="numeric">{{ $currency }} {{ $amount }}</td>
        </tr>
    </tbody>
</table>

<table class="summary-wrap">
    <tr>
        <td class="payment-box">
            <div class="eyebrow">Detalle del pago</div>
            <table class="meta">
                <tr><td class="meta-label">Pasarela</td><td class="meta-value">{{ $payment?->gateway ? ucfirst($payment->gateway) : '-' }}</td></tr>
                <tr><td class="meta-label">Resultado</td><td class="meta-value">{{ $payment?->status ? ucfirst($payment->status) : $statusLabel }}</td></tr>
                <tr><td class="meta-label">Referencia</td><td class="meta-value">{{ $payment?->reference ?? $invoice->payment_reference ?? '-' }}</td></tr>
                <tr><td class="meta-label">Próximo cobro</td><td class="meta-value">{{ $subscription->next_billing_date?->format('d/m/Y') ?? '-' }}</td></tr>
            </table>
        </td>
        <td class="totals-spacer"></td>
        <td class="totals">
            <table>
                <tr><td class="label">Subtotal</td><td class="value">{{ $currency }} {{ $amount }}</td></tr>
                <tr><td class="label">Impuestos</td><td class="value">{{ $currency }} 0.00</td></tr>
                <tr class="grand"><td>Total</td><td class="value">{{ $currency }} {{ $amount }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="notice">
    @if ($invoice->status === 'paid')
        <strong>Pago recibido.</strong> Esta factura registra un cobro procesado correctamente por {{ $payment?->gateway ?? 'la pasarela configurada' }}.
    @elseif ($invoice->status === 'failed')
        <strong>Pago no procesado.</strong> El intento fue rechazado o no pudo completarse. Puedes volver a intentar el pago desde el portal.
    @else
        <strong>Pago pendiente.</strong> El importe total permanece pendiente de liquidación desde el portal de cliente.
    @endif
</div>

<div class="footer">
    Documento generado por Subscription Hub el {{ now()->format('d/m/Y H:i') }}. Comprobante informativo; no sustituye un comprobante fiscal.
</div>
</body>
</html>
