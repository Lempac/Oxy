<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageType;
use App\Enums\PermsType;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Role;
use Auth;
use Illuminate\Http\Request;
use Storage;

class MessageController
{
    public function create(Request $request, Channel $channel)
    {
        $request->validate([
            'type' => 'required|in:'.implode(',', array_column(MessageType::cases(), 'value')),
        ]);

        $request->validate([
            'mdata' => $request->type === MessageType::Text->value ? 'required|string|max:500' : 'required|file|max:200000000',
        ]);

        $roles = $channel->server->roles->intersect(Auth::user()->roles);

        // TODO: Add checking for levels for message create
        if ($roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_CREATE_MESSAGE->value);
        })) {
            abort(403, 'Forbidden.');
        }

        if ($request->type !== MessageType::Text->value && $request->file('mdata')?->isValid()) {
            $file = $request->file('mdata');

            if (in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif', 'webp'])) {

                [$width, $height] = getimagesize($file->getRealPath());

                if ($width > 1920 || $height > 1080) {
                    return back()->withErrors(['icon' => ['The image must not exceed 1920x1080 pixels.']]);
                }
            }

            $name = $file->getClientOriginalName();
            $path = $file->store('uploads', 'public');
        }

        Message::create([
            'type' => $request->type,
            'mdata' => $request->type === MessageType::Text->value ? $request->mdata : (MessageType::File->value === $request->type ? $name.'|*|'.Storage::url($path) : Storage::url($path)),
            'channel_id' => $channel->id,
            'user_id' => $request->user()->id,
        ]);

        //        broadcast(new MessageCreated($request->mdata, $request->user()->id, $channel->id));

        return back();
    }

    public function edit(Request $request, Message $message)
    {
        $request->validate([
            'mdata' => 'required|string',
        ]);

        if ($message->user_id !== Auth::id()) {
            abort(403, 'Forbidden.');
        }

        if ($message->type != MessageType::Text->value) {
            abort(400, 'Message can not be edited');
        }

        $message->update([
            'mdata' => $request->mdata,
        ]);
        $message->save();

        //        broadcast(new MessageEdited($message->id, $message->channel_id, $request->user()->id));

        return back();
    }

    public function delete(Message $message)
    {

        $roles = $message->channel->server->roles->intersect(Auth::user()->roles);

        if ($message->user->id !== Auth::id() && $roles->doesntContain(function (Role $role) {
            return $role->hasPerms(PermsType::CAN_DELETE_MESSAGE->value);
        })) {
            abort(403, 'Forbidden.');
        }

        if ($message->type != MessageType::Text->value) {
            Storage::disk('public')->delete($message->mdata);
        }

        $message->delete();

        //        $serverId = $message->channel->server->id;

        //        broadcast(new MessageDeleted(
        //            $message->id,
        //            $message->channel->id,
        //            $serverId,
        //            $request->user()->id
        //        ));

        return back();
    }
}
