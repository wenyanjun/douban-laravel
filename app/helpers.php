<?php
function common(){
    $obj = [];
    $obj['coderYJ'] = '简书关注coderYJ 欢迎加QQ群讨论277030213';
    $obj["money"] = "给我打赏 https://blog.csdn.net/simplyou/article/details/113424779?spm=1001.2014.3001.5501";
    $obj["course"] = "微信小程序课程 https://www.bilibili.com/video/BV1ia4y1H794";
    $obj["www.itjava.cn"] = "API文档 www.itjava.cn";
    $obj["idea"] = "idea 激活 https://www.jianshu.com/p/28f4e9a6c7c8";
    return $obj;
}
// 应用公共文件
function json_success($data){
    $obj = common();
    $obj['code'] = 200;
    $obj['msg'] = '请求成功';
    $obj['data'] = $data;
    if (empty($data)){
        $obj['msg'] = '这已经是我的底线了';
    }
    return json_encode($obj);
}
function json_error($msg='获取失败'){
    $obj = common();
    $obj['code'] = 400;
    $obj['msg'] = $msg;
    $obj['data'] = null;
    return json_encode($obj);
}
function unicodeDecode($unicode_str){
    $json = '{"str":"'.$unicode_str.'"}';
    $arr = json_decode($json,true);
    if(empty($arr)) return '';
    return $arr['str'];
}
