<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(25);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string|max:255',
            'image'        => 'nullable|image|max:2048',
            'slug'         => 'required|string|max:200|unique:posts,slug',
            'content'      => 'required|string',
            'published_at'    => 'nullable|date',
            'is_pinned'       => 'boolean',
            'allow_reactions' => 'boolean',
            'allow_comments'  => 'boolean',
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $data['user_id'] = Auth::id();
        $data['is_pinned'] = $request->has('is_pinned');
        $data['allow_reactions'] = $request->boolean('allow_reactions');
        $data['allow_comments'] = $request->boolean('allow_comments');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }
        $post = Post::create($data);
        LogHelper::log('created_post', 'post', $post->id, ['title' => $post->title]);
        return redirect()->route('admin.posts.index')->with('success', 'Article créé.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string|max:255',
            'image'        => 'nullable|image|max:2048',
            'slug'         => 'required|string|max:200|unique:posts,slug,' . $post->id,
            'content'      => 'required|string',
            'published_at'    => 'nullable|date',
            'is_pinned'       => 'boolean',
            'allow_reactions' => 'boolean',
            'allow_comments'  => 'boolean',
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $data['is_pinned'] = $request->has('is_pinned');
        $data['allow_reactions'] = $request->boolean('allow_reactions');
        $data['allow_comments'] = $request->boolean('allow_comments');
        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }
        $post->update($data);
        LogHelper::log('updated_post', 'post', $post->id, ['title' => $post->title]);
        return redirect()->route('admin.posts.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        LogHelper::log('deleted_post', 'post', $post->id, ['title' => $post->title]);
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Article supprimé.');
    }
}
