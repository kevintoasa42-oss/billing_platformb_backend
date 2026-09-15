<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura</title>
<style>
  /* ===== Paleta de la app: gris · rojo · blanco =====
     --app-red:       #c62828  (rojo primario)
     --app-gray:      #5f6368  (gris texto/acentos)
     --app-gray-dark: #3c4043  (gris oscuro body)
     --app-gray-light:#f5f5f5  (fondos suaves)
     --app-gray-border:#e0e0e0 (bordes)
     --app-white:     #ffffff
  */

  /* A4 = 595.28pt × 841.89pt.  Márgenes compactos: 36pt (0.5 inch) laterales,
     40pt arriba y 45pt abajo. */
  @page { size: A4; margin: 40pt 36pt 45pt 36pt; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    color: #3c4043;
    font-size: 11px;
  }

  /* Ancho de contenido = 595.28 - 2×36 = 523.28pt.
     Todas las tablas hijas con width:100% se ajustan a este ancho.
     padding-top/bottom refuerzan el margen vertical de @page. */
  .page-wrap {
    width: 523.28pt;
    margin: 0 auto;
    padding-top: 40pt;
    padding-bottom: 45pt;
  }

  /* ===== HEADER ===== */
  .header {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 3px solid #c62828;
    margin-bottom: 16px;
  }
  .header, .info-section, .totals-wrap, .additional, .payment-block, .invoice-box { page-break-inside: avoid; }
  .header td { vertical-align: top; padding-bottom: 16px; }
  .header td.left { width: 60%; padding-right: 18px; }
  .header td.right { width: 40%; }

  .company-name {
    font-size: 21px;
    font-weight: bold;
    color: #c62828;
    letter-spacing: 0.5px;
  }
  .brand-badge {
    display: inline-block;
    width: 25px;
    height: 25px;
    margin-right: 7px;
    border-radius: 7px;
    background: #5f6368;
    vertical-align: -7px;
    text-align: center;
    color: #fff;
    font-size: 20px;
    line-height: 25px;
    overflow: hidden;
  }
  .brand-badge img { width: 19px; height: 19px; margin: 3px; }
  .company-info {
    margin-top: 6px;
    line-height: 1.5;
    color: #5f6368;
    font-size: 10.5px;
  }

  .invoice-box {
    border: 1px solid #c62828;
    border-radius: 6px;
    padding: 12px 16px;
    text-align: center;
  }
  .invoice-box .title {
    font-size: 14px;
    font-weight: bold;
    color: #c62828;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  .invoice-box .number {
    font-size: 15px;
    font-weight: bold;
    margin-top: 4px;
    color: #3c4043;
  }
  .invoice-box .row {
    margin-top: 7px;
    font-size: 10.5px;
    color: #5f6368;
  }
  .invoice-box .row table { width: 100%; border-collapse: collapse; }
  .invoice-box .row td.left { text-align: left; padding: 0; }
  .invoice-box .row td.right { text-align: right; padding: 0; }

  .status-badge {
    display: block;
    margin-top: 10px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 10.5px;
    font-weight: bold;
    letter-spacing: 0.5px;
  }
  .status-authorized { background: #e3f5e9; color: #1e7e42; border: 1px solid #b6e3c6; }
  .status-pending   { background: #fff6e0; color: #b07d00; border: 1px solid #f3d98b; }
  .status-rejected  { background: #fde4e4; color: #b3261e; border: 1px solid #f0b4b4; }
  .status-received  { background: #f5f5f5; color: #5f6368; border: 1px solid #e0e0e0; }
  .status-simulated { background: #eee; color: #5f6368; border: 1px solid #ccc; }

  /* ===== TRIBUTARIA ===== */
  .tax-info {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px dashed #bdbdbd;
  }
  .tax-info h4 {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #c62828;
    margin-bottom: 5px;
  }
  .tax-info p {
    color: #5f6368;
    font-size: 10.5px;
    line-height: 1.6;
  }
  .clave-acceso {
    word-break: break-all;
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    background: #f5f5f5;
    padding: 6px 8px;
    border-radius: 4px;
    font-size: 9.5px;
    margin-top: 5px;
    border: 1px dashed #bdbdbd;
  }

  /* ===== CLIENTE ===== */
  .info-section { width: 100%; margin-bottom: 18px; }
  .info-block {
    background: #f5f5f5;
    border-left: 4px solid #c62828;
    border-radius: 4px;
    padding: 12px 16px;
  }
  .info-block h4 {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #c62828;
    margin-bottom: 8px;
  }
  .info-block p {
    line-height: 1.6;
    color: #3c4043;
  }
  .info-block p strong { color: #1a1a1a; }

  /* ===== TABLA ===== */
  table.items {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
    table-layout: fixed;
  }
  table.items thead { display: table-header-group; }
  table.items tr { page-break-inside: avoid; }
  td, th, p { overflow-wrap: anywhere; word-wrap: break-word; }
  table.items thead th {
    background: #c62828;
    color: #fff;
    text-align: left;
    padding: 9px 10px;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
  }
  table.items thead th.num { text-align: right; }
  table.items tbody td {
    padding: 9px 10px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 11px;
    word-break: break-word;
  }
  table.items tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
  table.items tbody tr.alt { background: #f5f5f5; }

  /* ===== TOTALES + PAGO ===== */
  .totals-wrap { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
  .totals-wrap td { vertical-align: top; }
  .totals-wrap td.left { width: 55%; padding-right: 18px; }
  .totals-wrap td.right { width: 45%; }

  .payment-block {
    background: #f5f5f5;
    border-left: 4px solid #c62828;
    border-radius: 4px;
    padding: 12px 16px;
  }
  .payment-block h4 {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #c62828;
    margin-bottom: 8px;
  }
  .payment-block p {
    line-height: 1.6;
    color: #3c4043;
  }
  .payment-block p strong { color: #1a1a1a; }

  .totals { width: 100%; border-collapse: collapse; }
  .totals .row td {
    padding: 7px 12px;
    font-size: 11px;
    border-top: 1px solid #eeeeee;
  }
  .totals .row.subtotal td { color: #5f6368; }
  .totals .row.subtotal td.num { text-align: right; }
  .totals .row.grand td {
    background: #c62828;
    color: #fff;
    font-weight: bold;
    font-size: 12.5px;
    border-radius: 4px;
    border-top: none;
    padding: 10px 12px;
  }
  .totals .row.grand td.num { text-align: right; }

  /* ===== INFO ADICIONAL ===== */
  .additional { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
  .additional td { vertical-align: top; padding-right: 16px; }
  .additional td:last-child { padding-right: 0; }
  .additional .block {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px 16px;
  }
  .additional .block h4 {
    font-size: 11px;
    text-transform: uppercase;
    color: #c62828;
    margin-bottom: 8px;
    border-bottom: 1px solid #eeeeee;
    padding-bottom: 6px;
  }
  .additional .block p {
    line-height: 1.7;
    color: #5f6368;
    font-size: 10.5px;
  }

  /* ===== FOOTER (fijo al pie de cada página) ===== */
  .footer {
    position: fixed;
    bottom: 0;
    left: 36pt;
    right: 36pt;
    width: 523.28pt;
    border-top: 1px solid #e0e0e0;
    padding-top: 8px;
    padding-bottom: 4px;
    text-align: center;
    color: #9e9e9e;
    font-size: 9.5px;
    line-height: 1.6;
  }
</style>
</head>
<body>
<div class="page-wrap">

  {{-- ===== HEADER ===== --}}
  <table class="header">
    <tr>
      <td class="left">
        <div class="company-name">
          <span class="brand-badge" aria-hidden="true">
            @if(!empty($brandMarkDataUri))
              <img src="{{ $brandMarkDataUri }}" alt="">
            @else
              ☾
            @endif
          </span>{{ $companyName }}
        </div>
        <div class="company-info">
          @if(!empty($matrixAddress)){{ $matrixAddress }}<br>@endif
          RUC: {{ $ruc }}<br>
          @if(!empty($phone))Tel: {{ $phone }}@endif
          @if(!empty($phone) && !empty($email)) &nbsp;|&nbsp; @endif
          @if(!empty($email)){{ $email }}@endif
        </div>
        <div class="tax-info">
          <h4>Información tributaria</h4>
          <p>Ambiente: {{ $ambiente }} &nbsp;|&nbsp; Tipo de emisión: {{ $tipoEmision }}</p>
          @if(!empty($authorizationNumber))
            <p>Autorización SRI: {{ $authorizationNumber }}</p>
          @endif
          @if(!empty($accessKey))
            <div class="clave-acceso">
              Clave de acceso: {{ $accessKey }}
            </div>
          @endif
        </div>
      </td>
      <td class="right">
        <div class="invoice-box">
          <div class="title">Factura</div>
          <div class="number">N.º {{ $documentNumber }}</div>
          <div class="row">
            <table>
              <tr>
                <td class="left">Fecha de emisión</td>
                <td class="right">{{ $issueDate }}</td>
              </tr>
            </table>
          </div>
          <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
      </td>
    </tr>
  </table>

  {{-- ===== INFO CLIENTE ===== --}}
  <div class="info-section">
    <div class="info-block">
      <h4>Facturar a</h4>
      <p><strong>{{ $customerName }}</strong></p>
      @if(!empty($customerIdentification))<p>RUC/CI: {{ $customerIdentification }}</p>@endif
      @if(!empty($customerAddress))<p>{{ $customerAddress }}</p>@endif
      @if(!empty($customerEmail))<p>{{ $customerEmail }}</p>@endif
    </div>
  </div>

  @if(!empty($transporterName))
    <div class="info-section">
      <div class="info-block">
        <h4>Operación de transporte</h4>
        <p><strong>{{ $transporterName }}</strong></p>
        @if(!empty($transporterIdentification))<p>RUC/CI: {{ $transporterIdentification }}</p>@endif
        @if(!empty($transporterPlate))<p>Placa: <strong>{{ $transporterPlate }}</strong></p>@endif
      </div>
    </div>
  @endif

  {{-- ===== TABLA DE ITEMS ===== --}}
  <table class="items">
    <thead>
      <tr>
        <th style="width:8%">Cant.</th>
        <th style="width:14%">Código</th>
        <th style="width:20%">Producto</th>
        <th style="width:22%">Descripción</th>
        <th class="num" style="width:13%">P. Unitario</th>
        <th class="num" style="width:13%">Subtotal</th>
        <th class="num" style="width:10%">IVA</th>
      </tr>
    </thead>
    <tbody>
      @foreach($details as $index => $detail)
        <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
          <td>{{ $detail['quantity'] }}</td>
          <td>{{ $detail['barcode'] }}</td>
          <td>{{ $detail['product_name'] }}</td>
          <td>{{ $detail['description'] }}</td>
          <td class="num">{{ $detail['unit_price'] }}</td>
          <td class="num">{{ $detail['subtotal'] }}</td>
          <td class="num">{{ $detail['tax'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- ===== DETALLES DE PAGO + TOTALES ===== --}}
  <table class="totals-wrap">
    <tr>
      <td class="left">
        <div class="payment-block">
          <h4>Detalles de pago</h4>
          @foreach($payments as $payment)
            <p>Forma de pago: <strong>{{ $payment['method_name'] }}</strong></p>
            @if(!empty($payment['term_label']))
              <p>Plazo: <strong>{{ $payment['term_label'] }}</strong></p>
            @endif
          @endforeach
          @if(empty($payments))
            <p>Sin información de pago.</p>
          @endif
        </div>
      </td>
      <td class="right">
        <table class="totals">
          <tr class="row subtotal"><td>Subtotal</td><td class="num">{{ $subtotal }}</td></tr>
          <tr class="row subtotal"><td>Descuento</td><td class="num">{{ $discount }}</td></tr>
          @foreach($taxes as $tax)
            <tr class="row subtotal"><td>{{ $tax['label'] }}</td><td class="num">{{ $tax['value'] }}</td></tr>
          @endforeach
          <tr class="row grand"><td>Total a pagar</td><td class="num">{{ $total }}</td></tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- ===== INFO ADICIONAL ===== --}}
  @if(!empty($additionalInfo))
    <table class="additional">
      <tr>
        <td>
          <div class="block">
            <h4>Información adicional</h4>
            @foreach($additionalInfo as $info)
              <p><strong>{{ $info['name'] }}:</strong> {{ $info['value'] }}</p>
            @endforeach
          </div>
        </td>
      </tr>
    </table>
  @endif

</div>

{{-- ===== FOOTER (fijo al pie de cada página) ===== --}}
<div class="footer">
  Este documento es una representación impresa de una factura electrónica autorizada por el SRI.<br>
  @if(!empty($companyName))Gracias por su preferencia — {{ $companyName }}<br>@endif
  Proyecto: {{ $projectName }} · {{ $projectSlug }} · v{{ $projectVersion }}
</div>

</body>
</html>
