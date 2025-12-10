<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Connection;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;

class MessageService
{   
    public function handleMessage($socket,$message)
    {
        $chat = $this->findOrCreateChat($message);
        $mediaData = $this->handleMedia($message);

        $data = [
            'body'            => $message->body,
            'number'          => $message->number,
            'chat_id'         => $chat->id,
            'from_me'         => true,
            'media'           => $mediaData,
            'user_id'         => $chat->user_id,
            'temp_signature'  => $message->tempSignature,
            'ch_type'         => 'NEW.MESSAGE'
        ];

        $connection = Connection::where('user_id', $chat->user_id)->first();

        if ($connection?->status === 'DISCONNECTED') {
            return response()->json([
                "status"  => 400,
                "message" => "Whatsapp desconectado."
            ], 400);
        }

        $socket->send($data);

        return $data;
    }

    private function findOrCreateChat($request)
    {
        if ($request->filled('chat_id')) {
            $chat = Chat::find($request->chat_id);

            if ($chat) {
                $lastMessage = $request->filled('media') ? "Multimedia" : $request->body;

                $unread = ($request->from_me == false)
                    ? $chat->unread_message + 1
                    : 0;

                $chat->update([
                    'last_message'   => $lastMessage,
                    'unread_message' => $unread,
                ]);

                return $chat;
            }
        }

        $contact = Contact::firstOrCreate(
            ['phone_number' => $request->number],
            ['user_id' => Auth::user()->id]
        );

        return Chat::create([
            'state'          => 'OPEN',
            'last_message'   => $request->filled('media') ? "Multimedia" : $request->body,
            'unread_message' => 1,
            'contact_id'     => $contact->id,
            'user_id'        => $contact->user_id,
        ]);
    }
    
    private function handleMedia($request)
    {
        $mediaData = [];

        if (!$request->filled('media')) {
            return $mediaData;
        }

        $media = json_decode($request->media);

        foreach ($media as $file) {
            $path   = $this->testdir($file->type);
            $format = explode('/', $file->media_type)[1];
            $name   = date('H_i_s', time() - 18000);
            $stream = base64_decode(explode(',', $file->media)[1]);
            $process = "";

            $filename = "$path/$name.$format";
            file_put_contents($filename, $stream);

            if ($file->type === 'audio' && $format === 'webm') {
                $convertedFilename = "$path/$name.ogg";

                $process = new Process([
                    'ffmpeg',
                    '-i', public_path($filename),
                    '-vn',
                    '-ac', '1',
                    '-c:a', 'libopus',
                    $convertedFilename
                ]);

                $process->run();
                $format = 'ogg';
                $filename = $convertedFilename;
                $process = $process->getErrorOutput();
            }

            $mediaData[] = [
                "filename" => $filename,
                "caption"  => $file->caption ?? "",
                "type"     => $file->type,
                "format"   => $format,
                "process"  => $process,
            ];
        }

        return $mediaData;
    }

    public function checkdir($name){
        if(!is_dir($name)){
            mkdir(public_path($name),0777,true);
            return true;
        }else{
            return true;
        }
    }

    public function testdir($type){
        $root='files/'.$type.'/';
        $name=date('Y/m/d',time()-18000);
        $this->checkdir($root.$name);
        return $root.$name;
    }

}
