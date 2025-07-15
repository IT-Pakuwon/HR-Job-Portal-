<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\T_Message;
use App\Models\ProjectTask;
use Illuminate\Support\Carbon;

class SendCommentController extends Controller
{
    public function sendmsg(int $id, string $doctype, Request $request)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); 
        // dd($doctype.'-',$id);
            //    dd($request->all());
        //save trx_message
        T_Message::create([
            'docid' => $request->docid,
            'doctype' => $doctype,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->reason,
            'created_user' => $user->username,
            'status' => 'A'
        ]);

        
    }
}

