<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


// 充值汇率
class ExchangeModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'exchange_rate';

    protected $fillable = [
        'name', // 设定名称
        'nation_code', // 货币代号
        'diamond_quantity', // 钻石兑换比例数量 1 ： x
        'status', // 状态
        'created_at',
        'updated_at',
    ];
}
