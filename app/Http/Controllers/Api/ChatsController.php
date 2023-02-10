<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Models\Message;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Exception;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        try
        {
            $user = Auth::user();
            DB::beginTransaction();
                $message = $user->messages()->create([
                    'message' => $request->message
                ]);
            DB::commit();
            broadcast(new MessageSent($user, $message))->toOthers();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json(['chat_status' => $msg]);
    }
}
