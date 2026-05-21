@extends('layouts.backend')
@section('page_title', 'Invoices')

@section('styles')
<style>
.section-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);overflow:hidden;margin-bottom:1.5rem}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{text-align:left;padding:.625rem 1rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(2,27,46,.45);border-bottom:2px solid rgba(2,27,46,.08)}
.data-table td{padding:.875rem 1rem;border-bottom:1px solid rgba(2,27,46,.06);font-size:.875rem;vertical-align:middle}
.data-table tr:last-child td{border-bottom:0}
.badge{font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:5px;white-space:nowrap}
.badge-paid{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2)}
.badge-open{background:rgba(245,158,11,.1);color:#d97706;border:1px solid rgba(245,158,11,.2)}
.badge-void{background:rgba(2,27,46,.06);color:rgba(2,27,46,.4);border:1px solid rgba(2,27,46,.1)}
.badge-draft{background:rgba(107,114,128,.08);color:#6b7280;border:1px solid rgba(107,114,128,.15)}
.btn{display:inline-flex;align-items:center;gap:4px;padding:.4rem .875rem;border-radius:8px;font-family:inherit;font-size:.8rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.2s}
.btn-outline{background:#fff;color:#021b2e;border:1px solid rgba(2,27,46,.2)}.btn-outline:hover{border-color:#65a1d8;color:#65a1d8}
.empty-state{text-align:center;padding:3rem;color:rgba(2,27,46,.35);font-size:.875rem}
.inv-number{font-family:monospace;font-size:.82rem;font-weight:700;color:#021b2e;letter-spacing:.04em}
</style>
@endsection

@section('content')

<div class="section-card">
  <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.07);display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:.9rem;font-weight:800;color:#021b2e">Invoice History</span>
    <span style="font-size:.8rem;color:rgba(2,27,46,.4)">{{ $invoices->total() }} total</span>
  </div>

  @if($invoices->isEmpty())
    <div class="empty-state">No invoices yet.</div>
  @else
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Plan</th>
            <th>Amount</th>
            <th>Tax</th>
            <th>Total</th>
            <th>Status</th>
            <th>Gateway</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoices as $invoice)
            <tr>
              <td><span class="inv-number">{{ $invoice->invoice_number ?? '—' }}</span></td>
              <td style="color:rgba(2,27,46,.55)">{{ $invoice->created_at->format('M j, Y') }}</td>
              <td>{{ $invoice->subscription?->plan?->name ?? ($invoice->description ?? '—') }}</td>
              <td>{{ $invoice->formattedAmount() }}</td>
              <td style="color:rgba(2,27,46,.5);font-size:.82rem">
                @if($invoice->tax > 0)
                  {{ $invoice->tax_label ?? 'Tax' }}: {{ $invoice->tax_rate }}%
                @else
                  —
                @endif
              </td>
              <td style="font-weight:700">
                @php
                  $total = $invoice->total > 0 ? $invoice->total : $invoice->amount;
                  $sym   = match(strtolower($invoice->currency)) { 'inr' => '₹', 'eur' => '€', 'gbp' => '£', default => '$' };
                  echo $sym . number_format($total / 100, 2);
                @endphp
              </td>
              <td>
                <span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
              </td>
              <td style="font-size:.78rem;color:rgba(2,27,46,.45)">{{ ucfirst($invoice->provider) }}</td>
              <td style="text-align:right">
                @if($invoice->invoice_pdf)
                  <a href="{{ route('billing.invoices.download', $invoice->uuid) }}" class="btn btn-outline">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    PDF
                  </a>
                @elseif($invoice->invoice_pdf_url)
                  <a href="{{ $invoice->invoice_pdf_url }}" target="_blank" class="btn btn-outline">PDF</a>
                @elseif($invoice->hosted_invoice_url)
                  <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="btn btn-outline">View</a>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if($invoices->hasPages())
      <div style="padding:1rem 1.25rem;border-top:1px solid rgba(2,27,46,.06)">
        {{ $invoices->links() }}
      </div>
    @endif
  @endif
</div>

@endsection
