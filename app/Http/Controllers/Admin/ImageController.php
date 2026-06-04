<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::latest()->paginate(25);
        return view('admin.images.index', compact('images'));
    }

    public function create()
    {
        return view('admin.images.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:200',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);
        $file = $request->file('image');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('images', $filename, 'public');
        $image = Image::create(['name' => $request->name, 'file' => $filename]);
        LogHelper::log('created_image', 'image', $image->id, ['name' => $image->name]);
        return redirect()->route('admin.images.index')->with('success', 'Image ajoutée.');
    }

    public function edit(Image $image)
    {
        return view('admin.images.edit', compact('image'));
    }

    public function update(Request $request, Image $image)
    {
        $request->validate([
            'name' => 'required|string|max:200',
        ]);
        $image->update(['name' => $request->name]);
        LogHelper::log('updated_image', 'image', $image->id, ['name' => $image->name]);
        return redirect()->route('admin.images.index')->with('success', 'Image mise à jour.');
    }

    public function destroy(Image $image)
    {
        Storage::disk('public')->delete('images/' . $image->file);
        LogHelper::log('deleted_image', 'image', $image->id, ['name' => $image->name]);
        $image->delete();
        return redirect()->route('admin.images.index')->with('success', 'Image supprimée.');
    }
}
