<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OldData extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CHECKINOUT';
    protected $primaryKey = null;
    public $timestamps = false;
}
