@extends('layouts.backend')

@section('title', 'Notification Templates')

@section('content')
<div x-data="templateManager()" x-init="init()">

  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-xl font-bold text-ink">Notification Templates</h1>
      <p class="text-sm text-ink/50 mt-0.5">Customize messages sent across channels</p>
    </div>
    <button @click="openCreate()"
            class="px-4 py-2 bg-ink text-white text-sm font-bold rounded-lg hover:bg-brand-800 transition-colors flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
      New Template
    </button>
  </div>

  {{-- Channel filter --}}
  <div class="flex gap-2 mb-5 flex-wrap">
    @foreach(['all','email','push','whatsapp','slack','sms'] as $ch)
    <button @click="filterChannel = '{{ $ch }}'; loadTemplates()"
            :class="filterChannel === '{{ $ch }}' ? 'bg-ink text-white' : 'bg-white text-ink/60 hover:text-ink'"
            class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-colors capitalize"
            style="border-color:var(--line)">
      {{ ucfirst($ch) }}
    </button>
    @endforeach
  </div>

  {{-- Template list --}}
  <div class="card overflow-hidden">
    <template x-if="templates.length === 0 && !loading">
      <div class="text-center py-16 text-sm text-ink/40">No templates yet. Create one to get started.</div>
    </template>

    <div x-show="loading" class="text-center py-12 text-sm text-ink/40">Loading…</div>

    <div x-show="!loading && templates.length > 0" class="divide-y" style="border-color:var(--line)">
      <template x-for="tpl in templates" :key="tpl.uuid">
        <div class="flex items-center gap-4 px-5 py-4 hover:bg-paper transition-colors">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-[11px] font-bold text-white"
               :class="channelColor(tpl.channel)">
            <span x-text="tpl.channel.charAt(0).toUpperCase()"></span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm text-ink truncate" x-text="tpl.template_name"></div>
            <div class="text-xs text-ink/40 mt-0.5">
              <span x-text="tpl.type"></span>
              · <span x-text="tpl.channel" class="uppercase"></span>
            </div>
          </div>
          <span class="pill" :class="tpl.status === 'active' ? 'pill-mint' : 'pill-coral'" x-text="tpl.status"></span>
          <div class="flex gap-2">
            <button @click="openEdit(tpl)"
                    class="text-xs font-bold text-brand-600 hover:text-brand-800 px-2 py-1">Edit</button>
            <button @click="deleteTemplate(tpl.uuid)"
                    class="text-xs font-bold text-coral/70 hover:text-coral px-2 py-1">Delete</button>
          </div>
        </div>
      </template>
    </div>
  </div>

  {{-- Create/Edit Modal --}}
  <div x-show="modalOpen" x-transition.opacity
       class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" style="display:none">
    <div @click.stop
         class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden"
         style="max-height:90vh;overflow-y:auto">
      <div class="px-6 py-5 border-b flex items-center justify-between" style="border-color:var(--line)">
        <h2 class="font-bold text-ink" x-text="editTarget ? 'Edit Template' : 'New Template'"></h2>
        <button @click="modalOpen=false" class="text-ink/30 hover:text-ink transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
      </div>
      <form @submit.prevent="save" class="p-6 space-y-4">
        <div>
          <label class="label">Template Name</label>
          <input x-model="form.template_name" required class="field" placeholder="Welcome Email">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Type</label>
            <input x-model="form.type" required class="field" placeholder="user.welcome">
          </div>
          <div>
            <label class="label">Channel</label>
            <select x-model="form.channel" class="field">
              <option value="email">Email</option>
              <option value="push">Push</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="slack">Slack</option>
              <option value="sms">SMS</option>
            </select>
          </div>
        </div>
        <div x-show="form.channel === 'email'">
          <label class="label">Subject</label>
          <input x-model="form.subject" class="field" placeholder="Welcome to {{workspace_name}}!">
        </div>
        <div>
          <label class="label">Message Template</label>
          <textarea x-model="form.message_template" required rows="5" class="field resize-none"
                    placeholder="Hi {{user_name}}, welcome to {{workspace_name}}!"></textarea>
          <p class="text-xs text-ink/40 mt-1">Use <code>{{'{{'}}variable{{'}}'}}</code> for dynamic values</p>
        </div>
        <div>
          <label class="label">Status</label>
          <select x-model="form.status" class="field">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="pt-2 flex justify-end gap-3">
          <button type="button" @click="modalOpen=false"
                  class="px-4 py-2 text-sm font-bold rounded-lg border hover:bg-paper transition-colors" style="border-color:var(--line)">
            Cancel
          </button>
          <button type="submit" :disabled="saving"
                  class="px-5 py-2 bg-ink text-white text-sm font-bold rounded-lg hover:bg-brand-800 transition-colors disabled:opacity-50">
            <span x-text="saving ? 'Saving…' : 'Save Template'"></span>
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@section('styles')
<style>
.label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:rgba(2,27,46,.4);margin-bottom:5px}
.field{width:100%;padding:8px 12px;font-size:13px;border:1px solid var(--line);border-radius:8px;outline:none;font-family:inherit;transition:border-color .15s}
.field:focus{border-color:#65a1d8}
</style>
@endsection

@section('scripts')
<script>
function templateManager() {
  return {
    templates:     [],
    loading:       false,
    modalOpen:     false,
    editTarget:    null,
    saving:        false,
    filterChannel: 'all',
    form: { template_name:'', type:'', channel:'email', subject:'', message_template:'', status:'active' },

    async init() { await this.loadTemplates(); },

    async loadTemplates() {
      this.loading = true;
      const url = '/api/notifications/templates' + (this.filterChannel !== 'all' ? '?channel=' + this.filterChannel : '');
      const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
      this.templates = await res.json();
      this.loading = false;
    },

    openCreate() {
      this.editTarget = null;
      this.form = { template_name:'', type:'', channel:'email', subject:'', message_template:'', status:'active' };
      this.modalOpen = true;
    },

    openEdit(tpl) {
      this.editTarget = tpl;
      this.form = { template_name: tpl.template_name, type: tpl.type, channel: tpl.channel,
                    subject: tpl.subject ?? '', message_template: tpl.message_template, status: tpl.status };
      this.modalOpen = true;
    },

    async save() {
      this.saving = true;
      const url    = this.editTarget ? '/api/notifications/templates/' + this.editTarget.uuid : '/api/notifications/templates';
      const method = this.editTarget ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                   'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify(this.form),
      });

      this.saving = false;
      if (res.ok) { this.modalOpen = false; await this.loadTemplates(); }
    },

    async deleteTemplate(uuid) {
      if (!confirm('Delete this template?')) return;
      await fetch('/api/notifications/templates/' + uuid, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
      });
      await this.loadTemplates();
    },

    channelColor(ch) {
      const map = { email:'bg-brand-600', push:'bg-mint', whatsapp:'bg-green-600', slack:'bg-purple-700', sms:'bg-gold' };
      return map[ch] ?? 'bg-ink';
    },
  };
}
</script>
@endsection
