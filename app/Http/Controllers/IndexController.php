<?php
/**
 * @author <技术交流QQ群 277030213>
 * @since <3.0.0>
 * @GitHub https://github.com/wenyanjun/douban
 * 爬虫框架地址 http://querylist.cc/docs/api/v4/Elements-introduce
 */

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Models\MovieReviews;
use App\Models\Playing;
use App\Models\Showing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use QL\QueryList;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    public function index()
    {
        return '简书关注coderYJ 欢迎加QQ群讨论277030213';
    }
    public function delete(){
        // 每天凌晨运行一次
        // 即将上映
        $showing = Showing::all()->toArray();
        for($i=0; $i<count($showing); $i++){
            $obj = $showing[$i];
            $m_id = $obj['m_id'];
            // 删除电影评论
            MovieReviews::query()->where('m_id','=',$m_id)->delete();
            // 删除电影详情
            MovieDetail::query()->where('m_id','=',$m_id)->delete();
        }
        Showing::query()->truncate();

        // 正在上映
        $playing = Playing::all()->toArray();
        for($i = 0; $i<count($playing); $i++){
            $obj = $playing[$i];
            $m_id = $obj['m_id'];
            // 删除电影评论
            MovieReviews::query()->where('m_id','=',$m_id)->delete();
            // 删除电影详情
            MovieDetail::query()->where('m_id','=',$m_id)->delete();
        }
        Playing::query()->truncate();
    }
    public function top250(Request $request)
    {
        $page = $request->input("page", 0) * 1;

        if ($page <= 0) $page = 0;
        // 每页显示数量
        $perPage = 25;
        $page_start = $page * $perPage;
        $table = DB::table('top250');
        $data = $table->where('order_num','>=',$perPage*$page)
            ->where('order_num','<',$perPage*$page + $perPage)
            ->orderBy('order_num')
            ->limit($perPage)
            ->get()->toArray();

        if (count($data) == 0){
            $url = 'https://movie.douban.com/top250?start=' . $page_start;
            $ql = QueryList::getInstance();
            $ql = $ql->get($url);
            $data = $ql->find(".grid_view")->children("li")->map(function ($item) {
                $id = $item->find('.pic a')->href;
                preg_match('#subject/([\s\S]*?)/#', $id, $ids);
                $img = $item->find(".pic img")->attr('src');
                $info = $item->find(".info");
                $name = $info->find('.title')->eq(0)->text();
                $descript = $info->find('.bd p')->text();
                $index = strpos($descript, '主');
                $s1 = substr($descript, 0, $index);
                // php中一个汉字占用三个字节
                $s1 = substr($s1, 7);
                $s1 = trim($s1);
                // 不间断空格 chr(194).chr(160) \u00A0
                $director = str_replace(chr(194) . chr(160), "", $s1);
                $director = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $director);
                $star = $info->find('.bd .rating_num')->text();
                $quote = $info->find('.bd .inq')->text();
                return [
                    'm_id' => $ids[1],
                    'img' => $img,
                    'name' => $name,
                    'director' => $director,
                    'star' => $star,
                    'quote' => $quote
                ];
            })->all();
            for($i=0; $i<count($data); $i++){
                $data[$i]['order_num'] = $perPage * $page + $i;
            }
            $table->insert($data);
        }
        $obj = array('total' => 250, 'limit' => $perPage, 'page' => $page, 'subject' => $data);
        return json_success($obj);
    }

    // 正在上映
    public function playing(Request $request)
    {

        $city = $request->input("city", "guangzhou");

        $table = DB::table('playing');
        $data = $table->get()->toArray();

        if (count($data) == 0) {
            $url = "https://movie.douban.com/cinema/nowplaying/$city/";
            $ql = QueryList::getInstance();
            $ql = $ql->get($url);
            $data = $ql->find('#nowplaying .lists')->children('li')->map(function ($item) {
                $img = $item->find('img')->attr('src');
                return [
                    'm_id' => $item->id,
                    'title' => $item->attr('data-title'),
                    'score' => $item->attr('data-score'),
                    'star' => $item->attr('data-star'),
                    'year' => $item->attr('data-release'),
                    'duration' => $item->attr('data-duration'),
                    'region' => $item->attr('data-region'),
                    'director' => $item->attr('data-director'),
                    'actors' => $item->attr('data-actors'),
                    'votecount' => $item->attr('data-votecount'),
                    'img' => $img
                ];
            })->all();
            // 保存数据库
            $table->insert($data);
        }
        $obj = array('title' => '正在上映', 'city' => $city, 'subject' => $data);
        return json_success($obj);
    }

    // 即将上映
    public function showing(Request $request)
    {
        $city = $request->input("city", "guangzhou");
        $table = DB::table('showing');
        $data = $table->get()->toArray();
        if (count($data) == 0) {
            $url = "https://movie.douban.com/cinema/later/$city/";
            $ql = QueryList::getInstance();
            $ql = $ql->get($url);
            $data = $ql->find('#showing-soon')->children('div')->map(function ($item) {
                $img = $item->find('img')->attr('src');
                # id
                $id = $item->find('a')->attr('href');
                preg_match('#subject/([\d\D]*?)/#', $id, $ids);
                # 标题
                $title = $item->find('h3')->text();
                # 日期
                $lis = $item->find('ul')->children('li');
                $date = $lis->eq(0)->text();
                $plot = $lis->eq(1)->text();
                $region = $lis->eq(2)->text();
                $see = $lis->eq(3)->text();
                return [
                    'm_id' => $ids[1],
                    'img' => $img,
                    'title' => $title,
                    'date' => $date,
                    'plot' => $plot,
                    'region' => $region,
                    'see' => $see
                ];
            })->all();
            // 保存数据库
            $table->insert($data);
        }
        $obj = array('title' => '即将上映', 'city' => $city, 'subject' => $data);
        return json_success($obj);
    }

    /*
    status:0为正常
    Count:搜索结果数
    Data:[[Id,Img,Rating],...]
    */
    public function search(Request $request)
    {
        $q = $request->input("q", "");
        $page = $request->input("page", 0) * 1;
        if ($page <= 0) $page = 0;

        // 关键字
        $search = 'search' .$q.$page;
        $obj = null;
        $obj = Cache::remember($search,60*24*3,function () use ($page, $q, &$obj) {
            $SearchUrl = 'https://m.douban.com/j/search/?q=' . $q . '&t=movie&p=' . $page;
            $data = json_decode(self::http_get($SearchUrl), true);
            $ql = QueryList::getInstance();
            $count = $data['count'];
            $limit = $data['limit'];
            $html = $data['html'];
            $data = $ql->setHtml($html)->find('li')->children('a')->map(function ($item) {
                $id = $item->href;
                preg_match('#subject/([\d\D]*?)/#', $id, $ids);
                $img = $item->find('img')->src;
                $title = $item->find('.subject-title')->text();
                $rating = $item->find('.rating')->text();
                return [
                    'id' => $ids[1],
                    'img' => $img,
                    'title' => $title,
                    'rating' => $rating
                ];
            })->all();
            $obj = array('total' => $count, 'limit' => $limit, 'page' => $page, 'subject' => $data);
            return $obj;
        });

        return json_success($obj);
    }

    // 名人搜索
    public function People($q)
    {
//        'https://search.douban.com/movie/subject_search?search_text=%E9%BB%84%E6%B8%A4'
        $SearchUrl = 'https://movie.douban.com/j/subject_suggest?q=' . $q;
        $ApiData = json_decode(self::http_get($SearchUrl), true);
        return $ApiData;
    }

    // 详情
    public function info(Request $request)
    {
        $id = $request->input("id", '');
        if (empty($id)) {
            return json_error("id不能为空");
        }
        $table = DB::table("movie_detail");
        $data = $table->where('m_id', '=', $id)->get()->toArray();
        if (count($data) == 0) {
            $url = 'https://movie.douban.com/subject/' . $id . '/';
            $ql = QueryList::getInstance();
            try {
                $ql = $ql->get($url);
            } catch (\Exception $e) {
                return json_error("id不合法");
            }
            $title = $ql->find("#content h1 span")->eq(0)->text();
            // 图片
            $img = $ql->find("#mainpic img")->attr('src');
            // 导演
            $director = $ql->find("#info>span")->eq(0)->find('.attrs')->text();
            // 编剧
            $scriptwriter = $ql->find("#info>span")->eq(1)->find('.attrs')->texts();
            // 主演
            $actor = $ql->find("#info .actor")->find('.attrs')->texts();
            // 地区
//        $region = $ql->find("#info>.pl")->eq(1)->newInstance(null);
            // 类型
            $type = $ql->find("#info>span[property='v:genre']")->texts();
            // 上映时间
            $date = $ql->find("#info>span[property=\"v:initialReleaseDate\"]")->texts();
            // 时长
            $runtime = $ql->find("#info>span[property=\"v:runtime\"]")->text();
            // 评分
            $rating = $ql->find("#interest_sectl .rating_num")->text();
            // 描述
            $summary = $ql->find("#link-report span")->text();
            $data = array(
                'm_id' => $id,
                'title' => $title,
                'img' => $img,
                'director' => $director,
                'scriptwriter' => $scriptwriter,
                'actor' => $actor,
                'type' => $type,
                'date' => $date,
                'runtime' => $runtime,
                'rating' => $rating,
                'summary' => $summary
            );
            $table->insert($data);
        } else {
            $d = $data[0];
            $d->scriptwriter = json_decode($d->scriptwriter, true);
            $d->actor = json_decode($d->actor, true);
            $d->type = json_decode($d->type, true);
            $d->date = json_decode($d->date, true);
            $data = $d;
        }
        return json_success($data);
    }


    // 评论
    public function reviews(Request $request)
    {
        $page = $request->input("page", 0) * 1;
        $id = $request->input("id", '');
        if (empty($id)) {
            return json_error("id不能为空");
        }
        if ($page < 0) $page = 0;

        // 每页显示数量
        $perPage = 20;

        $table = DB::table('movie_reviews');
        $data = $table->where('m_id', '=', $id)
            ->where('order_num','>=',$perPage*$page)
            ->where('order_num','<',$perPage*$page + $perPage)
            ->orderBy('order_num')
            ->limit($perPage)
            ->get()->toArray();

        $total = count($data); // 查询总数
        if ($total == 0) {
            $data = $this->getReview($id, $page, $perPage);
            if ($data == null){
                return json_error("id无效");
            }
            // 先查询数据库中没有再插入
            for($i=0; $i<count($data); $i++){
                $data[$i]['order_num'] = $perPage * $page + $i;
            }
            $table->insert($data);
        }
        $obj = [
            'page' => $page,
            'limit' => $perPage,
            "subject" => $data
        ];
        return json_success($obj);
    }
    // 获取数据
    private function getReview($id, $page, $perPage){
        $url = 'https://movie.douban.com/subject/' . $id . '/comments?sort=new_score&status=P&limit='.$perPage.'&start=' . $page * $perPage;
        $ql = QueryList::getInstance();
        try {
            $ql = $ql->get($url);
        }catch (\Exception $e){
            return null;
        }
        $data = $ql->find('#comments')->children('.comment-item ')->map(function ($item) use ($id) {
            $avatar = $item->find('.avatar img')->attr('src');
            $name = $item->find('.comment-info>a')->text();
            $name = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $name);
            $rating = $item->find('.comment-info .rating')->attr('title');
            $date = $item->find('.comment-info .comment-time')->text();
            $content = $item->find('.comment-content .short')->text();
            $content = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $content);
            return [
                'm_id'=> $id,
                'avatar' => $avatar,
                "name" => $name,
                'rating' => $rating,
                'date' => $date,
                'content' => $content
            ];
        })->all();
        return $data;
    }
    // tag
    public function Get_tag($sort = 'U', $tags = '', $page = 0, $genres = '', $countries = '', $year_range = '')
    {
        //https://movie.douban.com/j/new_search_subjects?sort=U&range=0,10&tags=电影,经典&start=0&genres=剧情&countries=中国大陆&year_range=2020,2020
        //https://movie.douban.com/j/new_search_subjects?sort=U&tags=&start=0&genres=&countries=&year_range=
        //$sort = 'U', $tags = '', $page = 0, $genres = '', $countries = '', $year_range = ''
        if ($page <= 0) $page = 0;
        $page_start = $page * 20;
        $tagUrl = 'https://movie.douban.com/j/new_search_subjects?sort=' . $sort . '&range=0,10&tags=' . $tags . '&start=' . $page_start . '&genres=' . $genres . '&countries=' . $countries . '&year_range=' . $year_range;
        $apiData = json_decode(self::http_get($tagUrl), true);
        $count = count($apiData['data']);
        $returnData = array();
        for ($i = 0; $i < $count; $i++) {
            $returnData[$i]['order'] = 20 * $page + $i;
            $returnData[$i]['id'] = $apiData['data'][$i]['id'];
            $returnData[$i]['title'] = $apiData['data'][$i]['title'];
            $returnData[$i]['cover'] = $apiData['data'][$i]['cover'];
            $returnData[$i]['rate'] = $apiData['data'][$i]['rate'];
        }
        return json_success($returnData);
    }

    // 名人介绍
    public function Get_celebrity($q)
    {
        $data = self::People($q);
        $id = $data[0]['id'];

        $celebrityUrl = 'https://movie.douban.com/celebrity/' . $id . '/';
        $celebrity_Data = self::http_get($celebrityUrl);
        preg_match_all('#<h1>([\s\S]*?)<\/h1>#', $celebrity_Data, $Name);
        preg_match_all('#<span>性别<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $Sex);
        preg_match_all('#<span>星座<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $Constellation);
        preg_match_all('#<span>出生日期<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $BirthDay);
        preg_match_all('#<span>出生地<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $BirthPlace);
        preg_match_all('#<span>职业<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $Profession);
        preg_match_all('#<span>更多中文名<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $OtherName);
        preg_match_all('#<span>家庭成员<\/span>:([\s\S]*?)<\/li>#', $celebrity_Data, $FamilyMember);
        $Family_Member = preg_replace("/<a[^>]*>(.*?)<\/a>/is", "$1", $FamilyMember[1][0]);


        preg_match_all('#<span>官方网站<\/span>:([\s\S]*?)target="_blank">([\s\S]*?)<\/a>([\s\S]*?)<\/li>#', $celebrity_Data, $Website);

        $ReturnData['id'] = $id;
        $ReturnData['name'] = $Name[1][0];
        $ReturnData['sex'] = trim($Sex[1][0]);
        $ReturnData['constellation'] = trim($Constellation[1][0]);
        $ReturnData['birthDay'] = trim($BirthDay[1][0]);
        $ReturnData['birthPlace'] = trim($BirthPlace[1][0]);
        $ReturnData['profession'] = trim($Profession[1][0]);
        $ReturnData['otherName'] = trim($OtherName[1][0]);
        $ReturnData['familyMember'] = trim($Family_Member);
        $ReturnData['website'] = trim($Website[2][0]);
        if (strpos($celebrity_Data, '<span class="all hidden">') !== false) {
            preg_match_all('#<span class="all hidden">([\s\S]*?)<\/span>#', $celebrity_Data, $Brief_introduction);
            $ReturnData['brief_introduction'] = trim($Brief_introduction[1][0]);
        } else {
            preg_match_all('#影人简介([\s\S]*?)<div class="bd">([\s\S]*?)<\/div>#', $celebrity_Data, $Brief_introduction);
            $ReturnData['brief_introduction'] = trim($Brief_introduction[2][0]);
        }
        return json_success($ReturnData);
    }

    /*
        https://www.douban.com/link2/?url=
        http://www.douban.com/link2/?url=
    */
    public function DoubanUrlToUrl(&$DoubanUrl)
    {

        $DoubanUrl = str_replace('https://www.douban.com/link2/?url=', '', $DoubanUrl);
        $DoubanUrl = str_replace('http://www.douban.com/link2/?url=', '', $DoubanUrl);
        if (is_array($DoubanUrl)) {
            foreach ($DoubanUrl as $key => $val) {
                if (is_array($val)) {
                    self::DoubanUrlToUrl($DoubanUrl[$key]);
                }
            }
        }
        return $DoubanUrl;

        //$return =  str_replace('https://www.douban.com/link2/?url=', "", $DoubanUrl);
        //$return =  str_replace('http://www.douban.com/link2/?url=', "", $return);
        //return $return;
    }


    // 高分电影
    public function Movie($MovieType = '豆瓣高分', $MovieSort = 'recommend', $page_limit = '24', $page = 0)
    {
        if ($page <= 0) $page = 0;
        $page_start = $page * 24;
        $MovieUrl = 'https://movie.douban.com/j/search_subjects?type=movie&tag=' . $MovieType . '&sort=' . $MovieSort . '&page_limit=' . $page_limit . '&page_start=' . $page_start;
        $MovieData = self::http_get($MovieUrl);
        $MovieArr = json_decode($MovieData, true);
        return json_success($MovieArr);
    }

    // 热门电影
    public function Tv($TvType = '热门', $TvSort = 'recommend', $page_limit = '24', $page = 0)
    {
        if ($page <= 0) $page = 0;
        $page_start = $page * 24;
        $TvUrl = 'https://movie.douban.com/j/search_subjects?type=tv&tag=' . $TvType . '&sort=' . $TvSort . '&page_limit=' . $page_limit . '&page_start=' . $page_start;

        $TvData = self::http_get($TvUrl);
        $TvArr = json_decode($TvData, true);
        return json_success($TvArr);
    }

    // 网络请求
    public function http_get($url)
    {
        $oCurl = curl_init();
        $ip = mt_rand(11, 191) . "." . mt_rand(0, 240) . "." . mt_rand(1, 240) . "." . mt_rand(1, 240);
        $header = array(
            'CLIENT-IP:' . $ip,
            'X-FORWARDED-FOR:' . $ip,
        );
        //构造ip
        curl_setopt($oCurl, CURLOPT_USERAGENT, 'Baiduspider+(+http://www.baidu.com/search/spider.htm)');
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
        //构造IP
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("X-FORWARDED-FOR:" . $ip, 'CLIENT-IP:' . $ip));
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }


    public function GetSubstr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        $right = strpos($str, $rightStr, $left);
        if ($left < 0 or $right < $left) return '';
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    public function GetEpisodeUrl($UrlData)
    {
        if (strpos($UrlData, '<ul class="bs">') !== false) {
            #再检测 "@type": "Movie" 看是否是电影类型
            if (strpos($UrlData, '"@type": "Movie"') !== false) {
                return array('status' => 0, 'type' => 'movie', 'data' => self::GetMovieEpisodeUrl($UrlData));
            } elseif (strpos($UrlData, '"@type": "TVSeries"') !== false) {
                return array('status' => 0, 'type' => 'tv', 'data' => self::GetTVSeriesEpisodeUrl($UrlData));
            } else {
                return array('status' => 2);
            }
        } else {
            #无法播放类型，不管是电影还是电视剧
            return array('status' => 1);
        }
    }

    public function GetMovieEpisodeUrl($UrlData)
    {
        preg_match_all('#<a class="playBtn" data-cn="(.*?)"([\s\S]*?)href="(.*?)"([\s\S]*?)>#', $UrlData, $PlayList);
        for ($x = 0; $x < count($PlayList[1]); $x++) {
            $MovieEpisodeUrlData[$x]['from'] = $PlayList[1][$x];
            $MovieEpisodeUrlData[$x]['url'] = self::DoubanUrlToUrl($PlayList[3][$x]);
        }
        #print_r($MovieEpisodeUrlData);
        return $MovieEpisodeUrlData;
    }

    public function GetTVSeriesEpisodeUrl($UrlData)
    {
        #无需取JS源码类型
        if (strpos($UrlData, 'var sources = {};') !== false) {
            preg_match_all('#sources\[(.*?)\] = \[([\s\S]*?)\];#', $UrlData, $PlayUrlList);
            $i = 0;
            foreach ($PlayUrlList[2] as $a) {
                preg_match_all('#{play_link: "(.*)", ep: "#', $a, $ls);
                $PlayUrl[$i] = $ls[1];
                $i++;
            }
            return self::DoubanUrlToUrl($PlayUrl);
            #取js源码类型
        } else {
            preg_match_all('#<script type="text\/javascript" src="https:\/\/img3.doubanio.com\/misc\/mixed_static\/(.*?).js"><\/script>#', $UrlData, $JsId);
            $JsUrl = 'https://img3.doubanio.com/misc/mixed_static/' . end($JsId[1]) . '.js';
            $JsData = self::http_get($JsUrl);
            preg_match_all('#sources\[(.*?)\] = \[([\s\S]*?)\];#', $JsData, $PlayUrlList);
            $i = 0;
            foreach ($PlayUrlList[2] as $a) {
                preg_match_all('#{play_link: "(.*)", ep: "#', $a, $ls);
                $PlayUrl[$i] = $ls[1];
                $i++;
            }
            //print_r(self::DoubanUrlToUrl($PlayUrl));
            return self::DoubanUrlToUrl($PlayUrl);
        }
    }

    public function Get_recommen_dations($UrlData)
    {
        $parmsId = '#<a href="https:\/\/movie.douban.com\/subject\/(.*?)\/\?from=subject-page" class=""#';
        $parmsImg = '#<img src="(.*?)" alt="(.*?)" class=""#';

        preg_match_all($parmsId, self::GetSubstr($UrlData, '<div id="recommendations" class="">', '<div id="comments-section">'), $recommen_dation_id);
        preg_match_all($parmsImg, self::GetSubstr($UrlData, '<div id="recommendations" class="">', '<div id="comments-section">'), $recommen_dation_img);
        for ($x = 0; $x < count($recommen_dation_id[1]); $x++) {
            $recommen_dations[$x]['Id'] = $recommen_dation_id[1][$x];
            $recommen_dations[$x]['Name'] = $recommen_dation_img[2][$x];
            $recommen_dations[$x]['Img'] = $recommen_dation_img[1][$x];
        }
        return $recommen_dations;
    }
}
