<?php
namespace App\Traits;

use Illuminate\Http\Request;

trait FileUploadTrait{

    function uploadFile(Request $request,string $inputName,?string $oldpath=null,string $path='/uploads'){
        if($request->hasFile($inputName)){
            $file=$request->{$inputName};
            $ext=$file->getClientOriginalExtension();//gives the extension name
            $fileName='media_'.uniqid().'.'.$ext;

            $file->move(public_path($path),$fileName);

            return $path.'/'.$fileName;
        }
        return null;
    }
}
