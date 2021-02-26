<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use QL\QueryList;

// 豆瓣租房接口
class ZuFangController extends Controller
{
    // 过期时间2天
    public $exp_time = 60*60*24*2;

    // 租房信息
    function Index(Request $request){
        $page = $request->input("page", 0)*1;
        if ($page <= 0){
            $page = 0;
        }
        $count = 25;
        $start = $count * $page;
        try {
            $key = "zufang".$start;
            $data = Cache::remember($key,$this->exp_time,function () use ($start){
                $url = 'https://www.douban.com/group/308599/discussion?start='.$start;
                $ql = QueryList::getInstance();
                $ql = $ql->get($url);
                $data = $ql->find(".olt")->children("tr")->map(function ($item){
                    $title = $item->find('.title a')->text();
                    $actor = $item->find("td[nowrap='nowrap'] a")->text();
                    $time = $item->find(".time")->text();
                    $id = $item->find('.title a')->href;
                    $id1 = '';
                    if (!empty($id)){
                        preg_match('#topic/([\s\S]*?)/#', $id, $ids);
                        $id1 = $ids[1];
                    }
                    return [
                        "id"=>$id1,
                        "title" => $title,
                        "actor" => $actor,
                        "time"=>$time
                    ];
                })->all();
                array_shift($data);
                return $data;
            });
            $obj = ["page"=>$page, "subject"=>$data];
            return json_success($obj);
        }catch (\Exception $e){
            return json_error("请求出错");
        }
    }
    // 详情
    function detail(Request $request){
        $id = $request->input("id",'');
        if (empty($id)){
            return json_error("id不能为空");
        }
        try {
            $data = Cache::remember("zufang".$id,$this->exp_time,function ()use ($id){
                $url = "https://www.douban.com/group/topic/$id/";
                $ql = QueryList::getInstance();
                $ql = $ql->get($url);
                $title = $ql->find("h1")->text();
                $content = $ql->find(".topic-content");
                $user_img = $content->find(".user-face img")->src;
                // 来自
                $user_name = $content->find(".topic-doc .from a")->text();
                $create_time = $content->find(".topic-doc .create-time")->text();
                // 描述
                $descript = $content->find(".rich-content p")->texts()->toArray();
                // 过滤空值
                $descript = array_filter($descript);
                $imgs = $content->find(".rich-content")->children('.image-container')->map(function ($item){
                    $img = $item->find("img")->src;
                    return $img;
                })->toArray();

                $data = [
                    "title"=>$title,
                    "user_img"=>$user_img,
                    "user_name"=>$user_name,
                    "create_time"=>$create_time,
                    "descript"=>$descript,
                    "imgs"=>$imgs
                ];
                return $data;
            });
            return json_success($data);
        }catch (\Exception $e){
            return json_error("请求出错");
        }
    }
}
