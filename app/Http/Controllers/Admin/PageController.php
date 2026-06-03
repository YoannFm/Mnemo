<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(25);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'slug'         => 'required|string|max:200|unique:pages,slug',
            'content'      => 'required|string',
            'is_published' => 'sometimes|boolean',
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $data['is_published'] = $request->boolean('is_published');
        $page = Page::create($data);
        LogHelper::log('created_page', 'page', $page->id, ['title' => $page->title]);
        return redirect()->route('admin.pages.index')->with('success', 'Page créée.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'slug'         => 'required|string|max:200|unique:pages,slug,' . $page->id,
            'content'      => 'required|string',
            'is_published' => 'sometimes|boolean',
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $data['is_published'] = $request->boolean('is_published');
        $page->update($data);
        LogHelper::log('updated_page', 'page', $page->id, ['title' => $page->title]);
        return redirect()->route('admin.pages.index')->with('success', 'Page mise à jour.');
    }

    public function destroy(Page $page)
    {
        LogHelper::log('deleted_page', 'page', $page->id, ['title' => $page->title]);
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page supprimée.');
    }
}
