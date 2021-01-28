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
use Illuminate\Support\Facades\Cache;
use QL\QueryList;
use function GuzzleHttp\Promise\all;

class BookController extends Controller
{
    function index()
    {

    }

    // top250
    function top250(Request $request)
    {
        $page = $request->input("page", 0) * 1;
        if ($page <= 0) $page = 0;
        $perPage = 25;
        $page_start = $page * $perPage;
        $data = null;
        $data = Cache::rememberForever("top250" . $page, function () use ($page_start, &$data) {
            $url = "https://www.douban.com/doulist/513669/?start=$page_start&sort=time&playable=0&sub_type=";
            $q = QueryList::getInstance();
            $q = $q->get($url);
            $data = $q->find(".article")->children(".doulist-item")->map(function ($item) {
                $mod = $item->find('.mod');
                $id = $mod->find('.post a')->href;
                preg_match('#subject/([\s\S]*?)/#', $id, $ids);
                $title = $mod->find(".title a")->text();
                $img = $mod->find('.post img')->src;
                // 评分
                $score = $mod->find('.rating .rating_nums')->text();
                // 评价人数
                $people = $mod->find('.rating span')->eq(2)->text();
                $abstract = $mod->find('.abstract')->text();

                $abstract = str_replace(array("\r\n", "\r", "\n"), "", $abstract);
                $abstract = explode('                      ', $abstract, 4);
                $time = $mod->find('.ft .time span')->text();

                return [
                    'id' => $ids[1],
                    "title" => $title,
                    "img" => $img,
                    "score" => $score,
                    "people" => $people,
                    "abstract" => $abstract,
                    "time" => $time
                ];
            })->all();
            return $data;
        });

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['order_num'] = $perPage * $page + $i;
        }
        $obj = array('total' => 250, 'limit' => $perPage, 'page' => $page, 'subject' => $data);
        return json_success($obj);
    }

    // 详情
    function info(Request $request)
    {
        $id = $request->input("id", "");
        if (empty($id)) {
            return json_error("id不能为空");
        }
        $obj = Cache::rememberForever("info" . $id, function () use ($id) {
            $url = "https://book.douban.com/subject/$id/";
            $ql = QueryList::getInstance();
            $ql = $ql->get($url);
            $title = $ql->find("h1 span")->text();
            $info = $ql->find("#info")->texts();
            $info = str_replace(" ", '', $info);
            $info = str_replace("\\n", '', $info);
            $info = str_replace("[\"", '', $info);
            $info = str_replace("\"]", '', $info);
            $info = unicodeDecode($info);
            $arr = array("出版社:", "出版年:", "页数:", "定价:", "装帧:", "丛书:", "ISBN:");
            $start = 0;
            $valus = array();
            for ($i = 0; $i < count($arr); $i++) {
                $v = $arr[$i];
                $end = strpos($info, $v);
                if ($end == false) {
                    continue;
                }
                // 作者
                $actor = substr($info, $start, $end - $start);
                array_push($valus, $actor);
                $start = $end;
            }
            // ISBN
            $isbn = substr($info, $start, -1);
            array_push($valus, $isbn);
            $img = $ql->find(".nbg img")->src;
            $content = $ql->find(".intro p")->texts();
//        $intro = $ql->find(".indent .intro p")->eq(1)->text();

            $obj = [
                "title" => $title,
                "img" => $img,
                "info" => $valus,
                "content" => $content,
            ];
            return $obj;
        });

        return json_success($obj);
    }

    // 评论接口
    function comments(Request $request)
    {
        $id = $request->input("id", "");
        $page = $request->input("page", 0) * 1;
        if ($page <= 0) $page = 0;
        $perPage = 20;
        $page_start = $page * $perPage;
        if (empty($id)) {
            return json_error("id不能为空");
        }

        $data = Cache::rememberForever("comments" . $id, function () use ($id, $page_start) {
            $url = "https://book.douban.com/subject/$id/comments/?start=$page_start&limit=20&status=P&sort=new_score";
            $ql = QueryList::getInstance();
            $ql = $ql->get($url);
            $data = $ql->find("#comments ul")->children(".comment-item")->map(function ($item) {
                $avatar = $item->find(".avatar img")->src;
                $name = $item->find(".comment-info a")->text();
                $date = $item->find(".comment-info span")->text();
                $rating = $item->find(".comment-info .rating")->attr("title");
                $content = $item->find(".comment-content span")->text();
                return [
                    'avatar' => $avatar,
                    'name' => $name,
                    "date" => $date,
                    "rating" => $rating,
                    "content" => $content
                ];
            })->all();
            return $data;
        });

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['order_num'] = $perPage * $page + $i;
        }
        $obj = array('limit' => $perPage, 'page' => $page, 'subject' => $data);
        return json_success($obj);
    }

    function search()
    {
        $url = "https://search.douban.com/book/subject_search?search_text=%E9%92%A2%E9%93%81%E6%98%AF%E6%80%8E%E6%A0%B7%E7%82%BC%E6%88%90%E7%9A%84";
        $ql = QueryList::getInstance();
        $ql = $ql->get($url);
        $data = $ql->find("._pyl31mqb1")->children(".sc-bxivhb")->map(function ($item) {
            $title = $item->find('.title a')->text();
            return [
                "title" => $title
            ];
        })->all();
        dd($data);
    }

    // 1.虚构类 2 非虚构类
    function newBook(Request $request)
    {
        $type = $request->input("type", 1) * 1;
        $title = "虚构类";
        if ($type == 2) {
            $title = "非虚构类";
        }
        $data = Cache::remember("newBook", 60 * 60 * 24, function () use ($type, $title) {
            $url = "https://book.douban.com/latest?icn=index-latestbook-all";
            $ql = QueryList::getInstance();
            $ql = $ql->get($url);
            $sel = '.article ul';
            if ($type == 2) {
                $sel = '.aside ul';
            }
            $data = $ql->find($sel)->children("li")->map(function ($item) {
                $id = $item->find(".cover")->href;
                preg_match('#subject/([\s\S]*?)/#', $id, $ids);
                $img = $item->find("img")->src;
                $title = $item->find('.detail-frame h2 a')->text();
                $score = $item->find('.detail-frame .font-small')->text();
                $gray = $item->find('.detail-frame .color-gray')->text();
                $detail = $item->find('.detail-frame p')->eq(2)->text();

                return [
                    "id" => $ids[1],
                    "title" => $title,
                    "img" => $img,
                    "score" => $score,
                    "gray" => $gray,
                    "detail" => $detail
                ];
            })->all();
            return $data;
        });
        $obj = ['type' => $title, "subject" => $data];
        return json_success($obj);
    }
}
