@extends('admin.layouts.admin')

@section('title', 'CMS Pages')
@section('heading', 'CMS Pages')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.cms.create') }}" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
        + New Page
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search pages…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
        </div>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Status</option>
            <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        <select name="page_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Types</option>
            @foreach(['page','post','legal','faq'] as $t)
                <option value="{{ $t }}" {{ request('page_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-violet-700">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Title</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Type</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Status</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Author</th>
                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase tracking-wide">Published</th>
                <th class="text-right px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pages as $page)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <p class="font-medium text-gray-900">{{ $page->title }}</p>
                    <p class="text-xs text-gray-400">/{{ $page->slug }}</p>
                </td>
                <td class="px-4 py-3.5">
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ ucfirst($page->page_type) }}</span>
                </td>
                <td class="px-4 py-3.5">
                    @if($page->isPublished())
                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Published</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Draft</span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $page->author?->name ?? '—' }}</td>
                <td class="px-4 py-3.5 text-gray-400 text-xs">{{ $page->published_at?->format('M d, Y') ?? '—' }}</td>
                <td class="px-5 py-3.5 text-right space-x-3">
                    <a href="{{ route('admin.cms.edit', $page) }}" class="text-sm text-violet-600 hover:underline">Edit</a>
                    @if($page->isPublished())
                    <form method="POST" action="{{ route('admin.cms.unpublish', $page) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-amber-600 hover:underline">Unpublish</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.cms.publish', $page) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-emerald-600 hover:underline">Publish</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.cms.destroy', $page) }}" class="inline"
                          onsubmit="return confirm('Delete this page?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No pages found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($pages->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $pages->links() }}</div>
    @endif
</div>
@endsection
