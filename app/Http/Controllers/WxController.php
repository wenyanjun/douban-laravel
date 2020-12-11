<?php

namespace App\Http\Controllers;
use GuzzleHttp;
use Illuminate\Http\Request;

class WxController extends Controller
{
    const appid = 'wx94184ff59195c159';
    const secret = "1f16f4519f3145a1a51d45e480d984be";

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
    public function login(Request $request){
        $code = $request->input("code");
        if (empty($code)){
            return json_error("code不能为空");
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=". self::appid ."&secret=". self::secret ."&js_code=$code&grant_type=authorization_code";
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        $data = json_decode($response->getBody()->getContents(),true);

        if (array_key_exists("errcode",$data) && $data['errcode'] == 40163){
            return json_error("code过期");
        }
        return json_success($data);
    }
}
