<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageType;
use App\Events\Messages\MessageCreated;
use App\Events\Messages\MessageDeleted;
use App\Events\Messages\MessageEdited;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Storage;

class MessageController
{
    public function create(Request $request, int $channelId)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', array_column(MessageType::cases(), 'value')),
            'mdata' => 'required',
        ]);

        $channel = Channel::find($channelId);

        if (!$channel) {
            return response()->json(['message' => 'Channel not found'], 404);
        }

        if ($request->file('mdata')?->isValid()) {
            $path = $request->file('mdata')->store('uploads', 'public');
        }

        Message::create([
            'type' => $request->type,
            'mdata' => $request->type == MessageType::Text->value ? $request->mdata : Storage::url($path),
            'channel_id' => $channel->id,
            'user_id' => $request->user()->id,
        ]);

        broadcast(new MessageCreated($request->mdata, $request->user()->id, $channel->id));

        return response()->json(['message' => 'Message created'], 201);
    }

    public function edit(Request $request, int $messageId)
    {
        $request->validate([
            'mdata' => 'required|string',
        ]);

        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        if ($message->type != MessageType::Text->value) {
            return response()->json(['message' => 'Message can not be edited'], 400);
        }

        $message->update([
            'mdata' => $request->mdata,
        ]);
        $message->save();

        broadcast(new MessageEdited($message->id, $message->channel_id, $request->user()->id));

        return response()->json(['message' => 'Message updated'], 201);
    }

    public function delete(Request $request, int $messageId)
    {
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        if ($message->type == MessageType::Text->value) {
            Storage::disk('public')->delete($message->mdata);
        }

        $message->delete();


        $serverId = $message->channel->server->id;

        broadcast(new MessageDeleted(
            $message->id,
            $message->channel->id,
            $serverId,
            $request->user()->id
        ));

        return response()->json(['message' => 'Message deleted'], 201);
    }

}
