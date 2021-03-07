<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\PddController;
use App\Http\Controllers\WxController;
use App\Http\Controllers\ZuFangController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});
// 电影
Route::prefix('/movie')->group(function (){
    Route::get('/top250', [IndexController::class, "top250"]);
    Route::get('/playing',[IndexController::class,'playing']);
    Route::get('/showing',[IndexController::class,'showing']);
    // search 搜索
    Route::get("search",[IndexController::class,'search']);
    // info 影片信息
    Route::get("info",[IndexController::class,'info']);
    // review 影片评论
    Route::get("reviews", [IndexController::class,'reviews']);
    Route::get("delete",[IndexController::class, 'delete']);
});
// 微信
Route::prefix('/wx')->group(function (){
    // 微信相关
    Route::post("upload",[WxController::class, 'uploadImage']);
    // 登录
    Route::get("login",[WxController::class, 'login']);
});
// 图书
Route::prefix('/book')->group(function (){
    Route::get("top250",[BookController::class,"top250"]);
    Route::get("info",[BookController::class,"info"]);
    Route::get("comments",[BookController::class,"comments"]);
    Route::get("search",[BookController::class,'search']);
    Route::get("newBook",[BookController::class,'newBook']);
});
// 拼多多
Route::prefix("pdd")->group(function(){
    // 搜索
    Route::get("search",[PddController::class,"search"]);
    // 热销
    Route::get("recommend",[PddController::class,"recommend"]);
    // 备案
    Route::get("generate",[PddController::class,"generate"]);
    // 商品详情
    Route::get("detail",[PddController::class,"detail"]);
    // 转链
    Route::get("promotion",[PddController::class,'promotion']);
});
// 租房
Route::prefix("zufang")->group(function(){
    Route::get("/",[ZuFangController::class, "Index"]);
    Route::get("/detail",[ZuFangController::class, "detail"]);
});

//// 名人介绍
//Route::get("celebrity",'index/Index/Get_celebrity');
//// tag
//Route::get("tag",'index/Index/Get_tag');
//// 艺人搜索
//Route::get("people",'index/Index/People');
//// 高分电影
//Route::get("movie",'index/Index/Movie');
//// 热门电影
//Route::get("tv",'index/Index/Tv');

// 路由兜底
Route::fallback([IndexController::class, "index"]);
