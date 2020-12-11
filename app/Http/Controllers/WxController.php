<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WxController extends Controller
{
    // 文件上传
    public function uploadImage(Request $request){
        if ($request->hasFile('file') && $request->file("file")->isValid()){
            $file = $request->file("file");
            $allowed_extensions = ["png", "jpg", "gif"];
            if (!in_array($file->getClientOriginalExtension(), $allowed_extensions)) {
                dd('只能上传png,jpg和gif格式的图片.');
            }else{
                // public 文件夹下面建 storage/uploads 文件夹
                $destinationPath = 'uploads/';
                $extension = $file->getClientOriginalExtension();
                $fileName=md5(time().rand(1,1000)).'.'.$extension;
                $file->move($destinationPath,$fileName);
                $filePath = asset($destinationPath.$fileName);
                dd("文件路径：".$filePath);
            }
        }else{
           dd("图片上传失败");
        }
    }
}
