@extends('layouts.frontend')

@section('title', 'Schedara — Smart Scheduling Elevated')
@section('meta_description', 'Plan, publish, and measure social campaigns across 8 networks with an AI co-pilot, shared inbox, and analytics that actually answer the question.')

@section('content')

  {{-- ═══════ HERO ═══════ --}}
  <section class="hero" id="top">
    <div class="hero-grid"></div>
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <div class="orb orb3"></div>
    <div class="particles" id="particles"></div>

    <div class="hero-content">

      <div class="hero-badge">
        <span class="badge-chip">New</span>
        <span class="badge-text">AI Caption Studio — 30 days of posts in 2 minutes</span>
      </div>

      <h1 class="hero-h1">
        <span class="line">
          <span class="word" style="animation-delay:.4s">Smart</span><span class="space"></span>
          <span class="word" style="animation-delay:.52s">scheduling,</span>
        </span>
        <br>
        <span class="line">
          <span class="word hl" style="animation-delay:.68s">elevated.</span>
        </span>
      </h1>

      <div class="hero-sub">
        <span id="typed"></span><span class="cursor" id="cur"></span>
      </div>

      <div class="hero-ctas">
        <a href="{{ route('auth.login') }}" class="btn-hero btn-hero-p">
          Start 14-day free trial
          <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M4 10h12M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
        <a href="#features" class="btn-hero btn-hero-s">
          <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M6 4l8 6-8 6V4z"/></svg>
          See how it works
        </a>
      </div>

      <div class="hero-proof">
        <div class="av-stack">
          <div class="av">A</div>
          <div class="av" style="background:linear-gradient(135deg,#65a1d8,#021b2e)">M</div>
          <div class="av" style="background:linear-gradient(135deg,#8bb4dc,#4a8ccc)">J</div>
          <div class="av" style="background:linear-gradient(135deg,#021b2e,#65a1d8)">P</div>
          <div class="av" style="background:rgba(255,255,255,.1);color:rgba(245,254,254,.7);font-size:10px;">+8k</div>
        </div>
        <div>
          <div class="proof-stars">★★★★★</div>
          <div class="proof-t">4.9 · 2,400+ reviews</div>
        </div>
        <div class="proof-t">No credit card · Cancel anytime</div>
      </div>
    </div>

    {{-- Floating info cards --}}
    <div class="hero-cards">
      <div class="fcard fcard-eng">
        <div class="fc-label">Engagement</div>
        <div class="fc-value">142,890</div>
        <div class="fc-sub">This month</div>
        <span class="fc-badge badge-green">+38.2%</span>
      </div>
      <div class="fcard fcard-ai">
        <div class="fc-label">AI Generated</div>
        <div class="fc-value">30</div>
        <div class="fc-sub">posts drafted</div>
        <span class="fc-badge badge-brand">2 min</span>
      </div>
      <div class="fcard fcard-sched">
        <div class="fc-label">Status</div>
        <div class="fc-value" style="font-size:14px;font-weight:600;margin-top:4px;">
          <span class="live-dot"></span>Post live now
        </div>
        <div class="fc-sub">Thu, Apr 23 · 4 channels</div>
        <span class="fc-badge badge-green">Scheduled ✓</span>
      </div>
    </div>
  </section>

  {{-- Wave: hero → marquee --}}
  <div class="wave-wrap" style="background:var(--ink);">
    <svg viewBox="0 0 1440 90" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:90px;">
      <path d="M0,0 C240,80 480,0 720,50 C960,100 1200,20 1440,60 L1440,0 Z" fill="#021b2e"/>
      <path d="M0,0 L1440,0 L1440,90 C1200,60 960,100 720,70 C480,40 240,90 0,70 Z" fill="white"/>
    </svg>
  </div>

  {{-- ═══════ MARQUEE ═══════ --}}
  <section class="marquee-sec">
    <p class="marquee-label">Trusted by 12,000+ teams across 60 countries</p>
    <div class="marquee-wrap">
      <div class="marquee-track">
        <div class="marquee-inner">
          <span class="marquee-item">NorthSails</span>
          <span class="marquee-item">◆ Vesper</span>
          <span class="marquee-item">LOOMWORKS</span>
          <span class="marquee-item" style="font-style:italic">canvasly.</span>
          <span class="marquee-item">FRAMEHOUSE</span>
          <span class="marquee-item">● Halcyon</span>
          <span class="marquee-item">kettle &amp; co.</span>
          <span class="marquee-item">PIVOTLAB</span>
          <span class="marquee-item">Arcwave</span>
          <span class="marquee-item">◇ Fenix&nbsp;Co</span>
        </div>
        <div class="marquee-inner" aria-hidden="true">
          <span class="marquee-item">NorthSails</span>
          <span class="marquee-item">◆ Vesper</span>
          <span class="marquee-item">LOOMWORKS</span>
          <span class="marquee-item" style="font-style:italic">canvasly.</span>
          <span class="marquee-item">FRAMEHOUSE</span>
          <span class="marquee-item">● Halcyon</span>
          <span class="marquee-item">kettle &amp; co.</span>
          <span class="marquee-item">PIVOTLAB</span>
          <span class="marquee-item">Arcwave</span>
          <span class="marquee-item">◇ Fenix&nbsp;Co</span>
        </div>
      </div>
    </div>
  </section>

  {{-- ═══════ FEATURES ═══════ --}}
  <section class="sec" id="features" style="background:var(--paper);">
    <div class="sec-c">
      <div class="reveal">
        <span class="sec-eyebrow">Everything in one place</span>
        <h2 class="sec-h2">The social stack —<br>without the tab graveyard.</h2>
        <p class="sec-p">From the first draft to the post-mortem report. Schedara replaces six tools and a spreadsheet.</p>
      </div>

      <div class="bento">
        {{-- Calendar — wide --}}
        <div class="bc bc4 reveal d1">
          <span class="ftag ftag-b">Plan</span>
          <h3 class="fh3">Universal content calendar</h3>
          <p class="fd">Drag, drop, recycle. Every channel, every team, every campaign in one timeline — color-coded and conflict-aware.</p>
          <div class="mini-cal">
            <div class="mch">
              <span class="mo">April 2026</span>
              <div class="mch-nav">
                <button>‹</button>
                <button>›</button>
              </div>
            </div>
            <div class="cal-g">
              <div class="cal-dh">M</div><div class="cal-dh">T</div><div class="cal-dh">W</div>
              <div class="cal-dh">T</div><div class="cal-dh">F</div><div class="cal-dh">S</div><div class="cal-dh">S</div>
              <div class="cal-d">20</div>
              <div class="cal-d">21<div class="cdot" style="width:75%;background:var(--brand-l)"></div></div>
              <div class="cal-d">22
                <div class="cdot" style="width:80%;background:var(--brand)"></div>
                <div class="cdot" style="width:50%;background:var(--ink);margin-top:2px;"></div>
              </div>
              <div class="cal-d hi">
                <span class="dn">23</span>
                <div class="cdot" style="width:100%;background:var(--brand)"></div>
                <div class="cdot" style="width:65%;background:var(--mint);margin-top:2px;"></div>
                <div class="cdot" style="width:45%;background:var(--brand-d);margin-top:2px;"></div>
              </div>
              <div class="cal-d">24<div class="cdot" style="width:55%;background:var(--brand-l)"></div></div>
              <div class="cal-d" style="color:rgba(2,27,46,.2)">25</div>
              <div class="cal-d">26<div class="cdot" style="width:70%;background:var(--brand)"></div></div>
            </div>
          </div>
        </div>

        {{-- AI Captions --}}
        <div class="bc bc2 bc-dark reveal d2">
          <span class="ftag ftag-d">AI</span>
          <h3 class="fh3">Caption Studio</h3>
          <p class="fd fd-w">Generate on-brand captions in your voice — across 12 languages and every channel's tone.</p>
          <div class="ai-box">
            <div class="ai-lbl">prompt: spring jacket / playful / IG</div>
            <div class="ai-out">→ Layer up, lighten up ☀️ Our spring jacket is here — and it's the softest thing you'll wear all season.</div>
          </div>
        </div>

        {{-- Inbox --}}
        <div class="bc bc2 reveal d3">
          <span class="ftag ftag-g">Engage</span>
          <h3 class="fh3">Unified inbox</h3>
          <p class="fd">Every DM, mention, and comment in one queue. Assign, snooze, auto-reply.</p>
          <div class="inbox-list">
            <div class="ii">
              <div class="ii-av ig-av">IG</div>
              <div class="ii-body">
                <div class="ii-name">@maya.cph</div>
                <div class="ii-msg">Loved the new drop! Do you ship to Denmark? 🇩🇰</div>
              </div>
              <span class="ii-tag">New</span>
            </div>
            <div class="ii">
              <div class="ii-av tw-av">𝕏</div>
              <div class="ii-body">
                <div class="ii-name">@jordan_b</div>
                <div class="ii-msg">Where can I find the linen pant in size M?</div>
              </div>
            </div>
          </div>
        </div>

        {{-- Analytics --}}
        <div class="bc bc3 reveal d4">
          <span class="ftag ftag-b">Measure</span>
          <h3 class="fh3">Reports that explain themselves</h3>
          <p class="fd">Auto-generated insights tell you what worked and why — not just what happened.</p>
          <div class="bar-chart">
            <div class="bar"></div><div class="bar"></div><div class="bar"></div>
            <div class="bar"></div><div class="bar"></div><div class="bar"></div>
            <div class="bar"></div>
          </div>
          <div class="bar-days">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
          </div>
        </div>

        {{-- Approvals --}}
        <div class="bc bc3 bc-ink reveal d5">
          <span class="ftag ftag-d">Collaborate</span>
          <h3 class="fh3">Approvals without the email chain</h3>
          <p class="fd fd-w">Comment, request changes, and lock approvals in-thread. Roles &amp; permissions built in.</p>
          <div class="ap-list">
            <div class="ap-item">
              <div class="ap-av"></div>
              <div class="ap-info">
                <div class="ap-name">Priya · Brand lead</div>
                <div class="ap-sub">Approved 3 posts</div>
              </div>
              <span class="ap-s s-ok">Approved</span>
            </div>
            <div class="ap-item">
              <div class="ap-av" style="background:linear-gradient(135deg,#8bb4dc,#4a8ccc)"></div>
              <div class="ap-info">
                <div class="ap-name">Mateo · Copy</div>
                <div class="ap-sub">"Tighten the headline?"</div>
              </div>
              <span class="ap-s s-rev">Review</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Wave: features → stats --}}
  <div class="wave-wrap" style="background:var(--paper);">
    <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;">
      <path d="M0,40 C360,80 720,0 1080,50 C1260,70 1380,30 1440,40 L1440,80 L0,80 Z" fill="#021b2e"/>
    </svg>
  </div>

  {{-- ═══════ STATS ═══════ --}}
  <section class="stats-band">
    <div class="stats-orb"></div>
    <div class="stats-grid">
      <div class="stat-c reveal d1">
        <div class="stat-n"><span class="counter acc" data-target="12000">0</span><span class="acc">+</span></div>
        <div class="stat-l">Marketing teams worldwide</div>
      </div>
      <div class="stat-c reveal d2">
        <div class="stat-n"><span class="counter" data-target="41">0</span><span style="color:var(--brand)">%</span></div>
        <div class="stat-l">Avg. follower growth in 90 days</div>
      </div>
      <div class="stat-c reveal d3">
        <div class="stat-n"><span class="counter" data-target="7">0</span><span style="color:var(--brand)"> hrs</span></div>
        <div class="stat-l">Saved per marketer per week</div>
      </div>
      <div class="stat-c reveal d4">
        <div class="stat-n"><span class="acc">8</span></div>
        <div class="stat-l">Native social networks</div>
      </div>
    </div>
  </section>

  {{-- Wave: stats → how it works --}}
  <div class="wave-wrap" style="background:#021b2e;">
    <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;">
      <path d="M0,40 C360,0 720,80 1080,30 C1260,10 1380,50 1440,40 L1440,80 L0,80 Z" fill="var(--paper)"/>
    </svg>
  </div>

  {{-- ═══════ HOW IT WORKS ═══════ --}}
  <section class="sec" id="how" style="background:var(--paper);">
    <div class="sec-c">
      <div style="text-align:center;" class="reveal">
        <span class="sec-eyebrow">How it works</span>
        <h2 class="sec-h2">Three steps to effortless social.</h2>
        <p class="sec-p" style="max-width:480px;margin:0 auto;">Set up in minutes. See results in days.</p>
      </div>

      <div class="steps-grid">
        <div class="step-card reveal d1">
          <div class="step-num">01</div>
          <div class="step-ico">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#65a1d8" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="4"/>
              <path d="M8 12l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="step-h">Connect your channels</h3>
          <p class="step-p">Link Instagram, LinkedIn, TikTok and 5 more in under 2 minutes — one click per network, no developer needed.</p>
          <div class="step-line"></div>
        </div>

        <div class="step-card reveal d2">
          <div class="step-num">02</div>
          <div class="step-ico">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#65a1d8" stroke-width="2">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="step-h">Draft with AI</h3>
          <p class="step-p">Type a brief. Schedara's AI writes captions in your brand voice, resizes for each platform, and suggests the best posting times.</p>
          <div class="step-line"></div>
        </div>

        <div class="step-card reveal d3">
          <div class="step-num">03</div>
          <div class="step-ico">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#65a1d8" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <path d="M22 4L12 14.01l-3-3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3 class="step-h">Schedule &amp; measure</h3>
          <p class="step-p">Publish at optimal times, watch live analytics roll in, and get AI-written insight reports your whole team will actually read.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ═══════ CHANNELS ═══════ --}}
  <section class="sec" id="channels" style="background:white;padding-top:80px;padding-bottom:80px;">
    <div class="sec-c">
      <div style="text-align:center;" class="reveal">
        <span class="sec-eyebrow">Integrations</span>
        <h2 class="sec-h2">Native publishing to every<br>channel that matters.</h2>
      </div>

      <div class="ch-grid">
        <a href="#" class="ch-card reveal d1"><div class="ch-ico c-ig">IG</div><span class="ch-name">Instagram</span></a>
        <a href="#" class="ch-card reveal d2"><div class="ch-ico c-fb">f</div><span class="ch-name">Facebook</span></a>
        <a href="#" class="ch-card reveal d3"><div class="ch-ico c-tw">𝕏</div><span class="ch-name">X / Twitter</span></a>
        <a href="#" class="ch-card reveal d4"><div class="ch-ico c-li">in</div><span class="ch-name">LinkedIn</span></a>
        <a href="#" class="ch-card reveal d1"><div class="ch-ico c-tt">TT</div><span class="ch-name">TikTok</span></a>
        <a href="#" class="ch-card reveal d2"><div class="ch-ico c-yt">▶</div><span class="ch-name">YouTube</span></a>
        <a href="#" class="ch-card reveal d3"><div class="ch-ico c-th">@</div><span class="ch-name">Threads</span></a>
        <a href="#" class="ch-card reveal d4"><div class="ch-ico c-pn">P</div><span class="ch-name">Pinterest</span></a>
      </div>
    </div>
  </section>

  {{-- Wave: channels → testimonial --}}
  <div class="wave-wrap" style="background:white;">
    <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;">
      <path d="M0,0 C480,80 960,0 1440,60 L1440,80 L0,80 Z" fill="var(--paper)"/>
    </svg>
  </div>

  {{-- ═══════ TESTIMONIAL ═══════ --}}
  <section class="sec" id="customers" style="background:var(--paper);">
    <div class="sec-c">
      <div class="reveal">
        <span class="sec-eyebrow">Customers</span>
        <h2 class="sec-h2">Loved by teams who ship<br>content every single day.</h2>
      </div>

      <div class="testi-grid">
        <div class="testi-main reveal-l">
          <div class="testi-orb"></div>
          <div class="quote-mk">"</div>
          <p class="quote-txt">"We cut our weekly social ops time from 22 hours to 6, retired three other tools, and grew engaged followers 41% in one quarter. Schedara is the only marketing tool the whole team actually opens daily."</p>
          <div class="quote-auth">
            <div class="quote-av"></div>
            <div>
              <div class="qa-name">Priya Anand</div>
              <div class="qa-role">Head of Brand · NorthSails Apparel</div>
            </div>
          </div>
        </div>

        <div class="testi-aside">
          <div class="ts-box reveal-r d1">
            <div class="ts-big" style="color:var(--brand-d);">12,000<span style="font-size:32px;">+</span></div>
            <div class="ts-desc">Marketing teams across 60 countries</div>
          </div>
          <div class="ts-box reveal-r d2">
            <div class="ts-big" style="color:var(--ink);">41<span style="font-size:32px;">%</span></div>
            <div class="ts-desc">Avg. follower growth in first 90 days</div>
          </div>
          <div class="ts-box reveal-r d3">
            <div class="ts-big" style="color:var(--brand-d);">7 <span style="font-size:32px;">hrs</span></div>
            <div class="ts-desc">Saved per marketer, per week</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ═══════ PRICING ═══════ --}}
  <section class="sec" id="pricing" style="background:white;">
    <div class="sec-c">
      <div style="text-align:center;" class="reveal">
        <span class="sec-eyebrow">Pricing</span>
        <h2 class="sec-h2">Pay for seats, not seats<br>&amp; channels &amp; reports.</h2>
        <p class="sec-p" style="max-width:500px;margin:0 auto;">No per-channel fees. No per-report add-ons. Cancel anytime.</p>
        <div class="pricing-toggle">
          <button class="tgl-btn" id="tglM">Monthly</button>
          <button class="tgl-btn active" id="tglY">
            Yearly
            <span style="display:inline-block;background:var(--mint);color:white;font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px;margin-left:6px;">−20%</span>
          </button>
        </div>
      </div>

      <div class="price-grid">
        {{-- Starter --}}
        <div class="pc reveal d1">
          <div class="pc-plan">Starter</div>
          <div class="pc-for">For solo creators</div>
          <div class="pc-price" id="p1">$19</div>
          <div class="pc-per" id="pp1">per month · billed annually</div>
          <a href="#" class="pc-btn pb-out">Start free</a>
          <ul class="pc-feats">
            <li><span class="chk">✓</span>100 scheduled posts/month</li>
            <li><span class="chk">✓</span>Calendar &amp; queue</li>
            <li><span class="chk">✓</span>30-day analytics</li>
            <li style="color:rgba(2,27,46,.35)">
              <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:16px;color:rgba(2,27,46,.2);">—</span>
              Team collaboration
            </li>
            <li style="color:rgba(2,27,46,.35)">
              <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:16px;color:rgba(2,27,46,.2);">—</span>
              AI Caption Studio
            </li>
          </ul>
        </div>

        {{-- Growth — featured --}}
        <div class="pc pc-feat reveal d2">
          <span class="pc-badge">Most popular</span>
          <div class="pc-plan" style="color:white;">Growth</div>
          <div class="pc-for pc-for-w">For marketing teams</div>
          <div class="pc-price" id="p2" style="color:white;">$59</div>
          <div class="pc-per pc-per-w" id="pp2">per user/mo · billed annually</div>
          <a href="#" class="pc-btn pb-w">Start free trial</a>
          <ul class="pc-feats wht">
            <li><span class="chk chk-w">✓</span>Unlimited scheduled posts</li>
            <li><span class="chk chk-w">✓</span>AI Caption Studio (500 gen/mo)</li>
            <li><span class="chk chk-w">✓</span>Unified inbox + auto-replies</li>
            <li><span class="chk chk-w">✓</span>Approvals &amp; team roles</li>
            <li><span class="chk chk-w">✓</span>12-month analytics + export</li>
          </ul>
        </div>

        {{-- Scale --}}
        <div class="pc reveal d3">
          <div class="pc-plan">Scale</div>
          <div class="pc-for">For agencies &amp; brands</div>
          <div class="pc-price" style="font-size:40px;margin-top:20px;">Custom</div>
          <div class="pc-per">Volume pricing · unlimited channels</div>
          <a href="#" class="pc-btn pb-out">Book a demo</a>
          <ul class="pc-feats">
            <li><span class="chk">✓</span>Everything in Growth</li>
            <li><span class="chk">✓</span>SSO &amp; SCIM, audit logs</li>
            <li><span class="chk">✓</span>Custom report builder + API</li>
            <li><span class="chk">✓</span>Dedicated CSM &amp; 24/7 chat</li>
            <li><span class="chk">✓</span>White-label client portals</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  {{-- ═══════ FAQ ═══════ --}}
  <section class="sec" id="faq" style="background:var(--paper);">
    <div class="sec-c">
      <div style="text-align:center;" class="reveal">
        <span class="sec-eyebrow">FAQ</span>
        <h2 class="sec-h2">Questions, answered.</h2>
      </div>

      <div class="faq-list">
        @foreach ([
          ['q' => 'Which networks does Schedara support?',
           'a' => 'Instagram, Facebook, X (Twitter), LinkedIn, TikTok, YouTube, Threads, and Pinterest — with native publishing, no browser extensions. Bluesky and Mastodon are on the public roadmap for Q3.'],
          ['q' => 'How does the AI Caption Studio work?',
           'a' => 'Train it once on your past top-performing posts and brand guidelines. Then ask it for a month of content in your voice — playful, technical, or anywhere in between. Every output is editable and version-controlled.'],
          ['q' => 'Can I import from Hootsuite or Buffer?',
           'a' => 'Yes — one-click CSV import preserves your queue, channels, and labels. Our migration team will white-glove the move for any team over 10 seats, at no cost.'],
          ['q' => 'Is my data secure?',
           'a' => 'SOC 2 Type II, GDPR, and ISO 27001 certified. Data encrypted at rest (AES-256) and in transit (TLS 1.3). We never train AI models on your content.'],
          ['q' => 'What happens after the free trial?',
           'a' => 'Day 15 your workspace downgrades to the free Starter tier — no charge, no lost data. Upgrade any time. We\'ll send a reminder on day 12, never a surprise charge.'],
        ] as $i => $item)
        <div class="faq-i {{ $i === 0 ? 'open' : '' }} reveal d{{ $i + 1 }}">
          <div class="faq-q">
            {{ $item['q'] }}
            <svg class="faq-chv" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5.5 7.5l4.5 4.5 4.5-4.5" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="faq-a">{{ $item['a'] }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ═══════ CTA ═══════ --}}
  <section class="cta-outer">
    <div class="cta-inner reveal-s">
      <div class="cta-o1"></div>
      <div class="cta-o2"></div>
      <div class="cta-grid-bg"></div>
      <div class="cta-rel">
        <h2 class="cta-h2">Your next post is<br>one click away.</h2>
        <p class="cta-sub">Start your 14-day trial of Growth. No credit card required. Cancel in two clicks.</p>
        <div class="cta-btns">
          <a href="{{ route('auth.login') }}" class="cta-btn-p">
            Start free trial
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M4 10h12M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
          <a href="#" class="cta-btn-s">Talk to sales</a>
        </div>
        <div class="cta-perks">
          <div class="cta-perk">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 10l4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            14-day free trial
          </div>
          <div class="cta-perk">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 10l4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            No credit card
          </div>
          <div class="cta-perk">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 10l4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Setup in 5 minutes
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection

@section('scripts')
<script>
  /* Particles */
  (function () {
    const wrap = document.getElementById('particles');
    for (let i = 0; i < 28; i++) {
      const p = document.createElement('div');
      p.className = 'p';
      const size = Math.random() * 3 + 1;
      p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}%;top:${Math.random()*100}%;opacity:${Math.random()*.6+.1};animation-duration:${Math.random()*12+8}s;animation-delay:${Math.random()*-15}s;`;
      wrap.appendChild(p);
    }
  })();

  /* Typewriter */
  const text = 'Plan, publish, and measure social campaigns across 8 networks — with an AI co-pilot, a shared inbox, and analytics that actually answer the question.';
  let ci = 0;
  const typed = document.getElementById('typed');
  function type() {
    if (ci < text.length) { typed.textContent += text[ci++]; setTimeout(type, 22); }
  }
  setTimeout(type, 1200);

  /* Animated counters */
  const cio = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target, target = +el.dataset.target;
      let cur = 0;
      const inc = target / 60;
      const t = setInterval(() => {
        cur = Math.min(cur + inc, target);
        el.textContent = target > 999 ? Math.round(cur).toLocaleString() : Math.round(cur);
        if (cur >= target) clearInterval(t);
      }, 1800 / 60);
      cio.unobserve(el);
    });
  }, { threshold: 0.3 });
  document.querySelectorAll('.counter').forEach(el => cio.observe(el));

  /* Pricing toggle */
  const prices = { monthly: ['$23', '$74'], yearly: ['$19', '$59'] };
  const tglM = document.getElementById('tglM');
  const tglY = document.getElementById('tglY');
  tglM.addEventListener('click', () => {
    tglM.classList.add('active'); tglY.classList.remove('active');
    document.getElementById('p1').textContent = prices.monthly[0];
    document.getElementById('p2').textContent = prices.monthly[1];
    document.getElementById('pp1').textContent = 'per month · billed monthly';
    document.getElementById('pp2').textContent = 'per user/mo · billed monthly';
  });
  tglY.addEventListener('click', () => {
    tglY.classList.add('active'); tglM.classList.remove('active');
    document.getElementById('p1').textContent = prices.yearly[0];
    document.getElementById('p2').textContent = prices.yearly[1];
    document.getElementById('pp1').textContent = 'per month · billed annually';
    document.getElementById('pp2').textContent = 'per user/mo · billed annually';
  });

  /* FAQ accordion */
  document.querySelectorAll('.faq-i').forEach(item => {
    item.querySelector('.faq-q').addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-i').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });
</script>
@endsection
