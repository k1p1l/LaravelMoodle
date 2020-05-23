<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'id', 'title'
        ];

    public function items(){
        return $this->belongsToMany(Item::class, 'items_categories');
    }
}
