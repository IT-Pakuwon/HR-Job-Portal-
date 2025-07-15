<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use DataTables;

class PostController extends Controller
{
    public function index()
    {
        return view('post.index');
    }

    public function getPosts()
{
    return response()->json(['data' => Post::latest()->get()]);
}


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post = Post::create($request->all());

        return response()->json(['post' => $post]);
    }

    public function edit(Post $post)
    {
        return response()->json($post);
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post->update($request->all());

        return response()->json(['post' => $post]);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }
}
