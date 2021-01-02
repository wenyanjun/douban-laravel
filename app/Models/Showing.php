<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showing extends Model
{
    use HasFactory;
    //定义关联的数据表
    protected $table = 'showing';
    //定义主键
    protected $primaryKey = 'id';
    //设置是否允许操作时间
    public $timestamps = false;

    //设置允许批量赋值的字段
    protected $fillable = [
        'id',
        'm_id',
        'img',
        'title',
        'date',
        'plot',
        'region',
        'see',
    ];
}
