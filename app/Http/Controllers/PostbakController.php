<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();
        return view('post.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        Post::create($request->all());
        return redirect()->route('posts.index')->with('success', 'Post berhasil ditambahkan.');
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post->update($request->all());
        return redirect()->route('posts.index')->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post berhasil dihapus.');
    }
}

