<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public function records() {
    	return $this->hasMany('App\Record', 'nid', 'nid');
    }
}
