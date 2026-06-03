<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'slug'         => 'required|string|max:200|unique:posts,slug',
            'content'      => 'required|string',
            'published_at' => 'nullable|date',
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $data['user_id'] = Auth::id();
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
            'slug'         => 'required|string|max:200|unique:posts,slug,' . $post->id,
            'content'      => 'required|string',
            'published_at' => 'nullable|date',
        ]);
        $data['slug'] = Str::slug($data['slug']);
        $post->update($data);
        LogHelper::log('updated_post', 'post', $post->id, ['title' => $post->title]);
        return redirect()->route('admin.posts.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(Post $post)
    {
        LogHelper::log('deleted_post', 'post', $post->id, ['title' => $post->title]);
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Article supprimé.');
    }
}
