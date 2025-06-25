<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\SendMessageRequest;
use App\Models\ChatChannel;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Str;

class MessageController extends Controller
{
    //

    public function sendMessage(SendMessageRequest $request)
    {
        $validation = $request->validated();

        $message = new Message();
        $message->user_id = $validation['senderId'];
        $message->receiver_id = $validation['receiverId'];
        $message->message = $request->messageText ?? '';


        $channel = ChatChannel::where(function ($query) use ($message) {
            $query->where('first_user', $message->user_id)
                ->where('second_user', $message->receiver_id);
        })->orWhere(function ($query) use ($message) {
            $query->where('first_user', $message->receiver_id)
                ->where('second_user', $message->user_id);

        })->first();


        if (!$channel) {
            $channel = ChatChannel::create([
                'id' => $request['channel'],
                'first_user' => $message->user_id,
                'second_user' => $message->receiver_id
            ]);
        }


        $message->channel = $channel->id;

        if ($request->hasFile('image')) {
            $imageUrl = $this->dealWithImage($request->file('image'), $message->channel);

            $message->image_url = $imageUrl;
        }
        $message->save();

        MessageSent::dispatch($message, $channel->id);

    }

    public function createChannel(Request $request)
    {
        $channel = ChatChannel::where(function ($query) use ($request) {
            $query->where('first_user', $request['firstUser'])
                ->where('second_user', $request['secondUser']);
        })->orWhere(function ($query) use ($request) {
            $query->where('first_user', $request['secondUser'])
                ->where('second_user', $request['firstUser']);

        })->first();

        if (!$channel) {
            $channel = ChatChannel::create([
                'id' => $request['channel'],
                'first_user' => $request['firstUser'],
                'second_user' => $request['secondUser']
            ]);
        }

        return response()->json($channel->id);
    }
    public function getChatChannel(Request $request, User $user)
    {
        $currentUser = $request->user('sanctum');


        $channel = ChatChannel::where(function ($query) use ($currentUser, $user) {
            $query->where('first_user', $currentUser->id)
                ->where('second_user', $user->id);
        })->orWhere(function ($query) use ($currentUser, $user) {
            $query->where('first_user', $user->id)
                ->where('second_user', $currentUser->id);
        })->get()->first();

        if (!$channel) {
            return response()->json('');
        }
        return response()->json($channel->id);
    }

    public function getAllChatChannels(Request $request)
    {
        $currentUser = $request->user('sanctum');

        $channels = ChatChannel::where('first_user', $currentUser->id)
            ->orWhere('second_user', $currentUser->id)->get();

        $mappedChannels = $channels->map(function ($channel) {
            return $channel->id;
        })->all();

        return response()->json($mappedChannels);
    }

    public function getAllMessages(Request $request, ChatChannel $channel)
    {

        $messages = $channel->messages;
        $mappedMessages = $messages->map(function ($message) {
            return [
                'messageText' => $message->message,
                'senderId' => $message->user_id,
                'receiverId' => $message->receiver_id,
                'type' => $message->type,
                'timestamp' => Carbon::parse($message->created_at)->timestamp,
                'imageUrl' => $message->image_url,
                'channel' => $message->channel
            ];
        });
        return response()->json($mappedMessages);

    }

    private function dealWithImage($file, $channel)
    {
        $disk = Storage::disk('images');

        $fileName = uuid_create() . '.jpg';

        $file = $disk->putFileAs($channel, $file, $fileName);

        $url = $disk->url($file);

        return $url;


    }

}
