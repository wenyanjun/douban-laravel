<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

class PhoneController extends Controller
{
    public function index(Request $request)
    {
        // 响应时间不受限制
        ini_set('max_execution_time', '0');
        $phone = $request->input("phone", []);
        $flag = $request->input("flag", "");
        $cache_key = "phone";
        Cache::pull($cache_key);

        if (empty($phone)){
            return json_error("请求错误");
        }
        date_default_timezone_set('PRC');
        $date = date("Y-m-d-G-i-s");
        $dir = iconv("utf-8", "utf-8", $date);
        $dir = 'phone/'.$dir;
        $path = $dir."/".$date;
        $querys = "phone=";

        // 将数组以300个为单位进行分割
        $obj = self::split($phone);
        // 循环请求数据
        foreach ($obj as $value){
            for($i=0; $i<count($value); $i++){
                $v = $value[$i];
                $querys = $querys.$v.",";
            }
            self::writeFile($querys, $dir, $path);
            $querys = 'phone=';
        }
        // 压缩文件
        $zip = $dir.'.zip';
        $phone = base_path('public/').$dir;
        self::zipDir($phone,$zip);
        $ip = 'http://39.105.38.10:8081/';
        $uri = $ip.$zip;
        $obj = json_success(['zip_path'=>$uri,'flag'=>$flag]);
        Cache::put($cache_key, $obj);

        return $obj;

//        return json_error("请求失败");

//        return '简书关注coderYJ 欢迎加QQ群讨论277030213';
    }
    public function getKey(){
        $cache_key = "phone";
        $b = Cache::get($cache_key);
        return json_decode($b,true);
    }
    /**
     * 按照指定数量分块
     * @datetime 2019年7月2日  下午5:50:55
     * @comment
     *
     * @param array $data
     * @param number $num
     * @return array
     */
    public function split($data, $num = 300)
    {
        $arrRet = array();
        if( !isset( $data ) || empty( $data ) ) {
            return $arrRet;
        }
        $iCount = count($data)/$num;
        if( !is_int( $iCount ) ) {
            $iCount = ceil( $iCount );
        } else {
            $iCount += 1;
        }
        for( $i=0; $i<$iCount;++$i ) {
            $arrInfos = array_slice($data, $i*$num, $num );
            if( empty( $arrInfos )) {
                continue;
            }
            $arrRet[] = $arrInfos;
            unset( $arrInfos );
        }
        return $arrRet;
    }
    // 追加写入文件
    public function writeFile($querys, $dir, $path){
        $obj = self::http($querys);
        $arr_type = [
            "0"=>'实号',
            '1'=>'空号',
            '2'=>'停机',
            '3'=>'黑名单',
            '4'=>'无库存',
            '5'=>'其他'
        ];
        $arr_type2 = [
            "0"=>'shihao',
            '1'=>'konghao',
            '2'=>'tingji',
            '3'=>'heimingdan',
            '4'=>'wukucun',
            '5'=>'qita'
        ];
        if ($obj['success']){
            // 成功
            $data = $obj['data']['data'];
            $abc = [
                "0"=>[],
                '1'=>[],
                '2'=>[],
                '3'=>[],
                '4'=>[],
                '5'=>[]
            ];

            for($i=0; $i<count($data); $i++){
                $v = $data[$i];
                $state = $v['state'];
                $arr = &$abc[$state];
                $type = $arr_type[$state];
                $phone = $v['cell-phone'];
                $a = $phone."  ".$type."\n";
                array_push($arr, $a);
            }
            // 写入文件
            if (!file_exists($dir)){
                mkdir ($dir,0777,true);
            }

            foreach ($abc as $key=>$value){
                if (empty($value)) continue;
                $type = $arr_type2[$key];
                // 如果不存在则创建 如果存在则追加内容
                file_put_contents($path.$type.".txt",$value, FILE_APPEND);
            }
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param $querys
     * @return array
     */
    public function http($querys)
    {
        $host = "http://sms02.market.alicloudapi.com";
        $path = "/phone/deliver/arrs";
        $method = "GET";
        $appcode = "69752af9f5a447f1bdfc9e6abf7dee29";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);

//        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // 返回请求头信息, 设置为false 就没有了
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $result = curl_exec($curl);
        curl_close($curl);
        // json 反序列化
        $obj = json_decode($result, true);
        return $obj;
    }
    function zipDir($basePath,$zipName){
        $zip = new ZipArchive();
        $fileArr = [];
        $fileNum = 0;
        if (is_dir($basePath)){
            if ($dh = opendir($basePath)){
                $zip->open($zipName,ZipArchive::CREATE);
                while (($file = readdir($dh)) !== false){
                    if(in_array($file,['.','..',])) continue; //无效文件，重来
                    $file = iconv('gbk','utf-8',$file);
                    $extension = strchr($file,'.');
                    rename(iconv('UTF-8','GBK',$basePath.'/'.$file), iconv('UTF-8','GBK',$basePath.'/'.$fileNum.$extension));
                    $zip->addFile($basePath.'/'.$fileNum.$extension,$fileNum.$extension);
                    $zip->renameName($fileNum.$extension,$file);
                    $fileArr[$fileNum.$extension] = $file;
                    $fileNum++;
                }
                $zip->close();
                closedir($dh);
                foreach($fileArr as $k=>$v){
                    rename(iconv('UTF-8','GBK',$basePath.'/'.$k), iconv('UTF-8','GBK',$basePath.'/'.$v));
                }
            }
        }
    }
}
