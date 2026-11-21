<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsPageController extends Controller
{
    public function index(Request $request)
    {
        $query = CmsPage::query();
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        $pages = $query->latest()->paginate(15)->withQueryString();

        return view('admin.cms.index', compact('pages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:page,news,rule',
            'content' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        $data['is_active'] = true;
        $page = CmsPage::create($data);

        AuditLog::record('CMS', 'Create Page', "Created page: {$page->title}");

        return back()->with('status', 'Page create ho gaya.');
    }

    public function update(Request $request, CmsPage $cmsPage)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:page,news,rule',
            'content' => 'nullable|string',
        ]);

        $cmsPage->update($data);

        AuditLog::record('CMS', 'Update Page', "Updated page #{$cmsPage->id}");

        return back()->with('status', 'Page update ho gaya.');
    }

    public function toggle(CmsPage $cmsPage)
    {
        $cmsPage->update(['is_active' => ! $cmsPage->is_active]);

        return back()->with('status', 'Page status update ho gaya.');
    }

    public function destroy(CmsPage $cmsPage)
    {
        $cmsPage->delete();

        AuditLog::record('CMS', 'Delete Page', "Deleted page #{$cmsPage->id}");

        return back()->with('status', 'Page delete ho gaya.');
    }
}
