<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = ['website_name', 'description','email','phone','address','gst_number','logo'];
}
