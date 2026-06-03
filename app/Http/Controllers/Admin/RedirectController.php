<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function index()
    {
        $redirects = Redirect::latest()->paginate(25);
        return view('admin.redirects.index', compact('redirects'));
    }

    public function create()
    {
        return view('admin.redirects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source'     => 'required|string|max:500|unique:redirects,source',
            'target'     => 'required|string|max:500',
            'type'       => 'required|in:301,302',
            'is_enabled' => 'sometimes|boolean',
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');
        $redirect = Redirect::create($data);
        LogHelper::log('created_redirect', 'redirect', $redirect->id);
        return redirect()->route('admin.redirects.index')->with('success', 'Redirection créée.');
    }

    public function edit(Redirect $redirect)
    {
        return view('admin.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect)
    {
        $data = $request->validate([
            'source'     => 'required|string|max:500|unique:redirects,source,' . $redirect->id,
            'target'     => 'required|string|max:500',
            'type'       => 'required|in:301,302',
            'is_enabled' => 'sometimes|boolean',
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');
        $redirect->update($data);
        LogHelper::log('updated_redirect', 'redirect', $redirect->id);
        return redirect()->route('admin.redirects.index')->with('success', 'Redirection mise à jour.');
    }

    public function destroy(Redirect $redirect)
    {
        LogHelper::log('deleted_redirect', 'redirect', $redirect->id);
        $redirect->delete();
        return redirect()->route('admin.redirects.index')->with('success', 'Redirection supprimée.');
    }
}
