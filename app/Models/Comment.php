<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'comment',
        'user_id',
        'product_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public $with=['user'];

    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }

    public function product(){
        return $this->belongsTo(product::class , 'product_id');
    }

    
}
