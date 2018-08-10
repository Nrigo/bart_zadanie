<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
   
    public function galleryList(Request $request){
        
        $directories = \Storage::disk('public')->directories();
        
        $galleries = array();

        foreach($directories as $directory){
            array_push($galleries,["path" =>  rawurlencode($directory) , "name" => $directory]);
        }

        return response()->json([
            "galleries" => $galleries
        ]);
    }

    public function galleryCreate(Request $request){

        if($request->name==null || $request->name==""){
          return response()->json([
                  "code" =>  400,
                  "payload" => [
                    "paths" =>  ["name"],
                    "validator" =>  "required",
                    "example" => null
                  ],
                  "name" =>  "INVALID_SCHEMA",
                  "description" => "Bad JSON object: u'name' is a required property"  
          ],400);
        }
        else if(strpos($request->name,'/')!==FALSE){
          return response()->json([
                  "code" =>  400,
                  "payload" => [
                    "paths" =>  ["name"],
                    "validator" =>  "required",
                    "example" => null
                  ],
                  "name" =>  "INVALID_SCHEMA",
                  "description" => "Bad JSON object: u'name' can not contain '/' character"  
          ],400);
        }

        if(\Storage::disk('public')->exists($request->name)){

            return response()->json([
                "code"        => 409,
                "name"        => "INVALID_NAME",
                'description' => "Gallery with this name already exists",
            ],409);
        }

        if(\Storage::disk('public')->makeDirectory($request->name)){
            return response()->json([
                "path" =>  rawurlencode($request->name) , 
                "name" =>  $request->name
            ],201);  
        }
        else{
            return response()->json([
                "code"        => 500,
                "name"        => "UNDEFINED_ERROR",
                'description' => "Undefined error",
            ],500);
        }
    }

    public function imagesList(Request $request){

        $path = $request->path;

        if(!$this->isGallery($path)){
            return response()->json([
                "code"        => 404,
                "name"        => "INVALID_NAME",
                'description' => "Gallery with this name does not exists",
            ],404);
        }

        $files = \Storage::disk('public')->files($path);

        $images = array();
        
        foreach($files as $file){
            array_push($images,$this->getFileInfo($file));
        }
        
         return response()->json([
            'gallery' => ["path" =>  rawurlencode($request->path) , "name" => $request->path],
            'images'  => $images
         ],200);
    }
    
    public function delete(Request $request){

        if(!\Storage::disk('public')->exists($request->path)){
            return response()->json([
                "code"        => 404,
                "name"        => "INVALID_NAME",
                'description' => "File or directory with this name does not exists",
            ],404);
        }

        if($this->isDirectory($request->path)){
           if(\Storage::disk('public')->deleteDirectory($request->path)){
               return response()->json([
                    "code"        => 200,        
               ],200);
           }  
        }
        else{
           if(\Storage::disk('public')->delete($request->path)){
               return response()->json([
                   "code"        => 200,
               ],200);
           }  
        }
        
        return response()->json([
                "code"        => 500,
                "name"        => "UNDEFINED_ERROR",
                'description' => "Undefined error",
            ],500);

    }

    public function imageUpload(Request $request){

        if(!$this->isGallery($request->path)){
            return response()->json([
                "code"        => 404,
                "name"        => "INVALID_PATH",
                'description' => "Gallery with this path does not exists",
            ],404);
        }
        //dd($request->allFiles());
        $file = $request->file('image');

        if($file==null){
            return response()->json([
                "code"        => 400,
                "name"        => "INVALID_REQUEST",
                'description' => "Image is required property",
            ],400);
        }
 
        // -------- generate file name ---------------
        $facebookId = $this->getFacebookId();
        if($facebookId==null){
           $fileName = $file->getClientOriginalName();
        }
        else{
           $fileName = '('.$facebookId.')'.$file->getClientOriginalName();
        }
        // -------------------------------------------


        if($file->storeAs($request->path,$fileName,'public')){
            return response()->json([
                'uploaded' => [ $this->getFileInfo($request->path.'\\'.$fileName) ]
            ],201);
        }
        else{
            return response()->json([
                "code"        => 500,
                "name"        => "UNDEFINED_ERROR",
                'description' => "Undefined error",
            ],500);
        }
    }

    private function getFileInfo($path){

        $metadata = \Storage::disk('public')->getMetadata($path);
        $pathInfo = pathinfo($path);
            
        $path     = $pathInfo['basename'];
        $fullpath = $metadata['path'];
        $name     = ucfirst($pathInfo['filename']);
        $modified = \Carbon\Carbon::createFromTimestamp($metadata['timestamp'])->toAtomString();
        
        return ['path' => $this->encodePath($path),'fullpath' => $this->encodePath($fullpath),'name' => $name,'modified' => $modified];

    }

    private function isDirectory($path){
        if(\Storage::disk('public')->getMetadata($path)['type'] === 'dir'){
            return true;
        }
        else return false;
    }

    private function isGallery($path){
        if(\Storage::disk('public')->exists($path) && $this->isDirectory($path)){
            return true;
        }
        else return false;
    }


    private function encodePath($path){
        $pathParts = explode('/',$path);

        foreach($pathParts as &$pathPart){
            $pathPart = rawurlencode($pathPart);
        }
        return implode('/',$pathParts);
    }

    private function decodePath($path){
        $pathParts = explode('/',$path);

        foreach($pathParts as &$pathPart){
            $pathPart = urldecode($pathPart);
        }

        return implode('/',$pathParts);
    }

    private function getFacebookId(){
        $access_token = \Session::get('fb_access_token',null);
        if($access_token==null) return null;

        $url = 'https://graph.facebook.com/me';
        $data = array('fields' => 'id', 'access_token' => $access_token);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) { return null; }
        
        $arrayResult = json_decode($result);

        
        if(array_key_exists('id',$arrayResult)){
           return $arrayResult->id;
        }
        else return null;
    } 





}
