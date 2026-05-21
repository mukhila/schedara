@extends('admin.layouts.admin')

@section('title', 'Profile & Password')
@section('heading', 'Account Settings')
@section('subheading', 'Manage your profile and change your password')

@section('content')

<div class="max-w-3xl" x-data="{ tab: window.location.hash === '#password' ? 'password' : 'profile' }">

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 mb-6">
        <button @click="tab = 'profile'; history.replaceState(null,'',location.pathname)"
                :class="tab === 'profile' ? 'border-violet-600 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px">
            Profile Settings
        </button>
        <button @click="tab = 'password'; history.replaceState(null,'',location.pathname + '#password')"
                :class="tab === 'password' ? 'border-violet-600 text-violet-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm font-medium border-b-2 transition-colors -mb-px">
            Change Password
        </button>
    </div>

    {{-- Profile Tab --}}
    <div x-show="tab === 'profile'" id="profile">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-900">Personal Information</h3>
                <p class="text-sm text-gray-500 mt-0.5">Update your name, email, and timezone.</p>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="p-6 space-y-5">
                @csrf @method('PUT')

                @if($errors->any() && old('_section') === 'profile')
                <div class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif
                <input type="hidden" name="_section" value="profile">

                {{-- Avatar preview --}}
                <div class="flex items-center gap-4 pb-2">
                    <div class="w-16 h-16 rounded-full flex-shrink-0 overflow-hidden bg-violet-100 flex items-center justify-center">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-violet-700 font-bold text-xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Avatar URL</label>
                        <input type="url" name="avatar" value="{{ old('avatar', $user->avatar) }}"
                               placeholder="https://example.com/avatar.jpg"
                               class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Timezone</label>
                        <input type="text" name="timezone" value="{{ old('timezone', $user->timezone) }}"
                               placeholder="UTC"
                               class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    </div>
                    <div class="flex items-end">
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Account Created</label>
                            <p class="text-sm text-gray-700 border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50">
                                {{ $user->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="bg-violet-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-violet-700 transition-colors shadow-sm">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Password Tab --}}
    <div x-show="tab === 'password'" id="password" style="display:none">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-900">Change Password</h3>
                <p class="text-sm text-gray-500 mt-0.5">Use a strong password with mixed case and numbers.</p>
            </div>

            <form method="POST" action="{{ route('admin.password.update') }}" class="p-6 space-y-5">
                @csrf

                @if(session('error'))
                <div class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    {{ session('error') }}
                </div>
                @endif
                @if($errors->has('current_password') || $errors->has('password'))
                <div class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div x-data="{ showCurrent: false, showNew: false, showConfirm: false }" class="space-y-5">
                    {{-- Current Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password *</label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'" name="current_password" required
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password *</label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'" name="password" required
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <button type="button" @click="showNew = !showNew"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Minimum 8 characters, mixed case and numbers.</p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password *</label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="bg-violet-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-violet-700 transition-colors shadow-sm">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
