<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavItem;
use Illuminate\Http\Request;

class NavbarController extends Controller
{
    public function index()
    {
        $navItems = NavItem::orderBy('position')->get();

        return view('admin.navbar.index', compact('navItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'        => 'required|string|max:100',
            'url'          => 'required|string|max:255',
            'icon'         => 'nullable|string|max:100',
            'is_active'    => 'boolean',
            'open_new_tab' => 'boolean',
        ]);

        $data['position'] = NavItem::max('position') + 1;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['open_new_tab'] = $request->boolean('open_new_tab', false);

        NavItem::create($data);

        return back()->with('success', 'Élément de navigation ajouté.');
    }

    public function update(Request $request, NavItem $navItem)
    {
        $data = $request->validate([
            'label'        => 'required|string|max:100',
            'url'          => 'required|string|max:255',
            'icon'         => 'nullable|string|max:100',
            'is_active'    => 'boolean',
            'open_new_tab' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['open_new_tab'] = $request->boolean('open_new_tab', false);

        $navItem->update($data);

        return back()->with('success', 'Élément de navigation modifié.');
    }

    public function destroy(NavItem $navItem)
    {
        $navItem->delete();

        return back()->with('success', 'Élément de navigation supprimé.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->input('order') as $position => $id) {
            NavItem::where('id', $id)->update(['position' => $position]);
        }

        return response()->json(['success' => true]);
    }
}
