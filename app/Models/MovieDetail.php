<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieDetail extends Model
{
    use HasFactory;
    //定义关联的数据表
    protected $table = 'movie_detail';
    //定义主键
    protected $primaryKey = 'id';
    //设置是否允许操作时间
    public $timestamps = false;

    //设置允许批量赋值的字段
    protected $fillable = [
        'id',
        'title',
        'img',
        'scriptwriter',
        'director',
        'actor',
        'type',
        'date',
        'runtime',
        'rating',
        'summary',
        'm_id',
        'm_type'
    ];
}
