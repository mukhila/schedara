@extends('admin.layouts.admin')

@section('title', 'Settings')
@section('heading', 'System Settings')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
    @csrf

    @forelse($grouped as $category => $settings)
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $category) }}</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($settings as $setting)
            <div class="px-5 py-4 flex items-center justify-between gap-6">
                <div class="flex-1">
                    <label for="settings_{{ $setting->setting_key }}" class="block text-sm font-medium text-gray-900">
                        {{ $setting->label ?? $setting->setting_key }}
                    </label>
                </div>
                <div class="w-64">
                    @if($setting->type === 'boolean')
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[{{ $setting->setting_key }}]" value="0">
                            <input type="checkbox" id="settings_{{ $setting->setting_key }}"
                                   name="settings[{{ $setting->setting_key }}]" value="1"
                                   {{ $setting->typedValue() ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-violet-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    @elseif($setting->type === 'secret')
                        <input type="password" id="settings_{{ $setting->setting_key }}"
                               name="settings[{{ $setting->setting_key }}]"
                               placeholder="Leave blank to keep current"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    @else
                        <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}"
                               id="settings_{{ $setting->setting_key }}"
                               name="settings[{{ $setting->setting_key }}]"
                               value="{{ old('settings.' . $setting->setting_key, $setting->setting_value) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
        No settings configured.
    </div>
    @endforelse

    @if($grouped->isNotEmpty())
    <div class="flex justify-end">
        <button type="submit" class="bg-violet-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
            Save Settings
        </button>
    </div>
    @endif
</form>
@endsection
