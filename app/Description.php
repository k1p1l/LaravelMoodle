<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Description extends Model
{
    use SoftDeletes;
    // Указываем поле которе будет доступно нам
    protected $fillable = [
        'text', 'product_id'
    ];

    // Наша связь будет hasOne, что значит = у одного описания мб один продукт
    public function product()
    {
        return $this->hasOne(Product::class);
    }
}
