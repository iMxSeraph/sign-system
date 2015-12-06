<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Workday extends Model
{
    protected $fillable = ['date', 'isworkday'];
}
