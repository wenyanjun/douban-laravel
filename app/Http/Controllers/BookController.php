<?php
/**
 * @author <技术交流QQ群 277030213>
 * @since <3.0.0>
 * @GitHub https://github.com/wenyanjun/douban
 * 爬虫框架地址 http://querylist.cc/docs/api/v4/Elements-introduce
 */
namespace App\Http\Controllers;

// 豆瓣图书控制器
use Illuminate\Http\Request;
use QL\QueryList;

class BookController extends Controller {
    function index(){

    }
    function top250(Request $request){
        $page = $request->input("page", 0) * 1;
        if ($page <= 0) $page = 0;
        $perPage = 25;
        $page_start = $page * $perPage;

        $url = "https://www.douban.com/doulist/513669/?start=$page_start&sort=time&playable=0&sub_type=";
        $q = QueryList::getInstance();
        $q = $q ->get($url);
        $data = $q->find(".article")->children(".doulist-item")->map(function ($item){

            $mod = $item->find('.mod');
            $id = $mod ->find('.post a') ->href;
            preg_match('#subject/([\s\S]*?)/#', $id, $ids);
            $title = $mod->find(".title a")->text();
            $img = $mod ->find('.post img')->src;
            // 评分
            $score = $mod->find('.rating .rating_nums')->text();
            // 评价人数
            $people = $mod->find('.rating span')->eq(2)->text();
            $abstract = $mod->find('.abstract')->text();

            $abstract = str_replace(array("\r\n", "\r", "\n"), "", $abstract);
            $abstract = explode('                      ',$abstract,4);
            $time = $mod->find('.ft .time span')->text();

            return [
                'id'=>$ids[1],
                "title"=>$title,
                "img"=>$img,
                "score"=>$score,
                "people"=>$people,
                "abstract"=>$abstract,
                "time"=>$time
            ];
        })->all();
        for($i=0; $i<count($data); $i++){
            $data[$i]['order_num'] = $perPage * $page + $i;
        }
        $obj = array('total' => 250, 'limit' => $perPage, 'page' => $page, 'subject' => $data);
        return json_success($obj);
    }
    function unicodeDecode($unicode_str){
        $json = '{"str":"'.$unicode_str.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];
    }
    function info(Request $request){
        $url = "https://book.douban.com/subject/1021847/";
        $ql = QueryList::getInstance();
        $ql = $ql->get($url);
        $title = $ql->find("h1 span")->text();
        $info = $ql->find("#info")->texts();
        $info = str_replace(" ",'',$info);
        $info = str_replace("\\n",'',$info);
        $info = str_replace("[\"",'',$info);
        $info = str_replace("\"]",'',$info);
        $info = self::unicodeDecode($info);
        $arr = array("出版社:","出版年:","页数:","定价:","装帧:","丛书:","ISBN:");
        $start = 0;
        $valus = array();
        for ($i=0; $i<count($arr); $i++){
            $v = $arr[$i];
            $end = strpos($info,$v);
            // 作者
            $actor = substr($info,$start,$end-$start);
            array_push($valus, $actor);
            $start = $end;
        }
        // ISBN
        $isbn = substr($info, $start, -1);
        array_push($valus, $isbn);
        $img = $ql->find(".nbg img")->src;
        $content = $ql->find(".intro p")->eq(0)->text();
        $intro = $ql->find(".indent .intro p")->eq(1)->text();

        $obj = [
            "title"=>$title,
            "img"=>$img,
            "info"=>$valus,
            "content"=>$content,
            "intro"=>$intro
        ];
        return json_success($obj);
    }
}
