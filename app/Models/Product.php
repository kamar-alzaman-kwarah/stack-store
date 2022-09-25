<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'price', 'details', 'image_url', 'quantity', 'expiration_date',
        'period_one', 'period_two', 'discounts_one', 'discounts_two',
        'discounts_three', 'category_id', 'user_id', 'views', 'like' , 'current_price'
    ];

    protected $hidden = [
        'period_one', 'period_two', 'discounts_one', 'discounts_two',
        'discounts_three', 'category_id', 'user_id',
    ];

    public $with = ['category' , 'comments'];

    public $withCount = ['comments' , 'likes'];

    public function category(){
        return $this->belongsTo(category::class , 'category_id');
    }

    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }

    public function comments(){
        return $this->hasMany(Comment::class ,'product_id');
    }

    public function likes(){
        return $this->hasMany(Like::class ,'product_id');
    }

    public function discounts(){
        return $this->hasMany(discount::class ,'product_id')->orderBy('discount_date');
    }
}
