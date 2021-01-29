<?php

namespace App\Http\Controllers;

use Com\Pdd\Pop\Sdk\Api\Request\PddDdkGoodsDetailRequest;
use Com\Pdd\Pop\Sdk\Api\Request\PddDdkGoodsPromotionUrlGenerateRequest;
use Com\Pdd\Pop\Sdk\Api\Request\PddDdkGoodsRecommendGetRequest;
use Com\Pdd\Pop\Sdk\Api\Request\PddDdkGoodsSearchRequest;
use Com\Pdd\Pop\Sdk\Api\Request\PddDdkRpPromUrlGenerateRequest;
use Com\Pdd\Pop\Sdk\PopHttpClient;
use Illuminate\Http\Request;


class PddController extends Controller
{

    private static $customParam = '{"uid":"997745354","sid":"coderYJ","sub":"coderYJ"}';

    private static $pid = "9301013_115780797";

    private static $accessToken = "";

    //创建client客户端
    function init(){
        $clientId = "747036c9dd064a2eb21b781c31ca8caa";
        $clientSecret = "2904241592defc979d9016753266f5622c4d5a4b";
        $client = new PopHttpClient($clientId, $clientSecret);
        return $client;
    }
    // 备案参数
    function generate(){
        $client = self::init();

        $request = new PddDdkRpPromUrlGenerateRequest();

        $request->setChannelType(10);
        $request->setCustomParameters(self::$customParam);
//        $diyRedPacketParam = new PddDdkRpPromUrlGenerateRequest_DiyRedPacketParam();
//        $amountProbability = array();
//        $amountProbability[] = 0;
//        $diyRedPacketParam->setAmountProbability($amountProbability);
//        $diyRedPacketParam->setDisText(false);
//        $diyRedPacketParam->setNotShowBackground(false);
//        $diyRedPacketParam->setOptId(0);
//        $rangeItems = array();
//        $item = new PddDdkRpPromUrlGenerateRequest_DiyRedPacketParamRangeItemsItem();
//        $item->setRangeFrom(0);
//        $item->setRangeId(0);
//        $item->setRangeTo(0);
//        $rangeItems[] = $item;
//        $diyRedPacketParam->setRangeItems($rangeItems);
//        $request->setDiyRedPacketParam(diyRedPacketParam);
//        $request->setGenerateQqApp(false);
//        $request->setGenerateSchemaUrl(false);
//        $request->setGenerateShortUrl(false);
//        $request->setGenerateWeApp(false);
        $pIdList = array();
        $pIdList[] = self::$pid;
        $request->setPIdList($pIdList);
//        $request->setAmount(0);
//        $request->setScratchCardAmount(0);
        try{
            $response = $client->syncInvoke($request, self::$accessToken);
        } catch(Com\Pdd\Pop\Sdk\PopHttpException $e){
            echo $e->getMessage();
            exit;
        }
        $content = $response->getContent();
        if(isset($content['error_response'])){
            echo "异常返回";
        }
        echo json_encode($content,JSON_UNESCAPED_UNICODE);
    }
    // 搜索接口
    function search(Request $req)
    {
        $word = $req->input("word","女装");
        $tags = $req->input("tags",[]);
        $page = $req->input("page",1)*1;
        if ($page <= 0) $page = 1;

        $client = self::init();
        $request = new PddDdkGoodsSearchRequest();
        // 活动商品标记数组，例：[4,7]，4-秒杀，7-百亿补贴，31-品牌黑标，10564-精选爆品-官方直推爆款，
        // 10584-精选爆品-团长推荐，24-品牌高佣，20-行业精选，21-金牌商家，10044-潜力爆品，
        // 10475-爆品上新，10666-开年暖心补贴类目，其他的值请忽略
        if (is_array($tags)){
            $request->setActivityTags($tags);
        }

//        $request->setCatId(0);
        $request->setCustomParameters(self::$customParam);
//        $goodsSignList = array();
//        $goodsSignList[] = 'str';
//        $request->setGoodsSignList($goodsSignList);
//        $request->setIsBrandGoods(false);
        $request->setKeyword($word);
//        $request->setListId('str');
        $request->setMerchantType(0);
        $merchantTypeList = array();
        $merchantTypeList[] = 0;
//        $request->setMerchantTypeList($merchantTypeList);
//        $request->setOptId(0);
        $request->setPage($page);
        $request->setPageSize(21);
        $request->setPid(self::$pid);
//        $rangeList = array();
//        $item = new PddDdkGoodsSearchRequest_RangeListItem();
//        $item->setRangeFrom(0);
//        $item->setRangeId(0);
//        $item->setRangeTo(0);
//        $rangeList[] = $item;
//        $request->setRangeList($rangeList);
        // 	排序方式:0-综合排序;2-按佣金比例降序;3-按价格升序;4-按价格降序;6-按销量降序;
        //  9-券后价升序排序;10-券后价降序排序;16-店铺描述评分降序
        $request->setSortType(6);
        // 是否只返回优惠券的商品，false返回所有商品，true只返回有优惠券的商品
        $request->setWithCoupon(true);
//        $blockCats = array();
//        $blockCats[] = 0;
//        $request->setBlockCats($blockCats);
//        $blockCatPackages = array();
//        $blockCatPackages[] = 0;
//        $request->setBlockCatPackages($blockCatPackages);
        $response = $client->syncInvoke($request, self::$accessToken);
        $content = $response->getContent();
        if (isset($content['error_response'])) {
            echo "异常返回";
        }
        $obj = ["page"=>$page, "limit"=>20, "subject"=>$content];
        return json_success($obj);
    }
    // 商品推广
    function recommend(Request $req){
        $page = $req->input("page",0)*1;
        $offset = $page * 20;
        $catId = $req->input("catId",20100);
        $type = $req->input("type",5);

        $client = self::init();
        $request = new PddDdkGoodsRecommendGetRequest();
        // 猜你喜欢场景的商品类目，20100-百货，20200-母婴，20300-食品，20400-女装，20500-电器，20600-鞋包，20700-内衣，20800-美妆，
        // 20900-男装，21000-水果，21100-家纺，21200-文具,21300-运动,21400-虚拟,21500-汽车,21600-家装,21700-家具,21800-医药;
        $request->setCatId($catId);
        // 进宝频道推广商品，0-1.9包邮, 1-今日爆款, 2-品牌好货,3-相似商品推荐,4-猜你喜欢,5-实时热销榜,6-实时收益榜,
        // 7-今日热销榜,8-高佣榜单，默认值5
        $request->setChannelType($type);
        $request->setCustomParameters(self::$customParam);
//        $goodsIds = array();
//        $goodsIds[] = 0;
//        $request->setGoodsIds($goodsIds);
//        $goodsSignList = array();
//        $goodsSignList[] = 'str';
//        $request->setGoodsSignList($goodsSignList);
        $request->setLimit(20);
//        $request->setListId('str');
        $request->setOffset($offset);
        $request->setPid(self::$pid);
        try{
            $response = $client->syncInvoke($request, self::$accessToken);
        } catch(Com\Pdd\Pop\Sdk\PopHttpException $e){
            echo $e->getMessage();
            exit;
        }
        $content = $response->getContent();
        if(isset($content['error_response'])){
            echo "异常返回";
        }
        $obj = ["page"=>$page, "limit"=>20, "subject"=>$content];
        return json_success($obj);
    }
    // 商品详情
    function detail(Request $req){
        $goodsId_list = $req->input("goodsId_list",[]);
        $search_id = $req->input("search_id","");
        if (!is_array($goodsId_list)){
            return json_error("参数传递错误, 格式为 goodsId_list[]=1&goodsId_list[]=2");
        }
        $request = new PddDdkGoodsDetailRequest();
        $client = self::init();

        $request->setCustomParameters(self::$customParam);
//        $request->setGoodsSign($sign);
        $request->setGoodsIdList($goodsId_list);
        $request->setPid(self::$pid);
        $request->setSearchId($search_id);
        $request->setZsDuoId(0);
        try{
            $response = $client->syncInvoke($request, self::$accessToken);
        } catch(Com\Pdd\Pop\Sdk\PopHttpException $e){
            echo $e->getMessage();
            exit;
        }
        $content = $response->getContent();
        if(isset($content['error_response'])){
            echo "异常返回";
        }
        return json_success($content);
    }
    // 转链URL
    function promotion(Request $req){
        $search_id = $req->input("search_id","");
        $goodsId_list = $req->input("goodsId_list",[]);
        if (!is_array($goodsId_list)){
            return json_error("参数传递错误, 格式为 goodsId_list[]=1&goodsId_list[]=2");
        }
        $client = self::init();
        $request = new PddDdkGoodsPromotionUrlGenerateRequest();

//        $request->setCrashGiftId(0);
        $request->setCustomParameters(self::$customParam);
        // 是否生成带授权的单品链接。如果未授权，则会走授权流程
        $request->setGenerateAuthorityUrl(false);
        // 是否生成店铺收藏券推广链接
        $request->setGenerateMallCollectCoupon(false);
        // 是否生成qq小程序
        $request->setGenerateQqApp(false);
        // 是否返回 schema URL
        $request->setGenerateSchemaUrl(true);
        // 是否生成短链接，true-是，false-否
        $request->setGenerateShortUrl(true);
        // 是否生成小程序推广
        $request->setGenerateWeApp(true);
        // 商品goodsSign列表，支持批量生链
//        $goodsSignList = array();
//        $goodsSignList[] = 'str';
//        $request->setGoodsSignList($goods_list);

        $request->setGoodsIdList($goodsId_list);

        $request->setMultiGroup(false);
        $request->setPId(self::$pid);
//        $roomIdList = array();
//        $roomIdList[] = 'str';
//        $request->setRoomIdList($roomIdList);
        $request->setSearchId($search_id);
//        $targetIdList = array();
//        $targetIdList[] = 'str';
//        $request->setTargetIdList($targetIdList);
        $request->setZsDuoId(0);
        try{
            $response = $client->syncInvoke($request, self::$accessToken);
        } catch(Com\Pdd\Pop\Sdk\PopHttpException $e){
            echo $e->getMessage();
            exit;
        }
        $content = $response->getContent();
        if(isset($content['error_response'])){
            echo "异常返回";
        }
        return json_success($content);
    }
}
