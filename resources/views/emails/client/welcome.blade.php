<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f5fefe;color:#021b2e;margin:0;padding:0}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e3e9ee}
  .hdr{background:#021b2e;padding:28px 32px;color:#fff;text-align:center}
  .hdr .brand{font-size:20px;font-weight:800}
  .body{padding:32px}
  h2{font-size:20px;font-weight:700;margin-bottom:8px}
  p{font-size:14px;line-height:1.6;color:#4a5568;margin-bottom:16px}
  .btn{display:inline-block;background:#65a1d8;color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:14px}
  .footer{background:#f5fefe;padding:16px 32px;text-align:center;font-size:11px;color:#a0aec0;border-top:1px solid #e3e9ee}
</style></head>
<body>
<div class="wrap">
  <div class="hdr"><div class="brand">Welcome to Schedara 🎉</div></div>
  <div class="body">
    <h2>Hello, {{ $client->client_name }}!</h2>
    <p>Your workspace <strong>{{ $workspace->workspace_name }}</strong> has been set up and is ready for you.</p>
    <p>Complete your onboarding to unlock the full power of your social media management dashboard.</p>
    <p style="text-align:center;margin-top:24px">
      <a href="{{ url('/agency/clients/'.$client->uuid.'/onboarding') }}" class="btn">Complete Onboarding →</a>
    </p>
  </div>
  <div class="footer">© {{ date('Y') }} Schedara. All rights reserved.</div>
</div>
</body>
</html>
