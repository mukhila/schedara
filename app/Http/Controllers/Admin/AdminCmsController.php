<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Services\Admin\CmsPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCmsController extends Controller
{
    public function __construct(private CmsPageService $cms) {}

    public function index(Request $request): View
    {
        $pages = $this->cms->paginate($request->only(['search', 'status', 'page_type']));

        return view('admin.cms.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.cms.edit', ['page' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $this->cms->create($data, auth()->user());

        return redirect()->route('admin.cms.index')->with('success', 'Page created.');
    }

    public function edit(CmsPage $cmsPage): View
    {
        return view('admin.cms.edit', ['page' => $cmsPage]);
    }

    public function update(Request $request, CmsPage $cmsPage): RedirectResponse
    {
        $data = $this->validated($request, $cmsPage);

        $this->cms->update($cmsPage, $data);

        return redirect()->route('admin.cms.index')->with('success', 'Page updated.');
    }

    public function publish(CmsPage $cmsPage): RedirectResponse
    {
        $this->cms->publish($cmsPage);

        return back()->with('success', 'Page published.');
    }

    public function unpublish(CmsPage $cmsPage): RedirectResponse
    {
        $this->cms->unpublish($cmsPage);

        return back()->with('success', 'Page unpublished.');
    }

    public function destroy(CmsPage $cmsPage): RedirectResponse
    {
        $this->cms->delete($cmsPage);

        return redirect()->route('admin.cms.index')->with('success', 'Page deleted.');
    }

    private function validated(Request $request, ?CmsPage $page = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:cms_pages,slug';
        if ($page) $slugRule .= ",{$page->id}";

        return $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => $slugRule,
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image'         => 'nullable|string|max:500',
            'page_type'        => 'required|in:page,post,legal,faq',
            'status'           => 'required|in:draft,published',
            'sort_order'       => 'nullable|integer',
        ]);
    }
}
