<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


/**
 * @method static where(string $string, mixed $input)
 */
class EmailCodeModel extends Model
{
    use HasFactory;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mongodb';

    protected $table = 'email_code';

    protected $fillable = [
        'email',
        'v_code',
        'created_at',
        'updated_at',
        'end_time',
    ];
}
