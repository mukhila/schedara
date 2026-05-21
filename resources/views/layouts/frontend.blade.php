<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Schedara — Smart Scheduling Elevated')</title>
  <meta name="description" content="@yield('meta_description', 'Plan, publish, and measure social campaigns across 8 networks with an AI co-pilot, shared inbox, and analytics that actually answer the question.')">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    :root{
      --ink:#021b2e;
      --paper:#f5fefe;
      --brand:#65a1d8;
      --brand-d:#4a8ccc;
      --brand-l:#8bb4dc;
      --mint:#22B07E;
      --gold:#FDBB1F;
      --coral:#FF401C;
    }
    html{scroll-behavior:smooth;}
    body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--paper);color:var(--ink);overflow-x:hidden;-webkit-font-smoothing:antialiased;}

    /* ── KEYFRAMES ── */
    @keyframes fadeUp{from{opacity:0;transform:translateY(32px)}to{opacity:1;transform:translateY(0)}}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}
    @keyframes slideDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
    @keyframes wordReveal{from{opacity:0;transform:translateY(24px) rotateX(-20deg)}to{opacity:1;transform:translateY(0) rotateX(0)}}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}
    @keyframes floatR{0%,100%{transform:translateY(0) rotate(2deg)}50%{transform:translateY(-10px) rotate(-1deg)}}
    @keyframes orbDrift{0%{transform:translate(0,0) scale(1)}33%{transform:translate(40px,-50px) scale(1.08)}66%{transform:translate(-25px,30px) scale(0.94)}100%{transform:translate(0,0) scale(1)}}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
    @keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
    @keyframes lineGrow{from{width:0;opacity:0}to{width:100%;opacity:1}}
    @keyframes glowPulse{0%,100%{box-shadow:0 8px 30px rgba(101,161,216,.35)}50%{box-shadow:0 8px 50px rgba(101,161,216,.6)}}
    @keyframes barGrow{from{height:0}to{height:var(--h)}}
    @keyframes particleDrift{0%,100%{transform:translateY(0) translateX(0);opacity:.5}50%{transform:translateY(-60px) translateX(20px);opacity:1}}

    /* ── SCROLL REVEAL ── */
    .reveal{opacity:0;transform:translateY(44px);transition:opacity .75s ease,transform .75s ease;}
    .reveal.visible{opacity:1;transform:none;}
    .reveal-l{opacity:0;transform:translateX(-44px);transition:opacity .75s ease,transform .75s ease;}
    .reveal-l.visible{opacity:1;transform:none;}
    .reveal-r{opacity:0;transform:translateX(44px);transition:opacity .75s ease,transform .75s ease;}
    .reveal-r.visible{opacity:1;transform:none;}
    .reveal-s{opacity:0;transform:scale(.9);transition:opacity .75s ease,transform .75s ease;}
    .reveal-s.visible{opacity:1;transform:scale(1);}
    .d1{transition-delay:.1s}.d2{transition-delay:.2s}.d3{transition-delay:.3s}.d4{transition-delay:.4s}.d5{transition-delay:.5s}.d6{transition-delay:.6s}

    /* ── SCROLL PROGRESS ── */
    #progress{position:fixed;top:0;left:0;height:3px;width:0;background:linear-gradient(90deg,var(--brand),var(--mint));z-index:200;transition:width .1s;}

    /* ── NAV ── */
    .nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:0 40px;height:70px;display:flex;align-items:center;justify-content:space-between;transition:all .4s ease;}
    .nav.scrolled{background:rgba(2,27,46,.93);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-bottom:1px solid rgba(101,161,216,.14);}
    .nav-brand{display:flex;align-items:center;gap:12px;text-decoration:none;}
    .nav-brand img{height:38px;width:auto;}
    .nav-links{display:flex;align-items:center;gap:32px;list-style:none;}
    .nav-links a{text-decoration:none;color:rgba(245,254,254,.7);font-size:14px;font-weight:600;transition:color .25s;}
    .nav-links a:hover{color:var(--paper);}
    .nav-links a.active{color:var(--paper);}
    .nav-actions{display:flex;align-items:center;gap:10px;}
    .btn-ghost{text-decoration:none;color:rgba(245,254,254,.7);font-size:14px;font-weight:600;padding:8px 16px;border-radius:9px;transition:all .25s;}
    .btn-ghost:hover{color:white;background:rgba(255,255,255,.08);}
    .btn-nav{text-decoration:none;background:var(--brand);color:white;font-size:14px;font-weight:700;padding:10px 22px;border-radius:10px;display:inline-flex;align-items:center;gap:7px;transition:all .3s;}
    .btn-nav:hover{background:var(--brand-d);transform:translateY(-1px);box-shadow:0 8px 24px rgba(101,161,216,.4);}
    .hamburger{display:none;background:none;border:none;cursor:pointer;padding:8px;}
    .hamburger span{display:block;width:22px;height:2px;background:white;margin:5px 0;transition:.3s;}

    /* ── SECTION SHARED ── */
    .sec{padding:100px 40px;}
    .sec-c{max-width:1280px;margin:0 auto;}
    .sec-eyebrow{display:inline-block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--brand-d);margin-bottom:14px;}
    .sec-h2{font-size:clamp(32px,4.2vw,54px);font-weight:900;letter-spacing:-1.8px;line-height:1.07;margin-bottom:18px;}
    .sec-p{font-size:17px;color:rgba(2,27,46,.55);line-height:1.75;max-width:580px;}

    /* ── WAVE DIVIDERS ── */
    .wave-wrap{display:block;line-height:0;overflow:hidden;}
    .wave-wrap svg{display:block;width:100%;}

    /* ── MARQUEE ── */
    .marquee-sec{background:white;border-top:1px solid rgba(2,27,46,.07);border-bottom:1px solid rgba(2,27,46,.07);padding:28px 0;overflow:hidden;}
    .marquee-label{text-align:center;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:2.5px;color:rgba(2,27,46,.32);margin-bottom:18px;}
    .marquee-wrap{overflow:hidden;mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent);}
    .marquee-track{display:flex;gap:0;animation:marquee 30s linear infinite;width:max-content;}
    .marquee-inner{display:flex;align-items:center;gap:56px;padding-right:56px;}
    .marquee-item{font-size:19px;font-weight:800;color:rgba(2,27,46,.27);letter-spacing:-.3px;white-space:nowrap;}

    /* ── HERO ── */
    .hero{min-height:100vh;background:linear-gradient(135deg,#021b2e 0%,#08263e 35%,#0d3356 65%,#021b2e 100%);position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;padding:100px 40px 0;}
    .hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(101,161,216,.055) 1px,transparent 1px),linear-gradient(90deg,rgba(101,161,216,.055) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse 85% 85% at 50% 50%,#000 20%,transparent 80%);pointer-events:none;}
    .orb{position:absolute;border-radius:50%;filter:blur(90px);pointer-events:none;}
    .orb1{width:700px;height:700px;background:radial-gradient(circle,rgba(101,161,216,.18) 0%,transparent 70%);top:-200px;right:-200px;animation:orbDrift 14s ease-in-out infinite;}
    .orb2{width:500px;height:500px;background:radial-gradient(circle,rgba(101,161,216,.13) 0%,transparent 70%);bottom:0;left:-150px;animation:orbDrift 18s ease-in-out infinite reverse;}
    .orb3{width:280px;height:280px;background:radial-gradient(circle,rgba(34,176,126,.1) 0%,transparent 70%);top:40%;left:45%;animation:orbDrift 11s ease-in-out infinite 3s;}
    .particles{position:absolute;inset:0;pointer-events:none;overflow:hidden;}
    .p{position:absolute;width:2px;height:2px;border-radius:50%;background:var(--brand);animation:particleDrift linear infinite;}
    .hero-content{position:relative;z-index:2;text-align:center;max-width:940px;width:100%;}
    .hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(101,161,216,.1);border:1px solid rgba(101,161,216,.22);border-radius:100px;padding:5px 18px 5px 5px;margin-bottom:36px;animation:slideDown .9s ease both .2s;}
    .badge-chip{background:var(--brand);color:white;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;padding:4px 11px;border-radius:100px;}
    .badge-text{font-size:13px;font-weight:500;color:rgba(245,254,254,.8);}
    .hero-h1{font-size:clamp(46px,7.5vw,92px);font-weight:900;line-height:1.01;letter-spacing:-2.5px;color:var(--paper);margin-bottom:28px;perspective:800px;}
    .hero-h1 .word{display:inline-block;animation:wordReveal .65s ease both;}
    .hero-h1 .space{display:inline-block;width:.28em;}
    .hero-h1 .hl{color:var(--brand);position:relative;}
    .hero-h1 .hl::after{content:'';position:absolute;bottom:-6px;left:0;height:4px;border-radius:2px;background:linear-gradient(90deg,var(--brand),transparent);animation:lineGrow 1.1s ease both 1.3s;width:0;}
    .hero-sub{font-size:clamp(16px,2.4vw,19px);color:rgba(245,254,254,.6);line-height:1.75;max-width:620px;margin:0 auto 44px;min-height:56px;animation:fadeUp .8s ease both 1.1s;}
    .cursor{display:inline-block;width:2px;height:1em;background:var(--brand);vertical-align:-.05em;animation:blink 1s step-end infinite;}
    .hero-ctas{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-bottom:52px;animation:fadeUp .8s ease both 1.3s;}
    .btn-hero{text-decoration:none;font-size:16px;font-weight:700;padding:16px 32px;border-radius:14px;display:inline-flex;align-items:center;gap:10px;transition:all .3s;}
    .btn-hero-p{background:linear-gradient(135deg,var(--brand) 0%,var(--brand-d) 100%);color:white;box-shadow:0 8px 32px rgba(101,161,216,.35);animation:glowPulse 3s ease-in-out infinite;}
    .btn-hero-p:hover{transform:translateY(-3px);box-shadow:0 16px 44px rgba(101,161,216,.55);}
    .btn-hero-s{background:rgba(255,255,255,.07);border:1px solid rgba(101,161,216,.28);color:rgba(245,254,254,.9);backdrop-filter:blur(10px);}
    .btn-hero-s:hover{background:rgba(255,255,255,.12);border-color:rgba(101,161,216,.5);transform:translateY(-2px);}
    .hero-proof{display:flex;align-items:center;justify-content:center;gap:36px;flex-wrap:wrap;animation:fadeUp .8s ease both 1.5s;}
    .av-stack{display:flex;align-items:center;}
    .av{width:38px;height:38px;border-radius:50%;border:3px solid rgba(2,27,46,.8);margin-left:-10px;background:linear-gradient(135deg,var(--brand-l),var(--ink));font-size:11px;font-weight:700;color:white;display:flex;align-items:center;justify-content:center;}
    .av:first-child{margin-left:0;}
    .proof-stars{color:var(--gold);font-size:15px;display:flex;gap:2px;}
    .proof-t{font-size:13px;color:rgba(245,254,254,.55);font-weight:500;}
    .hero-cards{position:relative;z-index:2;width:100%;max-width:1160px;height:200px;margin:52px auto 0;}
    .fcard{position:absolute;background:rgba(255,255,255,.07);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(101,161,216,.18);border-radius:22px;padding:22px;}
    .fcard-eng{width:210px;left:20px;top:10px;animation:float 7s ease-in-out infinite;}
    .fcard-sched{width:250px;right:20px;top:30px;animation:floatR 8s ease-in-out infinite 1s;}
    .fcard-ai{width:195px;left:50%;transform:translateX(-50%);top:0;animation:float 6s ease-in-out infinite .5s;}
    .fc-label{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:rgba(245,254,254,.45);margin-bottom:6px;}
    .fc-value{font-size:26px;font-weight:900;color:white;line-height:1;}
    .fc-sub{font-size:11px;color:rgba(245,254,254,.55);margin-top:4px;}
    .fc-badge{display:inline-block;font-size:11px;font-weight:700;padding:3px 9px;border-radius:7px;margin-top:7px;}
    .badge-green{background:rgba(34,176,126,.2);color:#48d8a3;}
    .badge-brand{background:rgba(101,161,216,.2);color:var(--brand-l);}
    .live-dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--mint);animation:pulse 2s ease-in-out infinite;margin-right:5px;vertical-align:middle;}

    /* ── FEATURES BENTO ── */
    .bento{display:grid;grid-template-columns:repeat(6,1fr);gap:18px;margin-top:60px;}
    .bc{background:white;border:1px solid rgba(2,27,46,.08);border-radius:28px;padding:30px;overflow:hidden;position:relative;transition:transform .4s ease,box-shadow .4s ease;}
    .bc:hover{transform:translateY(-7px);box-shadow:0 28px 64px -20px rgba(2,27,46,.14);}
    .bc4{grid-column:span 4;}.bc2{grid-column:span 2;}.bc3{grid-column:span 3;}
    .bc-dark{background:linear-gradient(145deg,var(--ink) 0%,#0d2f4a 100%);color:white;}
    .bc-ink{background:linear-gradient(145deg,#082945 0%,#143e62 100%);color:white;}
    .ftag{display:inline-flex;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;padding:4px 12px;border-radius:100px;margin-bottom:14px;}
    .ftag-b{background:rgba(101,161,216,.1);color:var(--brand-d);}
    .ftag-d{background:rgba(255,255,255,.1);color:rgba(255,255,255,.65);}
    .ftag-g{background:rgba(34,176,126,.15);color:var(--mint);}
    .fh3{font-size:21px;font-weight:800;letter-spacing:-.4px;margin-bottom:10px;}
    .fd{font-size:13.5px;line-height:1.65;color:rgba(2,27,46,.52);}
    .fd-w{color:rgba(255,255,255,.58);}
    .mini-cal{background:var(--paper);border:1px solid rgba(2,27,46,.07);border-radius:18px;padding:16px;margin-top:22px;}
    .mch{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
    .mch .mo{font-size:13px;font-weight:700;}
    .mch-nav{display:flex;gap:4px;}
    .mch-nav button{width:27px;height:27px;border-radius:8px;border:1px solid rgba(2,27,46,.09);background:white;cursor:pointer;font-size:13px;color:rgba(2,27,46,.45);}
    .cal-g{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
    .cal-dh{font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:rgba(2,27,46,.3);text-align:center;padding:3px 0;}
    .cal-d{aspect-ratio:1;border-radius:8px;background:rgba(2,27,46,.03);padding:4px;font-size:9px;color:rgba(2,27,46,.38);}
    .cal-d.hi{background:rgba(101,161,216,.1);border:1px solid rgba(101,161,216,.28);}
    .cal-d .dn{font-weight:700;color:var(--brand-d);}
    .cdot{height:3px;border-radius:2px;margin-top:2px;}
    .ai-box{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.11);border-radius:16px;padding:14px 18px;font-size:12px;line-height:1.6;margin-top:22px;}
    .ai-lbl{color:rgba(255,255,255,.38);margin-bottom:5px;}
    .ai-out{color:white;}
    .inbox-list{margin-top:18px;display:flex;flex-direction:column;gap:8px;}
    .ii{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--paper);border-radius:13px;}
    .ii-av{width:30px;height:30px;border-radius:50%;font-size:10px;font-weight:700;color:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .ig-av{background:linear-gradient(135deg,#f09433,#dc2743,#bc1888);}
    .tw-av{background:#000;}
    .ii-body{flex:1;min-width:0;}
    .ii-name{font-size:12px;font-weight:700;}
    .ii-msg{font-size:11px;color:rgba(2,27,46,.52);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .ii-tag{font-size:9px;font-weight:800;padding:2px 7px;border-radius:5px;background:rgba(255,64,28,.1);color:var(--coral);}
    .ap-list{margin-top:22px;display:flex;flex-direction:column;gap:8px;}
    .ap-item{display:flex;align-items:center;gap:12px;padding:12px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:14px;}
    .ap-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--brand-l),var(--ink));flex-shrink:0;}
    .ap-info{flex:1;}
    .ap-name{font-size:12px;font-weight:700;color:white;}
    .ap-sub{font-size:11px;color:rgba(255,255,255,.45);}
    .ap-s{font-size:10px;font-weight:800;padding:3px 9px;border-radius:7px;}
    .s-ok{background:rgba(34,176,126,.18);color:#48d8a3;}
    .s-rev{background:rgba(253,187,31,.18);color:var(--gold);}
    .bar-chart{display:flex;align-items:flex-end;gap:7px;height:96px;margin-top:22px;}
    .bar{flex:1;border-radius:5px 5px 0 0;background:linear-gradient(180deg,var(--brand),rgba(101,161,216,.3));transform-origin:bottom;animation:barGrow 1.2s ease both;}
    .bar:nth-child(1){--h:40%;height:40%;animation-delay:.1s;}
    .bar:nth-child(2){--h:58%;height:58%;animation-delay:.15s;}
    .bar:nth-child(3){--h:36%;height:36%;animation-delay:.2s;}
    .bar:nth-child(4){--h:72%;height:72%;animation-delay:.25s;}
    .bar:nth-child(5){--h:91%;height:91%;animation-delay:.3s;}
    .bar:nth-child(6){--h:78%;height:78%;animation-delay:.35s;}
    .bar:nth-child(7){--h:52%;height:52%;animation-delay:.4s;}
    .bar-days{display:flex;justify-content:space-between;font-size:9.5px;color:rgba(2,27,46,.35);font-weight:600;margin-top:4px;}

    /* ── STATS ── */
    .stats-band{background:linear-gradient(135deg,var(--ink) 0%,#0a2f4a 100%);padding:72px 40px;position:relative;overflow:hidden;}
    .stats-orb{position:absolute;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(101,161,216,.15) 0%,transparent 70%);top:-150px;right:-150px;pointer-events:none;animation:orbDrift 16s ease-in-out infinite;}
    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);max-width:1280px;margin:0 auto;}
    .stat-c{text-align:center;padding:36px 24px;border-right:1px solid rgba(101,161,216,.1);}
    .stat-c:last-child{border-right:none;}
    .stat-n{font-size:clamp(44px,5vw,64px);font-weight:900;letter-spacing:-2px;color:white;line-height:1;}
    .stat-n .acc{color:var(--brand);}
    .stat-l{font-size:14px;color:rgba(245,254,254,.48);font-weight:500;margin-top:8px;}

    /* ── HOW IT WORKS ── */
    .steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;margin-top:70px;}
    .step-card{position:relative;}
    .step-num{font-size:88px;font-weight:900;color:rgba(101,161,216,.07);line-height:1;margin-bottom:12px;transition:color .5s;}
    .step-card:hover .step-num{color:rgba(101,161,216,.17);}
    .step-ico{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,rgba(101,161,216,.13),rgba(101,161,216,.04));border:1px solid rgba(101,161,216,.2);display:flex;align-items:center;justify-content:center;margin-bottom:20px;}
    .step-h{font-size:19px;font-weight:800;margin-bottom:10px;letter-spacing:-.3px;}
    .step-p{font-size:14px;color:rgba(2,27,46,.52);line-height:1.7;}
    .step-line{position:absolute;top:90px;right:-16px;width:32px;height:2px;background:linear-gradient(90deg,rgba(101,161,216,.3),transparent);}

    /* ── CHANNELS ── */
    .ch-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:12px;margin-top:52px;}
    .ch-card{display:flex;flex-direction:column;align-items:center;gap:10px;padding:22px 12px;background:white;border:1px solid rgba(2,27,46,.07);border-radius:22px;text-decoration:none;transition:all .35s;}
    .ch-card:hover{transform:translateY(-9px);box-shadow:0 18px 40px -12px rgba(101,161,216,.3);border-color:rgba(101,161,216,.3);background:white;}
    .ch-ico{width:50px;height:50px;border-radius:15px;display:flex;align-items:center;justify-content:center;color:white;font-size:16px;font-weight:800;}
    .ch-name{font-size:11px;font-weight:700;color:var(--ink);}
    .c-ig{background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);}
    .c-fb{background:#1877f2;}.c-tw{background:#0f0f0f;}.c-li{background:#0a66c2;}
    .c-tt{background:#010101;}.c-yt{background:#ff0000;}.c-th{background:#101010;}.c-pn{background:#e60023;}

    /* ── TESTIMONIAL ── */
    .testi-grid{display:grid;grid-template-columns:1.35fr 1fr;gap:28px;align-items:stretch;margin-top:70px;}
    .testi-main{background:linear-gradient(145deg,var(--ink) 0%,#0d3358 100%);border-radius:36px;padding:56px;position:relative;overflow:hidden;}
    .testi-orb{position:absolute;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(101,161,216,.2) 0%,transparent 70%);top:-80px;right:-80px;pointer-events:none;}
    .quote-mk{font-size:88px;line-height:.8;color:rgba(101,161,216,.28);font-family:Georgia,serif;margin-bottom:16px;}
    .quote-txt{font-size:20px;font-weight:600;line-height:1.55;color:rgba(245,254,254,.9);margin-bottom:32px;position:relative;z-index:1;}
    .quote-auth{display:flex;align-items:center;gap:16px;}
    .quote-av{width:54px;height:54px;border-radius:50%;background:linear-gradient(135deg,var(--brand-l),var(--brand-d));border:3px solid rgba(101,161,216,.28);}
    .qa-name{font-size:15px;font-weight:700;color:white;}
    .qa-role{font-size:13px;color:rgba(245,254,254,.48);}
    .testi-aside{display:flex;flex-direction:column;gap:14px;}
    .ts-box{background:white;border:1px solid rgba(2,27,46,.07);border-radius:26px;padding:28px 32px;flex:1;transition:all .35s;}
    .ts-box:hover{transform:translateX(7px);border-color:rgba(101,161,216,.3);}
    .ts-big{font-size:50px;font-weight:900;letter-spacing:-2px;line-height:1;}
    .ts-desc{font-size:13px;color:rgba(2,27,46,.48);margin-top:5px;}

    /* ── PRICING ── */
    .pricing-toggle{display:inline-flex;align-items:center;background:var(--paper);border:1px solid rgba(2,27,46,.1);border-radius:100px;padding:4px;margin-top:24px;}
    .tgl-btn{background:none;border:none;padding:10px 24px;border-radius:100px;font-size:14px;font-weight:600;cursor:pointer;color:rgba(2,27,46,.48);transition:.3s;font-family:inherit;}
    .tgl-btn.active{background:var(--ink);color:white;}
    .price-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:52px;}
    .pc{border-radius:30px;padding:34px;display:flex;flex-direction:column;border:1px solid rgba(2,27,46,.08);background:white;transition:all .4s;position:relative;}
    .pc:hover{transform:translateY(-9px);box-shadow:0 28px 70px -18px rgba(2,27,46,.13);}
    .pc-feat{background:var(--ink);border-color:transparent;box-shadow:0 20px 60px -16px rgba(2,27,46,.4);}
    .pc-feat:hover{box-shadow:0 32px 80px -16px rgba(2,27,46,.55);}
    .pc-badge{position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--brand);color:var(--ink);font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;padding:5px 18px;border-radius:100px;white-space:nowrap;}
    .pc-plan{font-size:18px;font-weight:800;margin-bottom:3px;}
    .pc-for{font-size:12px;color:rgba(2,27,46,.38);}
    .pc-for-w{color:rgba(255,255,255,.38);}
    .pc-price{font-size:58px;font-weight:900;letter-spacing:-3px;line-height:1;margin:20px 0 4px;}
    .pc-per{font-size:13px;color:rgba(2,27,46,.38);}
    .pc-per-w{color:rgba(255,255,255,.38);}
    .pc-btn{display:block;text-align:center;text-decoration:none;padding:14px;border-radius:15px;font-size:15px;font-weight:700;margin:24px 0;transition:.3s;}
    .pb-out{background:var(--paper);border:1px solid rgba(2,27,46,.1);color:var(--ink);}
    .pb-out:hover{border-color:var(--brand);background:rgba(101,161,216,.04);}
    .pb-w{background:white;color:var(--ink);}
    .pb-w:hover{background:rgba(245,254,254,.9);}
    .pc-feats{list-style:none;flex:1;}
    .pc-feats li{display:flex;align-items:flex-start;gap:10px;padding:8px 0;font-size:13.5px;border-bottom:1px solid rgba(2,27,46,.05);}
    .pc-feats.wht li{border-bottom-color:rgba(255,255,255,.06);color:rgba(255,255,255,.8);}
    .pc-feats li:last-child{border-bottom:none;}
    .chk{width:20px;height:20px;border-radius:50%;background:rgba(34,176,126,.1);color:var(--mint);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;}
    .chk-w{background:rgba(34,176,126,.2);}

    /* ── FAQ ── */
    .faq-list{max-width:760px;margin:52px auto 0;display:flex;flex-direction:column;gap:10px;}
    .faq-i{background:white;border:1px solid rgba(2,27,46,.07);border-radius:20px;overflow:hidden;transition:border-color .3s;}
    .faq-i.open{border-color:rgba(101,161,216,.3);box-shadow:0 4px 20px rgba(101,161,216,.08);}
    .faq-q{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;cursor:pointer;font-size:16px;font-weight:700;gap:16px;}
    .faq-chv{width:20px;height:20px;color:rgba(2,27,46,.28);flex-shrink:0;transition:transform .3s,color .3s;}
    .faq-i.open .faq-chv{transform:rotate(180deg);color:var(--brand);}
    .faq-a{max-height:0;overflow:hidden;transition:max-height .45s ease,padding .3s;font-size:14px;color:rgba(2,27,46,.58);line-height:1.75;padding:0 24px;}
    .faq-i.open .faq-a{max-height:180px;padding-bottom:22px;}

    /* ── CTA ── */
    .cta-outer{padding:0 40px 80px;background:white;}
    .cta-inner{max-width:1280px;margin:0 auto;background:linear-gradient(135deg,var(--ink) 0%,#0e3256 55%,#1b4e7a 100%);border-radius:42px;padding:84px 64px;text-align:center;position:relative;overflow:hidden;}
    .cta-o1{position:absolute;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle,rgba(101,161,216,.25) 0%,transparent 70%);top:-120px;right:-120px;animation:orbDrift 14s ease-in-out infinite;}
    .cta-o2{position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(101,161,216,.18) 0%,transparent 70%);bottom:-80px;left:-80px;animation:orbDrift 17s ease-in-out infinite reverse;}
    .cta-grid-bg{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:44px 44px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,#000 15%,transparent 80%);}
    .cta-rel{position:relative;z-index:1;}
    .cta-h2{font-size:clamp(36px,5vw,66px);font-weight:900;letter-spacing:-2.5px;line-height:1.04;color:white;max-width:700px;margin:0 auto 20px;}
    .cta-sub{font-size:18px;color:rgba(255,255,255,.6);max-width:460px;margin:0 auto 44px;line-height:1.65;}
    .cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;}
    .cta-btn-p{text-decoration:none;background:white;color:var(--ink);font-size:16px;font-weight:700;padding:16px 34px;border-radius:14px;display:inline-flex;align-items:center;gap:9px;transition:.3s;}
    .cta-btn-p:hover{background:rgba(245,254,254,.93);transform:translateY(-2px);}
    .cta-btn-s{text-decoration:none;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:white;font-size:16px;font-weight:600;padding:16px 34px;border-radius:14px;transition:.3s;backdrop-filter:blur(10px);}
    .cta-btn-s:hover{background:rgba(255,255,255,.15);}
    .cta-perks{display:flex;justify-content:center;gap:32px;flex-wrap:wrap;margin-top:32px;}
    .cta-perk{display:flex;align-items:center;gap:8px;font-size:14px;color:rgba(255,255,255,.55);}

    /* ── FOOTER ── */
    .footer{background:var(--ink);color:rgba(245,254,254,.55);padding:64px 40px 32px;}
    .footer-inner{max-width:1280px;margin:0 auto;}
    .footer-grid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr 1fr;gap:48px;margin-bottom:48px;}
    .f-logo{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
    .f-logo img{height:34px;}
    .f-desc{font-size:13.5px;max-width:240px;line-height:1.75;margin-bottom:20px;}
    .f-socials{display:flex;gap:8px;}
    .f-soc{width:36px;height:36px;background:rgba(255,255,255,.05);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;color:rgba(245,254,254,.55);transition:.3s;}
    .f-soc:hover{background:rgba(101,161,216,.18);color:var(--brand);}
    .f-col-t{font-size:13px;font-weight:700;color:white;margin-bottom:16px;}
    .f-links{list-style:none;display:flex;flex-direction:column;gap:10px;}
    .f-links a{text-decoration:none;font-size:13.5px;color:rgba(245,254,254,.5);transition:color .25s;}
    .f-links a:hover{color:white;}
    .footer-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;font-size:13px;}
    .status-dot{width:7px;height:7px;border-radius:50%;background:var(--mint);animation:pulse 2s ease-in-out infinite;display:inline-block;margin-right:6px;}

    /* ── BACK TO TOP ── */
    #btt{position:fixed;bottom:32px;right:32px;width:46px;height:46px;border-radius:50%;background:var(--ink);border:1px solid rgba(101,161,216,.3);color:white;display:flex;align-items:center;justify-content:center;cursor:pointer;text-decoration:none;opacity:0;transform:translateY(20px);transition:.35s;z-index:50;}
    #btt.show{opacity:1;transform:translateY(0);}
    #btt:hover{background:var(--brand-d);transform:translateY(-4px);}

    /* ── MOBILE NAV ── */
    .mob-nav{display:none;position:fixed;top:70px;left:0;right:0;background:rgba(2,27,46,.96);backdrop-filter:blur(24px);border-bottom:1px solid rgba(101,161,216,.12);z-index:99;padding:20px 24px;}
    .mob-nav.open{display:block;}
    .mob-links{list-style:none;display:flex;flex-direction:column;gap:2px;}
    .mob-links a{text-decoration:none;color:rgba(245,254,254,.7);font-size:16px;font-weight:600;padding:13px 16px;border-radius:12px;display:block;transition:.2s;}
    .mob-links a:hover{background:rgba(101,161,216,.1);color:white;}
    .mob-btns{margin-top:14px;display:flex;flex-direction:column;gap:8px;}

    /* ── RESPONSIVE ── */
    @media(max-width:1024px){
      .bento{grid-template-columns:repeat(2,1fr);}
      .bc4,.bc3{grid-column:span 2;}.bc2{grid-column:span 1;}
      .stats-grid{grid-template-columns:repeat(2,1fr);}
      .steps-grid{grid-template-columns:repeat(2,1fr);}
      .ch-grid{grid-template-columns:repeat(4,1fr);}
      .testi-grid{grid-template-columns:1fr;}
      .price-grid{grid-template-columns:1fr;max-width:420px;margin-left:auto;margin-right:auto;}
      .footer-grid{grid-template-columns:1fr 1fr 1fr;}
    }
    @media(max-width:768px){
      .nav-links,.nav-actions{display:none;}
      .hamburger{display:block;}
      .hero{padding:86px 20px 0;}
      .hero-cards{display:none;}
      .bento{grid-template-columns:1fr;}
      .bc4,.bc2,.bc3{grid-column:span 1;}
      .ch-grid{grid-template-columns:repeat(4,1fr);}
      .steps-grid{grid-template-columns:1fr;}
      .footer-grid{grid-template-columns:1fr 1fr;}
      .cta-inner{padding:48px 24px;border-radius:26px;}
      .sec{padding:72px 20px;}
      .stats-band{padding:56px 20px;}
      .stat-c{border-right:none;border-bottom:1px solid rgba(101,161,216,.1);padding:28px 16px;}
      .stat-c:last-child{border-bottom:none;}
    }

    @yield('styles')
  </style>

  @yield('head')
</head>
<body>

  <!-- Scroll Progress Bar -->
  <div id="progress"></div>

  <!-- Back to Top -->
  <a href="#top" id="btt" aria-label="Back to top">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M5 13l5-5 5 5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </a>

  <!-- ═══════ NAV ═══════ -->
  <header class="nav" id="nav">
    <a href="{{ route('home') }}" class="nav-brand">
      <img src="{{ asset('logo.png') }}" alt="Schedara">
    </a>

    <nav>
      <ul class="nav-links">
        <li><a href="#features">Features</a></li>
        <li><a href="#how">How it works</a></li>
        <li><a href="#channels">Channels</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ul>
    </nav>

    <div class="nav-actions">
      @auth
        <a href="{{ route('dashboard') }}" class="btn-ghost">Dashboard</a>
      @else
        <a href="{{ route('auth.login') }}" class="btn-ghost">Sign in</a>
        <a href="{{ route('auth.login') }}" class="btn-nav">
          Start free
          <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M4 10h12M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      @endauth
    </div>

    <button class="hamburger" id="ham" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </header>

  <!-- Mobile Nav -->
  <nav class="mob-nav" id="mobNav">
    <ul class="mob-links">
      <li><a href="#features">Features</a></li>
      <li><a href="#how">How it works</a></li>
      <li><a href="#channels">Channels</a></li>
      <li><a href="#pricing">Pricing</a></li>
      <li><a href="#faq">FAQ</a></li>
    </ul>
    <div class="mob-btns">
      @auth
        <a href="{{ route('dashboard') }}" style="text-decoration:none;background:var(--brand);color:white;font-size:15px;font-weight:700;padding:14px 20px;border-radius:13px;text-align:center;">Go to Dashboard</a>
      @else
        <a href="{{ route('auth.login') }}" style="text-decoration:none;color:rgba(245,254,254,.7);font-size:15px;font-weight:600;padding:14px 20px;border-radius:13px;border:1px solid rgba(101,161,216,.2);text-align:center;">Sign in</a>
        <a href="{{ route('auth.login') }}" style="text-decoration:none;background:var(--brand);color:white;font-size:15px;font-weight:700;padding:14px 20px;border-radius:13px;text-align:center;">Start free trial</a>
      @endauth
    </div>
  </nav>

  <!-- ═══════ PAGE CONTENT ═══════ -->
  @yield('content')

  <!-- ═══════ FOOTER ═══════ -->
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-grid">
        <div>
          <div class="f-logo">
            <img src="{{ asset('logo.png') }}" alt="Schedara">
          </div>
          <p class="f-desc">Smart scheduling, elevated. Plan, publish, and measure across every channel — together.</p>
          <div class="f-socials">
            <a href="#" class="f-soc">𝕏</a>
            <a href="#" class="f-soc">in</a>
            <a href="#" class="f-soc">IG</a>
            <a href="#" class="f-soc">▶</a>
          </div>
        </div>

        <div>
          <div class="f-col-t">Product</div>
          <ul class="f-links">
            <li><a href="#">Calendar</a></li>
            <li><a href="#">Analytics</a></li>
            <li><a href="#">Channels</a></li>
            <li><a href="#">Inbox</a></li>
            <li><a href="#">AI Caption Studio</a></li>
          </ul>
        </div>

        <div>
          <div class="f-col-t">Resources</div>
          <ul class="f-links">
            <li><a href="#">Blog</a></li>
            <li><a href="#">Help center</a></li>
            <li><a href="#">API docs</a></li>
            <li><a href="#">Templates</a></li>
            <li><a href="#">Changelog</a></li>
          </ul>
        </div>

        <div>
          <div class="f-col-t">Company</div>
          <ul class="f-links">
            <li><a href="#">About</a></li>
            <li><a href="#">Customers</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Press kit</a></li>
            <li><a href="#">Contact</a></li>
          </ul>
        </div>

        <div>
          <div class="f-col-t">Legal</div>
          <ul class="f-links">
            <li><a href="#">Privacy</a></li>
            <li><a href="#">Terms</a></li>
            <li><a href="#">Security</a></li>
            <li><a href="#">DPA</a></li>
            <li><a href="#">Cookies</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <div>© {{ date('Y') }} Schedara, Inc. All rights reserved.</div>
        <div style="display:flex;align-items:center;gap:14px;">
          <span><span class="status-dot"></span>All systems normal</span>
          <span>·</span>
          <a href="#" style="color:rgba(245,254,254,.5);text-decoration:none;">status.schedara.com</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- ═══════ SHARED SCRIPTS ═══════ -->
  <script>
    /* Scroll progress + nav + back-to-top */
    const nav  = document.getElementById('nav');
    const btt  = document.getElementById('btt');
    const prog = document.getElementById('progress');
    window.addEventListener('scroll', () => {
      const s = window.scrollY;
      nav.classList.toggle('scrolled', s > 30);
      btt.classList.toggle('show', s > 400);
      const h = document.documentElement;
      prog.style.width = (s / (h.scrollHeight - h.clientHeight) * 100) + '%';
    }, { passive: true });

    /* Hamburger */
    const ham    = document.getElementById('ham');
    const mobNav = document.getElementById('mobNav');
    ham.addEventListener('click', () => mobNav.classList.toggle('open'));
    mobNav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => mobNav.classList.remove('open')));

    /* Scroll reveal (IntersectionObserver) */
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal, .reveal-l, .reveal-r, .reveal-s').forEach(el => io.observe(el));
  </script>

  @yield('scripts')

</body>
</html>
