<?php
namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Models\News;
use Illuminate\Http\Request;
use App\Models\Autonbr;
use App\Models\Attachment;

class NewsController extends Controller
{
    public function index()
    {
        return view('pages.news.news');
    }

    // public function json()
    // {
    //     return response()->json(News::latest()->get());
    // }
    public function json()
    {
        $post = News::select(['id', 'title', 'description', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $post]);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $datenow = Carbon::now()->format('Y-m-d');
        $dt = Carbon::now();
        $year = $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype = 'NEW';
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        // Generate task ID
        $autonbr = Autonbr::lockForUpdate()
            ->where('doctype', $doctype)
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', 'A')
            ->first();

        if (!$autonbr) {
            $autonbr = Autonbr::create([
                'doctype' => $doctype,
                'year' => $year,
                'month' => $month,
                'status' => 'A',
                'number' => 1
            ]);
            $urutan = 1;
        } else {
            $urutan = $autonbr->number + 1;
            $autonbr->number = $urutan;
            $autonbr->save();
        }

        $tglbln = substr($year, 2) . $month;
        $docid = $doctype . $tglbln . sprintf("%03d", $urutan);
                       

        $post = News::create([
            'docid' => $docid,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'A',
            'created_user' => $user->username,
        ]);  

        if ($request->hasfile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $randomNumber = random_int(10000000, 99999999);
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
               
                $originalName = str_replace('%', '', $file->getClientOriginalName());
                $attachfile = md5($randomNumber) . '-' . $originalName;

                //attach to folder
                $folder_attach = public_path() . '/attachments/'.$year;
                $config['upload_path'] = $folder_attach;                   
                if(!is_dir($folder_attach))
                {
                    mkdir($folder_attach, 0777);
                }
                
                $folder_upload = $folder_attach;
                // $folder_upload = public_path() . '/attachments';
                $file->move($folder_upload, $attachfile);

                //insert to table attachments
                $attach = new Attachment();
                $attach->docid = $docid;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->username;
                $attach->save();
            }
        }

        return response()->json($post);
    }

    public function edit($id)
    {
        $post = News::findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        // dd([
        //     'title' => $request->input('title'),
        //     'description' => $request->input('description'),
        //     'files' => $request->file('attachments'),
        // ]);
        
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $post = News::findOrFail($id);

        $post->title = $request->title;
        $post->description = $request->description;
        $post->updated_user = auth()->user()->username; 
        $post->save();

        return response()->json($post);
    }


    public function toggleStatus($id)
    {
        $post = News::findOrFail($id);
        $post->update(['status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

}
