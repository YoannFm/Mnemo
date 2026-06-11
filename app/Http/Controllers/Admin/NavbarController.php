<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\NavItem;
use Illuminate\Http\Request;

class NavbarController extends Controller
{
    public function index()
    {
        $navItems = NavItem::whereNull('parent_id')->orderBy('position')->with('children')->get();

        return view('admin.navbar.index', compact('navItems'));
    }

    public function create()
    {
        $pages   = \App\Models\Page::orderBy('title')->get();
        $posts   = \App\Models\Post::orderBy('title')->get();
        $roles   = \App\Models\Role::orderBy('name')->get();
        $navItem = new NavItem();
        $types   = ['link' => 'Lien', 'page' => 'Page', 'post' => 'Article', 'dropdown' => 'Menu deroulant'];

        return view('admin.navbar.create', compact('pages', 'posts', 'roles', 'navItem', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'     => 'required|string|max:100',
            'icon'      => 'nullable|string|max:100',
            'type'      => 'required|in:link,page,post,dropdown',
            'value'     => 'nullable|string|max:500',
            'new_tab'   => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['new_tab']   = $request->boolean('new_tab');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['position']  = NavItem::whereNull('parent_id')->max('position') + 1;

        $navItem = NavItem::create($data);

        LogHelper::log('created_nav_item', 'navbar', $navItem->id, ['label' => $navItem->label]);

        return redirect()->route('admin.navbar.index')->with('success', 'Element ajoute.');
    }

    public function edit(NavItem $navItem)
    {
        $pages = \App\Models\Page::orderBy('title')->get();
        $posts = \App\Models\Post::orderBy('title')->get();
        $roles = \App\Models\Role::orderBy('name')->get();
        $types = ['link' => 'Lien', 'page' => 'Page', 'post' => 'Article', 'dropdown' => 'Menu deroulant'];

        return view('admin.navbar.edit', compact('navItem', 'pages', 'posts', 'roles', 'types'));
    }

    public function update(Request $request, NavItem $navItem)
    {
        $data = $request->validate([
            'label'     => 'required|string|max:100',
            'icon'      => 'nullable|string|max:100',
            'type'      => 'required|in:link,page,post,dropdown',
            'value'     => 'nullable|string|max:500',
            'new_tab'   => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['new_tab']   = $request->boolean('new_tab');
        $data['is_active'] = $request->boolean('is_active', true);

        $navItem->update($data);

        LogHelper::log('updated_nav_item', 'navbar', $navItem->id, ['label' => $navItem->label]);

        return redirect()->route('admin.navbar.index')->with('success', 'Element modifie.');
    }

    public function destroy(NavItem $navItem)
    {
        LogHelper::log('deleted_nav_item', 'navbar', $navItem->id, ['label' => $navItem->label], 'warning');

        $navItem->children()->delete();
        $navItem->delete();

        return redirect()->route('admin.navbar.index')->with('success', 'Element supprime.');
    }

    public function updateOrder(Request $request)
    {
        $order    = $request->input('order', []);
        $position = 0;
        $this->processOrder($order, null, $position);

        LogHelper::log('updated_nav_order', 'navbar', null);

        return response()->json(['success' => true]);
    }

    private function processOrder(array $items, ?int $parentId, int &$position): void
    {
        foreach ($items as $item) {
            NavItem::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'position'  => $position++,
            ]);

            if (!empty($item['children'])) {
                $childPos = 0;
                $this->processOrder($item['children'], (int) $item['id'], $childPos);
            }
        }
    }
}
