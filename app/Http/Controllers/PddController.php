<?php


namespace App\Http\Controllers;

//require_once dirname(__FILE__).'/Config.php';
//require_once dirname(__FILE__)."/../vendor/autoload.php";


use Com\Pdd\Pop\Sdk\Api\Request\PddDdkGoodsRecommendGetRequest;
use Com\Pdd\Pop\Sdk\Api\Request\PddDdkGoodsSearchRequest;
use Com\Pdd\Pop\Sdk\Api\Request\PddDdkRpPromUrlGenerateRequest;
use Com\Pdd\Pop\Sdk\PopHttpClient;


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
    function search()
    {
        $client = self::init();
        $request = new PddDdkGoodsSearchRequest();
//        $activityTags = array();
//        $activityTags[] = 0;
//        $request->setActivityTags($activityTags);
//        $request->setCatId(0);
        $request->setCustomParameters(self::$customParam);
//        $goodsSignList = array();
//        $goodsSignList[] = 'str';
//        $request->setGoodsSignList($goodsSignList);
//        $request->setIsBrandGoods(false);
        $request->setKeyword('女装');
//        $request->setListId('str');
        $request->setMerchantType(0);
        $merchantTypeList = array();
        $merchantTypeList[] = 0;
//        $request->setMerchantTypeList($merchantTypeList);
//        $request->setOptId(0);
        $request->setPage(1);
        $request->setPageSize(20);
        $request->setPid(self::$pid);
//        $rangeList = array();
//        $item = new PddDdkGoodsSearchRequest_RangeListItem();
//        $item->setRangeFrom(0);
//        $item->setRangeId(0);
//        $item->setRangeTo(0);
//        $rangeList[] = $item;
//        $request->setRangeList($rangeList);
//        $request->setSortType(0);
        $request->setWithCoupon(false);
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
        echo json_encode($content, JSON_UNESCAPED_UNICODE);
    }
    function recommend(){
        $client = self::init();
        $request = new PddDdkGoodsRecommendGetRequest();

//        $request->setCatId(0);
//        $request->setChannelType(0);
        $request->setCustomParameters('coderYJ');
//        $goodsIds = array();
//        $goodsIds[] = 0;
//        $request->setGoodsIds($goodsIds);
//        $goodsSignList = array();
//        $goodsSignList[] = 'str';
//        $request->setGoodsSignList($goodsSignList);
        $request->setLimit(20);
//        $request->setListId('str');
        $request->setOffset(0);
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
        echo json_encode($content,JSON_UNESCAPED_UNICODE);
    }
}
