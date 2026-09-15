<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de crédito {{ $documentNumber }}</title>
    <style>
        @page { margin: 24px 30px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .header { border-bottom: 3px solid #1a3c6e; margin-bottom: 14px; }
        .header td { vertical-align: top; padding: 0 0 12px; }
        .company { width: 58%; }
        .box { width: 42%; text-align: center; border: 1px solid #1a3c6e; padding: 10px; }
        h1 { color: #1a3c6e; font-size: 19px; margin: 0 0 6px; }
        h2 { color: #1a3c6e; font-size: 14px; margin: 0 0 5px; }
        p { margin: 3px 0; line-height: 1.45; }
        .key { margin-top: 8px; padding: 5px; background: #f3f6fa; word-break: break-all; font-size: 8px; }
        .section { margin: 10px 0; padding: 9px 12px; background: #f6f8fb; border-left: 4px solid #1a3c6e; }
        .section-title { color: #1a3c6e; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .items { margin-top: 12px; }
        .items th { background: #1a3c6e; color: white; padding: 7px; text-align: left; }
        .items td { padding: 7px; border-bottom: 1px solid #e1e6ed; }
        .num { text-align: right; }
        .totals { width: 43%; margin: 14px 0 0 auto; }
        .totals td { padding: 5px 8px; border-top: 1px solid #e1e6ed; }
        .grand td { color: white; background: #1a3c6e; font-weight: bold; font-size: 12px; }
        .footer { margin-top: 22px; border-top: 1px solid #dfe5eb; padding-top: 8px; color: #718096; text-align: center; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="company">
                <h1>{{ $companyName }}</h1>
                @if($tradeName !== $companyName)<p>{{ $tradeName }}</p>@endif
                <p>RUC: {{ $ruc }}</p>
                <p>{{ $matrixAddress }}</p>
                <div class="key">Clave de acceso: {{ $accessKey }}</div>
            </td>
            <td class="box">
                <h2>Nota de crédito</h2>
                <p><strong>N.º {{ $documentNumber }}</strong></p>
                <p>Fecha: {{ $issueDate }}</p>
                <p>Estado: <strong>{{ $status }}</strong></p>
                @if($authorizationNumber !== '')<p>Autorización: {{ $authorizationNumber }}</p>@endif
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Cliente</div>
        <p><strong>{{ $customerName }}</strong> — {{ $customerIdentification }}</p>
        @if($customerAddress !== '')<p>{{ $customerAddress }}</p>@endif
    </div>

    <div class="section">
        <div class="section-title">Comprobante modificado</div>
        <p>Número: <strong>{{ $modifiedDocumentNumber }}</strong> &nbsp; Fecha: {{ $modifiedIssueDate }}</p>
        <p>Motivo: {{ $reason }}</p>
    </div>

    <table class="items">
        <thead><tr><th>Cant.</th><th>Descripción</th><th class="num">P. unitario</th><th class="num">Base</th><th class="num">IVA</th></tr></thead>
        <tbody>
        @foreach($details as $detail)
            <tr>
                <td>{{ $detail['quantity'] }}</td>
                <td>{{ $detail['description'] }}@if($detail['code'] !== '')<br><small>Código: {{ $detail['code'] }}</small>@endif</td>
                <td class="num">{{ $detail['unit_price'] }}</td>
                <td class="num">{{ $detail['taxable_base'] }}</td>
                <td class="num">{{ $detail['tax'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">{{ $subtotal }}</td></tr>
        <tr><td>Descuento</td><td class="num">{{ $discount }}</td></tr>
        @foreach($taxes as $tax)<tr><td>{{ $tax['label'] }}</td><td class="num">{{ $tax['value'] }}</td></tr>@endforeach
        <tr class="grand"><td>Total modificación</td><td class="num">{{ $total }}</td></tr>
    </table>

    @if($additionalInfo->isNotEmpty())
        <div class="section">
            <div class="section-title">Información adicional</div>
            @foreach($additionalInfo as $info)<p><strong>{{ $info->name }}:</strong> {{ $info->value }}</p>@endforeach
        </div>
    @endif
    <div class="footer">Representación impresa de una nota de crédito electrónica. Código SRI: 04.</div>
</body>
</html>
