<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Ambil data dalam format JSON
    public function json()
    {
        return response()->json(Post::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post = Post::create($request->all());
        return response()->json($post); // Kirim JSON agar Fetch API bisa menangani
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post->update($request->all());
        return response()->json($post); // Kirim JSON agar bisa update di frontend
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post berhasil dihapus']); // Kirim JSON konfirmasi
    }
}
