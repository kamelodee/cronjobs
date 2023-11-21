<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcumaticaOrderCustomer extends Model
{
    use HasFactory;
    protected $fillable=[
        'partner_id',
        'phone_sanitized',
        'contact_address_complete',
        'name',
      
    ];
}

