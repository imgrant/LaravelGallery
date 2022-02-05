<?php

namespace App\Http\Controllers;

use App\Photos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GalleryController extends Controller
{
    public function index(){
        return view('app');
    }

    public function getPhotos()
    {
        return response()->json(Auth::user()->photos->toArray());
    }

    public function uploadPhotos(Request $request)
    {
        $file = $request->file('file');
        $ext = $file->extension();
        $name = str_random(20).'.'.$ext ;
        list($width, $height) = getimagesize($file);
        $size = filesize($file);
        $path = Storage::putFileAs(
            'uploads', $file, $name
        );
        if($path){
            $create = Auth::user()->photos()->create([
                'file' => $path,
                'uri' => Storage::url($path),
                'public' => false,
                'height' => $height,
                'width' => $width,
                'size' => $size
            ]);

            if($create){
                return response()->json([
                    'uploaded' => true
                ]);
            }
        }
    }

    public function deletePhoto(Request $request)
    {
        $photo = Photos::find($request->id);
        if(Storage::delete($photo->file) && $photo->delete()){
            return response()->json([
                'deleted' => true
            ]);
        }
    }

}
