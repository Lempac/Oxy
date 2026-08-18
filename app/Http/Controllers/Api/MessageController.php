<?php

namespace App\Http\Controllers\Api;

use App\Events\Messages\MessageCreated;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessageController
{
    public function create(Request $request, Server $server, Channel $channel)
    {
        $maxFileSize = config('uploads.max_file_size', 25600); // 25 MB in KB
        $maxAttachments = config('uploads.max_attachments_per_message', 10);
        $maxTotalSize = config('uploads.max_total_size', 102400); // 100 MB in KB
        $maxMsgLength = config('uploads.max_message_length', 2000);
        $maxImgWidth = config('uploads.max_image_width', 4096);
        $maxImgHeight = config('uploads.max_image_height', 4096);
        $blockedExtensions = config('uploads.blocked_extensions', [
            'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar',
            'cgi', 'pl', 'asp', 'aspx', 'jsp', 'sh', 'bash', 'zsh', 'cmd', 'ps1',
        ]);

        $request->validate([
            'content' => "nullable|string|max:{$maxMsgLength}",
            'attachments' => "nullable|array|max:{$maxAttachments}",
            'attachments.*' => "file|max:{$maxFileSize}",
        ]);

        $content = $request->input('content') ?? $request->input('mdata');
        $files = $request->file('attachments') ?? [];
        if (! is_array($files)) {
            $files = [$files];
        }
        // Filter out null / invalid file entries
        $files = array_values(array_filter($files, fn ($file) => $file && $file->isValid()));

        $hasContent = ! empty(trim((string) $content));
        $hasAttachments = count($files) > 0;

        if (! $hasContent && ! $hasAttachments) {
            throw ValidationException::withMessages([
                'content' => 'Please provide a message text or attach at least one file.',
            ]);
        }

        setPermissionsTeamId($channel->server_id);
        $user = Auth::user();

        if ($hasContent && ! $user->hasPermissionTo('CAN_CREATE_MESSAGE')) {
            abort(403, 'You do not have permission to send messages in this server.');
        }

        if ($hasAttachments && ! $user->hasPermissionTo('CAM_CREATE_ATTACHMENTS')) {
            abort(403, 'You do not have permission to attach files in this server.');
        }

        $totalSizeKb = 0;
        $attachmentDataList = [];

        foreach ($files as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            $fileSizeKb = $file->getSize() / 1024;
            $totalSizeKb += $fileSizeKb;

            if (in_array($extension, $blockedExtensions, true)) {
                throw ValidationException::withMessages([
                    'attachments' => "Files with the '.{$extension}' extension are not permitted.",
                ]);
            }

            $width = null;
            $height = null;

            if (in_array($extension, ['jpeg', 'png', 'jpg', 'gif', 'webp', 'bmp', 'svg'], true)) {
                $imageDimensions = @getimagesize($file->getRealPath());
                if ($imageDimensions) {
                    [$width, $height] = $imageDimensions;
                    if ($width > $maxImgWidth || $height > $maxImgHeight) {
                        throw ValidationException::withMessages([
                            'attachments' => "Image '{$originalName}' exceeds the maximum allowed dimensions ({$maxImgWidth}x{$maxImgHeight}px).",
                        ]);
                    }
                }
            }

            $path = $file->store('uploads', config('filesystems.default'));
            if (! $path) {
                throw ValidationException::withMessages([
                    'attachments' => "Failed to upload file '{$originalName}' to storage.",
                ]);
            }

            $attachmentDataList[] = [
                'filename' => $originalName,
                'path' => $path,
                'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
            ];
        }

        if ($totalSizeKb > $maxTotalSize) {
            throw ValidationException::withMessages([
                'attachments' => 'Total attachments size exceeds the maximum payload limit of 100MB.',
            ]);
        }

        DB::transaction(function () use ($content, $channel, $user, $attachmentDataList) {
            $message = Message::withoutEvents(function () use ($content, $channel, $user) {
                return Message::create([
                    'content' => $content ?: null,
                    'channel_id' => $channel->id,
                    'user_id' => $user->id,
                ]);
            });

            foreach ($attachmentDataList as $attachmentData) {
                $message->attachments()->create($attachmentData);
            }

            $message->load('attachments');
            event(new MessageCreated($message));
        });

        return back()->with('message', 'Message created');
    }

    public function edit(Request $request, Message $message)
    {
        $maxMsgLength = config('uploads.max_message_length', 2000);

        $request->validate([
            'content' => "nullable|string|max:{$maxMsgLength}",
            'mdata' => "nullable|string|max:{$maxMsgLength}",
        ]);

        if ($message->user_id !== Auth::id()) {
            abort(403, 'Forbidden.');
        }

        $content = $request->input('content') ?? $request->input('mdata');

        if (empty(trim((string) $content)) && $message->attachments()->count() === 0) {
            throw ValidationException::withMessages([
                'content' => 'Message content cannot be empty.',
            ]);
        }

        $message->update([
            'content' => $content ?: null,
        ]);

        return back()->with('message', 'Message updated');
    }

    public function delete(Message $message)
    {
        setPermissionsTeamId($message->channel->server_id);
        if ($message->user_id !== Auth::id() && ! Auth::user()->hasPermissionTo('CAN_DELETE_MESSAGE')) {
            abort(403, 'Forbidden.');
        }

        $message->delete();

        return back()->with('message', 'Message deleted');
    }
}
