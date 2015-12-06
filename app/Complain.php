<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    protected $fillable = ['type', 'eid', 'startdate', 'enddate', 'description', 'token'];

    public function employee()
    {
        return $this->hasOne('App\Employee', 'eid', 'eid');
    }
}
