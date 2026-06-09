<?php

namespace Plugins\Tags\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Plugins\Tags\Models\Tag;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50|unique:tags,name']);
        $tag = Tag::create(['name' => $request->name, 'color' => $request->color ?? '#6b7280']);
        return response()->json($tag);
    }

    public function attach(Request $request, Module $module)
    {
        if ($module->owner_id !== Auth::id()) abort(403);
        $request->validate(['tag_id' => 'required|exists:tags,id']);
        $module->tags()->syncWithoutDetaching([$request->tag_id]);
        return back()->with('success', 'Tag ajouté.');
    }

    public function detach(Module $module, Tag $tag)
    {
        if ($module->owner_id !== Auth::id()) abort(403);
        $module->tags()->detach($tag->id);
        return back()->with('success', 'Tag retiré.');
    }
}
