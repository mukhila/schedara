<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f5fefe;color:#021b2e;margin:0;padding:0}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e3e9ee}
  .hdr{background:#021b2e;padding:28px 32px;color:#fff}
  .brand{font-size:18px;font-weight:800}
  .hdr-sub{font-size:11px;opacity:.6;margin-top:4px}
  .body{padding:32px}
  h2{font-size:18px;font-weight:700;margin-bottom:16px}
  p{font-size:14px;line-height:1.7;color:#4a5568;margin-bottom:14px}
  .period{display:inline-block;background:#f5fefe;border:1px solid #e3e9ee;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:600;color:#4a8ccc;margin-bottom:20px}
  .footer{background:#f5fefe;padding:16px 32px;text-align:center;font-size:11px;color:#a0aec0;border-top:1px solid #e3e9ee}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <div class="brand">{{ $branding?->brand_name ?? 'Schedara' }}</div>
    <div class="hdr-sub">Analytics Report</div>
  </div>
  <div class="body">
    <h2>Hi {{ $client->client_name }},</h2>
    <p>Your analytics report <strong>{{ $report->report_name }}</strong> is ready. Please find it attached to this email.</p>
    @if(!empty($report->report_config['period_start']))
    <div class="period">
      Period: {{ $report->report_config['period_start'] }} – {{ $report->report_config['period_end'] ?? now()->toDateString() }}
    </div>
    @endif
    <p>This report summarises your social media performance for the selected period. If you have any questions, please reach out to your account manager.</p>
  </div>
  <div class="footer">
    @if(!($branding?->hide_saas_branding)) Powered by Schedara &middot; @endif
    Generated {{ $report->generated_at?->format('M d, Y') ?? now()->format('M d, Y') }}
  </div>
</div>
</body>
</html>
