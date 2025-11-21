<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|in:photo,video',
            'photo' => 'required_if:type,photo|file|mimes:jpg,jpeg,png|max:10240',
            'video' => 'required_if:type,video|file|mimes:webm,mp4|max:51200',
        ]);

        $user = auth()->user();
        $type = $request->type;

        if ($type === 'photo') {
            $path = $request->file('photo')->store('photos', 'public');

            // Salva info nel database (opzionale)
            // Media::create([
            //     'user_id' => $user->id,
            //     'type' => 'photo',
            //     'path' => $path,
            // ]);

            return back()->with('success', 'Foto salvata con successo!');
        } else {
            $path = $request->file('video')->store('videos', 'public');

            // Salva info nel database (opzionale)
            // Media::create([
            //     'user_id' => $user->id,
            //     'type' => 'video',
            //     'path' => $path,
            // ]);

            return back()->with('success', 'Video salvato con successo!');
        }
    }
}
