<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'price', 'category_id'
    ];

    //Данное отношение hasMany, что значит = у одного продукта мб множество категорий
    public function category()
    {
        return $this->hasMany(Category::class);
    }

    //Данное отношение hasMany, что значит = у одного продукта мб множество записей
    public function description()
    {
        return $this->hasMany(Description::class);
    }
}
