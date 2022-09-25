<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id',
        'discount_date',
        'discount_value'
    ];

    public function product(){
        return $this->belongsTo(product::class , 'product_id');
    }
}
