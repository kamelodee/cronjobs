<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcumaticaOrderItem extends Model
{
    use HasFactory;
    protected $fillable=[
        'product_qty',
        'qty_to_deliver',
        'product_packaging_qty',
        'price_total',
        'product_template_id',
        'product_template_name',
        'name',
        'order_id',
      
    ];
}
