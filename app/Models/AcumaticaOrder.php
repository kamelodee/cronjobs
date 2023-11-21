<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcumaticaOrder extends Model
{
    use HasFactory;
    protected $fillable=[
        'website_order_line',
        'delivery_status',
        'cart_quantity',
        'picking_policy',
        'partner_id',
        'date_order',
        'delivery_count',
        'type_name',
        'name',
        'display_name',
        'amount_total',
        'state',
        'order_line',
        'warehouse_id',
        'website_id',
        'warehouse',
        'order_id',
        'x_studio_acumatica_update',
      
    ];
}


