<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessage;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = User::where('id', auth()->id())->select([
            'id',
            'name',
            'email',
        ])->first();

        $users = User::where('id', '!=', $user->id)->get();

        return view('home', [
            'user' => $user,
            'users' => $users,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $chatUser = User::find($request->get('chat_user_id'));

        $existsChat = Chat::where(function ($query) use ($currentUser, $chatUser) {
            $query->where('sender_id', $currentUser->id)
                ->where('receiver_id', $chatUser->id);
        })->orWhere(function ($query) use ($currentUser, $chatUser) {
            $query->where('sender_id', $chatUser->id)
                ->where('receiver_id', $currentUser->id);
        })->first();

        $messages = $existsChat
            ? $existsChat->messages()->with('user')->get()
            : collect();



        return response()->json($messages);
    }

    public function chat(int $user_id): \Illuminate\View\View
    {
        $currentUser = User::where('id', auth()->id())->select([
            'id',
            'name',
            'email',
        ])->first();

        $chatUser = User::where('id', $user_id)->select([
            'id',
            'name',
            'email',
        ])->first();

        return view("chat", [
            'user' => $currentUser,
            'chatUser' => $chatUser,
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        $chatUser = User::find($request->get('user_id'));

        $existsChat = Chat::where(function ($query) use ($currentUser, $chatUser) {
            $query->where('sender_id', $currentUser->id)
                ->where('receiver_id', $chatUser->id);
        })->orWhere(function ($query) use ($currentUser, $chatUser) {
            $query->where('sender_id', $chatUser->id)
                ->where('receiver_id', $currentUser->id);
        })->first();

        if (!$existsChat) {
            $existsChat = Chat::create([
                'sender_id' => $currentUser->id,
                'receiver_id' => $chatUser->id,
            ]);
        }

        if (!$existsChat->id) {
            return response()->json(['success' => false, 'message' => 'Chat creation failed.'], 500);
        }

        $message = Message::create([
            'chat_id' => $existsChat->id,
            'user_id' => $currentUser->id,
            'text' => $request->get('text'),
        ]);


        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $message->photo = $photoPath;
            $message->save();
        }

        SendMessage::dispatch($message);

        return response()->json([
            'success' => true,
            'message' => 'Message created and job dispatched.',
            'data' => $message,
        ]);
    }
}
