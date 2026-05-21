@extends('admin.layouts.admin')

@section('title', $page ? 'Edit Page' : 'New Page')
@section('heading', $page ? 'Edit: ' . $page->title : 'New CMS Page')

@section('content')
<form method="POST" action="{{ $page ? route('admin.cms.update', $page) : route('admin.cms.store') }}"
      class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    @if($page) @method('PUT') @endif

    @if($errors->any())
    <div class="lg:col-span-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $page->title ?? '') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                <p class="text-xs text-gray-400 mt-1">Auto-generated from title if left blank.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                <textarea name="content" id="content" rows="20" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-violet-500">{{ old('content', $page->content ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                <textarea name="excerpt" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">{{ old('excerpt', $page->excerpt ?? '') }}</textarea>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h3 class="font-semibold text-gray-900">SEO</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="draft"     {{ old('status', $page->status ?? 'draft') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $page->status ?? 'draft') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="page_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    @foreach(['page','post','legal','faq'] as $t)
                        <option value="{{ $t }}" {{ old('page_type', $page->page_type ?? 'page') === $t ? 'selected' : '' }}>
                            {{ ucfirst($t) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-violet-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-violet-700">
                    {{ $page ? 'Save' : 'Create' }}
                </button>
                <a href="{{ route('admin.cms.index') }}" class="flex-1 text-center border border-gray-300 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
