@extends('layouts.backend')
@section('page_title', 'Team')

@section('styles')
<style>
.members-table{width:100%;border-collapse:collapse}
.members-table th{text-align:left;padding:.625rem 1rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(2,27,46,.45);border-bottom:2px solid rgba(2,27,46,.08)}
.members-table td{padding:.875rem 1rem;border-bottom:1px solid rgba(2,27,46,.06);vertical-align:middle}
.members-table tr:last-child td{border-bottom:0}
.avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#fff;flex-shrink:0}
.member-info{display:flex;align-items:center;gap:.75rem}
.member-name{font-weight:700;font-size:.9rem;color:#021b2e}
.member-email{font-size:.78rem;color:rgba(2,27,46,.45)}
.role-badge{font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:5px;white-space:nowrap}
.invite-section{background:#fff;border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;border:1px solid rgba(2,27,46,.08)}
.invite-title{font-size:1rem;font-weight:800;margin-bottom:1rem;color:#021b2e;display:flex;align-items:center;gap:.5rem}
.invite-form{display:grid;grid-template-columns:1fr 180px auto auto;gap:.75rem;align-items:end}
.form-label{font-size:.75rem;font-weight:700;color:rgba(2,27,46,.55);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.35rem;display:block}
.form-control{width:100%;padding:.625rem .875rem;border:1px solid rgba(2,27,46,.15);border-radius:9px;font-family:inherit;font-size:.875rem;color:#021b2e;outline:none;transition:border-color .2s,box-shadow .2s}
.form-control:focus{border-color:#65a1d8;box-shadow:0 0 0 3px rgba(101,161,216,.15)}
select.form-control{cursor:pointer}
.btn-invite{background:#65a1d8;color:#fff;font-weight:700;padding:.625rem 1.25rem;border-radius:9px;border:none;cursor:pointer;font-family:inherit;font-size:.875rem;white-space:nowrap;transition:.2s}
.btn-invite:hover{background:#4a8ccc}
.btn-sm{padding:.35rem .75rem;border-radius:7px;font-size:.78rem;font-weight:700;cursor:pointer;font-family:inherit;border:none;transition:.2s}
.btn-danger{background:rgba(239,68,68,.08);color:#dc2626;border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:rgba(239,68,68,.15)}
.btn-ghost{background:transparent;color:rgba(2,27,46,.45);border:1px solid rgba(2,27,46,.12)}
.btn-ghost:hover{background:rgba(2,27,46,.04)}
.section-card{background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);overflow:hidden;margin-bottom:1.5rem}
.section-header{padding:1rem 1.25rem;border-bottom:1px solid rgba(2,27,46,.07);display:flex;align-items:center;justify-content:space-between}
.section-title{font-size:.9rem;font-weight:800;color:#021b2e}
.badge-count{background:rgba(101,161,216,.12);color:#65a1d8;font-size:.75rem;font-weight:700;padding:.1rem .45rem;border-radius:5px}
.pending-row{background:rgba(245,158,11,.03)}
.pending-badge{background:rgba(245,158,11,.1);color:#d97706;border:1px solid rgba(245,158,11,.2);font-size:.72rem;font-weight:700;padding:.15rem .45rem;border-radius:5px}
.role-select{padding:.3rem .5rem;border:1px solid rgba(2,27,46,.15);border-radius:7px;font-family:inherit;font-size:.78rem;color:#021b2e;cursor:pointer;background:#fff}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:#15803d}
.flash-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#dc2626}
@media(max-width:768px){.invite-form{grid-template-columns:1fr}}
</style>
@endsection

@section('content')

@if(session('success'))
  <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="flash flash-error">{{ $errors->first() }}</div>
@endif

{{-- ── Invite Section ── --}}
@can('team.invite')
<div class="invite-section">
  <div class="invite-title">
    <svg width="16" height="16" fill="none" stroke="#65a1d8" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
    Invite a team member
  </div>
  <form method="POST" action="{{ route('team.invite') }}" class="invite-form">
    @csrf
    <div>
      <label class="form-label">Email address</label>
      <input type="email" name="email" class="form-control" placeholder="colleague@company.com" required>
    </div>
    <div>
      <label class="form-label">Role</label>
      <select name="role" class="form-control">
        @foreach($assignableRoles as $role)
          <option value="{{ $role->value }}">{{ $role->label() }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">Message (optional)</label>
      <input type="text" name="message" class="form-control" placeholder="Join our team!">
    </div>
    <div style="display:flex;align-items:flex-end">
      <button type="submit" class="btn-invite">Send invite</button>
    </div>
  </form>
</div>
@endcan

{{-- ── Members ── --}}
<div class="section-card">
  <div class="section-header">
    <span class="section-title">Members</span>
    <span class="badge-count">{{ $members->count() }}</span>
  </div>
  <table class="members-table">
    <thead>
      <tr>
        <th>Member</th>
        <th>Role</th>
        <th>Joined</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($members as $membership)
        @php
          $role   = $membership->roleEnum();
          $isMe   = $membership->user_id === auth()->id();
          $initial = strtoupper(mb_substr($membership->user->name, 0, 1));
          $avatarBg = ['#65a1d8','#8b5cf6','#10b981','#f59e0b','#ef4444'][$membership->user_id % 5];
        @endphp
        <tr>
          <td>
            <div class="member-info">
              <div class="avatar" style="background:{{ $avatarBg }}">{{ $initial }}</div>
              <div>
                <div class="member-name">
                  {{ $membership->user->name }}
                  @if($isMe) <span style="font-size:.7rem;color:rgba(2,27,46,.4);font-weight:400">(you)</span> @endif
                </div>
                <div class="member-email">{{ $membership->user->email }}</div>
              </div>
            </div>
          </td>
          <td>
            @can('team.manage')
              @if(!$isMe && !$membership->isOwner())
                <form method="POST" action="{{ route('team.update-role', $membership->user_id) }}" style="display:inline">
                  @csrf @method('PUT')
                  <select name="role" class="role-select" onchange="this.form.submit()">
                    @foreach($assignableRoles as $r)
                      <option value="{{ $r->value }}" {{ $r->value === $membership->role ? 'selected' : '' }}>
                        {{ $r->label() }}
                      </option>
                    @endforeach
                  </select>
                </form>
              @else
                <span class="role-badge" style="background:{{ $role->badgeColor() }}18;color:{{ $role->badgeColor() }};border:1px solid {{ $role->badgeColor() }}33">
                  {{ $role->label() }}
                </span>
              @endif
            @else
              <span class="role-badge" style="background:{{ $role->badgeColor() }}18;color:{{ $role->badgeColor() }};border:1px solid {{ $role->badgeColor() }}33">
                {{ $role->label() }}
              </span>
            @endcan
          </td>
          <td style="color:rgba(2,27,46,.45);font-size:.82rem">
            {{ $membership->joined_at?->format('M j, Y') ?? '—' }}
          </td>
          <td style="text-align:right">
            @can('team.remove')
              @if(!$isMe && !$membership->isOwner())
                <form method="POST" action="{{ route('team.remove', $membership->user_id) }}" style="display:inline"
                      onsubmit="return confirm('Remove {{ $membership->user->name }} from the workspace?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-sm btn-danger">Remove</button>
                </form>
              @endif
            @endcan
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- ── Pending Invitations ── --}}
@if($invitations->isNotEmpty())
<div class="section-card">
  <div class="section-header">
    <span class="section-title">Pending invitations</span>
    <span class="badge-count">{{ $invitations->count() }}</span>
  </div>
  <table class="members-table">
    <thead>
      <tr>
        <th>Email</th>
        <th>Role</th>
        <th>Invited by</th>
        <th>Expires</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($invitations as $inv)
        @php $invRole = $inv->roleEnum(); @endphp
        <tr class="pending-row">
          <td>
            <div style="font-weight:600;font-size:.875rem;color:#021b2e">{{ $inv->email }}</div>
          </td>
          <td>
            <span class="role-badge" style="background:{{ $invRole->badgeColor() }}18;color:{{ $invRole->badgeColor() }};border:1px solid {{ $invRole->badgeColor() }}33">
              {{ $invRole->label() }}
            </span>
          </td>
          <td style="font-size:.82rem;color:rgba(2,27,46,.45)">{{ $inv->inviter->name }}</td>
          <td style="font-size:.82rem;color:rgba(2,27,46,.45)">{{ $inv->expires_at->diffForHumans() }}</td>
          <td style="text-align:right">
            @can('team.invite')
              <div style="display:flex;gap:.5rem;justify-content:flex-end">
                <form method="POST" action="{{ route('team.invitations.resend', $inv->id) }}">
                  @csrf
                  <button type="submit" class="btn-sm btn-ghost">Resend</button>
                </form>
                <form method="POST" action="{{ route('team.invitations.cancel', $inv->id) }}"
                      onsubmit="return confirm('Cancel this invitation?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-sm btn-danger">Cancel</button>
                </form>
              </div>
            @endcan
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- ── Role legend ── --}}
<div style="background:#fff;border-radius:14px;border:1px solid rgba(2,27,46,.08);padding:1.25rem">
  <div class="section-title" style="margin-bottom:1rem">Role permissions</div>
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:.78rem">
      <thead>
        <tr>
          <th style="text-align:left;padding:.5rem .75rem;color:rgba(2,27,46,.45);font-weight:700;border-bottom:1px solid rgba(2,27,46,.08)">Permission</th>
          @foreach(\App\Enums\TenantRole::cases() as $r)
            <th style="padding:.5rem .75rem;text-align:center;color:{{ $r->badgeColor() }};font-weight:700;border-bottom:1px solid rgba(2,27,46,.08)">{{ $r->label() }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach(\App\Enums\TenantPermission::cases() as $perm)
          <tr>
            <td style="padding:.5rem .75rem;color:rgba(2,27,46,.7);border-bottom:1px solid rgba(2,27,46,.04)">{{ $perm->label() }}</td>
            @foreach(\App\Enums\TenantRole::cases() as $r)
              <td style="text-align:center;padding:.5rem .75rem;border-bottom:1px solid rgba(2,27,46,.04)">
                @if($r->can($perm))
                  <span style="color:#10b981;font-size:1rem">✓</span>
                @else
                  <span style="color:rgba(2,27,46,.15);font-size:.85rem">—</span>
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection
