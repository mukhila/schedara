<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f5fefe;color:#021b2e;margin:0;padding:0}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e3e9ee}
  .hdr{background:#021b2e;padding:28px 32px;color:#fff;text-align:center}
  .progress-bar{height:8px;background:#e3e9ee;border-radius:999px;overflow:hidden;margin-bottom:4px}
  .progress-fill{height:100%;background:linear-gradient(90deg,#65a1d8,#22B07E)}
  .body{padding:32px}
  h2{font-size:18px;font-weight:700;margin-bottom:8px}
  p{font-size:14px;line-height:1.6;color:#4a5568;margin-bottom:16px}
  .btn{display:inline-block;background:#65a1d8;color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px}
  .footer{background:#f5fefe;padding:16px 32px;text-align:center;font-size:11px;color:#a0aec0;border-top:1px solid #e3e9ee}
</style></head>
<body>
<div class="wrap">
  <div class="hdr">
    <div style="font-size:18px;font-weight:800">Almost there, {{ $client->client_name }}!</div>
  </div>
  <div class="body">
    <h2>{{ $progress }}% complete — {{ $pending }} step(s) remaining</h2>
    <div class="progress-bar"><div class="progress-fill" style="width:{{ $progress }}%"></div></div>
    <p style="font-size:12px;color:#a0aec0;margin-top:4px">{{ $progress }}% of onboarding done</p>
    <p>Your workspace is almost ready! Complete the remaining onboarding steps to start managing your social media.</p>
    <p style="text-align:center;margin-top:24px">
      <a href="{{ url('/agency/clients/'.$client->uuid.'/onboarding') }}" class="btn">Continue Onboarding →</a>
    </p>
  </div>
  <div class="footer">© {{ date('Y') }} Schedara. You received this because you have an active workspace.</div>
</div>
</body>
</html>
