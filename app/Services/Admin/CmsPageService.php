<?php

namespace App\Services\Admin;

use App\Models\AdminActivityLog;
use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CmsPageService
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = CmsPage::with('author');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('slug', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['page_type'])) {
            $query->where('page_type', $filters['page_type']);
        }

        return $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    public function create(array $data, User $author): CmsPage
    {
        $page = CmsPage::create([
            ...$data,
            'created_by' => $author->id,
            'slug'       => $data['slug'] ?? Str::slug($data['title']),
            'status'     => $data['status'] ?? 'draft',
        ]);

        AdminActivityLog::record('create', 'cms', "Created CMS page '{$page->title}'", $page);

        return $page;
    }

    public function update(CmsPage $page, array $data): CmsPage
    {
        if (isset($data['title']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $page->update($data);

        AdminActivityLog::record('update', 'cms', "Updated CMS page '{$page->title}'", $page);

        return $page->fresh();
    }

    public function publish(CmsPage $page): void
    {
        $page->publish();

        AdminActivityLog::record('publish', 'cms', "Published CMS page '{$page->title}'", $page);
    }

    public function unpublish(CmsPage $page): void
    {
        $page->unpublish();

        AdminActivityLog::record('unpublish', 'cms', "Unpublished CMS page '{$page->title}'", $page);
    }

    public function delete(CmsPage $page): void
    {
        AdminActivityLog::record('delete', 'cms', "Deleted CMS page '{$page->title}'", $page);

        $page->delete();
    }
}
