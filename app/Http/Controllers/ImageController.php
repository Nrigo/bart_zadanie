<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ImageController extends Controller
{
   
    public function getImageThumbnail(Request $request){
         
         $width  = $request->w;
         $height = $request->h;
         $path   = $request->path;

         if($width==0)  $width  = null;
         if($height==0) $height = null;

         $pathInfo = pathinfo($path);
         $storagePath = \Storage::disk('public')->path($path);
         
         if(!\Storage::disk('public')->exists($path) || !\Storage::disk('public')->getMetadata($path)['type'] === 'file'){
            return response()->json([
                "code"        => 404,
                "name"        => "INVALID_PATH",
                'description' => "Requested path is not an image",
            ],404);
         }
         try{
             $img = \Image::make($storagePath)->resize($width,$height,function($constraint) use($width,$height){
                 if($width==null || $height==null) $constraint->aspectRatio();
                 $constraint->upsize();
             })->encode();
         }
         catch(\Exception $e){
            return response()->json([
                "code"        => 500,
                "name"        => "UNDEFINED_ERROR",
                'description' => "Undefined error",
            ],500);
         }

         $headers = [
            //'Content-Type' => 'image/'.$pathInfo['extension'],
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename='.$pathInfo['basename'],
         ];

         return response()->stream(function() use ($img) {
            echo $img;
         }, 200, $headers);

    }

}
