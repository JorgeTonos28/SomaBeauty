<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Ticket #{{ $ticket->id }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  @media print {
    @page { size: 80mm auto; margin: 0 }
    body { margin: 0 }
  }

  :root{
    --ink:#000;
    --accent:#e85aad;
  }

  body{
    font: 14px/1.35 "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    background:#f5f5f5;
    color:var(--ink);
  }

  .wrap{
    width: 80mm;
    margin: 0 auto;
    background:#fff;
    border: 1px solid var(--ink);
    position: relative;
  }

  .scallop{
    height: 8mm;
    background:
      radial-gradient(circle at 12px 8px, var(--accent) 8px, transparent 8px) 0 0/24px 16px repeat-x,
      radial-gradient(circle at 12px 8px, var(--accent) 8px, transparent 8px) 12px 8px/24px 16px repeat-x;
  }
  .scallop.top{ border-bottom:1px solid var(--ink); }
  .scallop.bottom{ transform: rotate(180deg); border-top:1px solid var(--ink); }

  .ticket{
    padding: 10px 10px 6px;
  }

  .center{ text-align:center; }
  .muted{ opacity:.85; font-size:12px; }

  .info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:4px 8px;
    margin-bottom:6px;
  }

  .rule{
    height: 14px;
    margin: 6px 0;
    background:
      linear-gradient(to right, transparent 0, transparent 6px, var(--ink) 6px, var(--ink) 8px, transparent 8px) 0 7px/16px 1px repeat-x;
  }

  .title{
    font-weight: 700;
    letter-spacing: 1px;
  }

  .tbl{
    width:100%;
    border-collapse: collapse;
    font-variant-numeric: tabular-nums;
  }
  .tbl thead th{
    text-align:left;
    font-size:12px;
    font-weight:600;
    padding:2px 0 4px;
    border-bottom:1px dashed var(--ink);
  }
  .tbl td{
    padding:4px 0;
    vertical-align: top;
  }
  .col-qty{ width: 30px; }
  .col-amt{ width: 70px; text-align:right; }

  .totals{
    width:100%;
    font-variant-numeric: tabular-nums;
  }
  .totals td{ padding: 2px 0; }
  .totals .val{ text-align:right; }

  .amount{
    font-weight:800;
    font-size:16px;
  }

  .thanks{
    text-align:center;
    margin-top: 6px;
    font-weight:600;
    letter-spacing:.5px;
  }

  .barcode{
    height: 36px;
    margin: 6px 6px 10px;
    background:
      repeating-linear-gradient(
        to right,
        #000 0, #000 2px,
        transparent 2px, transparent 4px,
        #000 4px, #000 6px,
        transparent 6px, transparent 9px
      );
  }

  .payment-info{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:4px 8px;
    margin-top:6px;
    font-size:12px;
  }

  .full{ grid-column: span 2; }
</style>
</head>
<body>
@php
    $businessName = optional($appearanceSettings)->business_name ?? config('app.name');
    $address = optional($appearanceSettings)->business_address;
    $taxId = optional($appearanceSettings)->tax_id;
    $paymentLabels = [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
        'mixto' => 'Mixto',
    ];
    $paymentLabel = $paymentLabels[$ticket->payment_method] ?? 'No especificado';
    $bankLabel = optional($ticket->bankAccount)?->bank;
    $bankAccount = optional($ticket->bankAccount)?->account;
    $customerName = $ticket->customer_name ?: 'Consumidor final';
    $customerPhone = $ticket->customer_phone;
    $attendedBy = optional($ticket->user)->name ?? 'N/D';
@endphp
  <div class="wrap">
    <div class="scallop top"></div>

    <div class="ticket">
      <div class="center">
        <div class="title">{{ mb_strtoupper($businessName, 'UTF-8') }}</div>
        @if($address)
          <div class="muted">{{ $address }}</div>
        @endif
      </div>

      <div class="rule"></div>

      <div class="muted info-grid">
        <div>{{ $paidAt->format('d/m/Y') }}&nbsp; {{ $paidAt->format('h:i A') }}</div>
        <div style="text-align:right;">Ticket #{{ $ticket->id }}</div>
        <div>Cliente: {{ $customerName }}</div>
        <div style="text-align:right;">Atendido: {{ $attendedBy }}</div>
        @if($customerPhone)
          <div>Tel: {{ $customerPhone }}</div>
        @endif
        <div style="text-align:right;">Pago: {{ $paymentLabel }}</div>
        @if($taxId)
          <div class="full" style="text-align:right;">RNC: {{ $taxId }}</div>
        @endif
        @if($bankLabel || $bankAccount)
          <div class="full" style="text-align:right;">Cuenta: {{ trim(($bankLabel ? $bankLabel.' ' : '').($bankAccount ?? '')) }}</div>
        @endif
      </div>

      <table class="tbl">
        <thead>
          <tr>
            <th class="col-qty">QTY</th>
            <th>DESCRIPCIÓN</th>
            <th class="col-amt">IMP.</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $item)
            <tr>
              <td class="col-qty">{{ $item['qty'] }}</td>
              <td>{{ $item['description'] }}</td>
              <td class="col-amt">RD$ {{ number_format($item['amount'], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="rule"></div>

      <table class="totals">
        <tr>
          <td>Subtotal :</td>
          <td class="val">RD$ {{ number_format($subtotalBeforeDiscount, 2) }}</td>
        </tr>
        <tr>
          <td>Descuento :</td>
          <td class="val">RD$ {{ number_format($totalDiscount, 2) }}</td>
        </tr>
      </table>

      <div style="display:flex; justify-content:space-between; align-items:center; margin:6px 0 2px;">
        <div class="amount">TOTAL</div>
        <div class="amount">RD$ {{ number_format($ticket->total_amount, 2) }}</div>
      </div>

      <div class="payment-info muted">
        <div>Pagado: RD$ {{ number_format($ticket->paid_amount, 2) }}</div>
        <div style="text-align:right;">Cambio: RD$ {{ number_format($ticket->change, 2) }}</div>
        <div class="full" style="text-align:center;">Método: {{ $paymentLabel }}</div>
      </div>

      <div class="thanks">*** ¡GRACIAS POR PREFERIRNOS! ***</div>

      <div class="barcode" aria-hidden="true"></div>
    </div>

    <div class="scallop bottom"></div>
  </div>

  <script>
    window.onload = () => window.print();
  </script>
</body>
</html>
