<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'category_id', 'image', 'description', 'user_id'
    ];

    protected $dates = ['deleted_at'];

    public function categories(){
        return $this->belongsToMany(Category::class, 'items_categories');
    }

    public function users(){
        return $this->belongsTo(User::class);
    }
}
