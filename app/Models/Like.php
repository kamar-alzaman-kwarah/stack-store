<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $table = 'likes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'product_id',
        'like'
    ];

    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }

    public function product(){
        return $this->belongsTo(product::class , 'product_id');
    }
}
