<?php
// 应用公共文件
function json_success($data){
    $obj['code'] = 200;
    $obj['msg'] = '请求成功';
    $obj['data'] = $data;
    if (empty($data)){
        $obj['msg'] = '这已经是我的底线了';
    }
    $obj['coder'] = '简书关注coderYJ 欢迎加QQ群讨论277030213';
    return json_encode($obj);
}
function json_error($msg='获取失败'){
    $obj['code'] = 400;
    $obj['msg'] = $msg;
    $obj['data'] = null;
    $obj['coder'] = '简书关注coderYJ 欢迎加QQ群讨论277030213';
    return json_encode($obj);
}
