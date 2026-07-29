<?php

namespace App\Http\Controllers;

use App\Models\MsApplication;
use Illuminate\Http\Request;

class MsApplicationController extends Controller
{

    public function index()
    {
        // Ambil data dari beberapa tabel jika perlu
        $applications = MsApplication::all();     

        return view('pages.applications.applications', compact('applications'));
    }

    // Ambil data dalam format JSON
    public function json()
    {
        return response()->json(MsApplication::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'application_code' => 'required',
            'application_name' => 'required',
            // 'status' => 'required',
        ]);

        $post = MsApplication::create([
            'application_code' => $request->application_code,
            'application_name' => $request->application_name,
            'status' => 'A', // Set status default
        ]);  

        // $post = MsApplication::create($request->all());
        return response()->json($post); // Kirim JSON agar Fetch API bisa menangani
    }

    public function update(Request $request, MsApplication $post)
    {
        $request->validate([
            'application_code' => 'required',
            'application_name' => 'required',
            // 'status' => 'required',
        ]);

        $post->update($request->all());
        return response()->json($post); // Kirim JSON agar bisa update di frontend
    }

    public function destroy(MsApplication $post)
    {
        $post->delete();
        return response()->json(['message' => 'Data berhasil dihapus']); // Kirim JSON konfirmasi
    }
}
