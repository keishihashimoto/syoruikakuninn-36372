<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoPay extends Model
{
    use HasFactory;

    public function memo(){
        return $this->belongsTo("App\Models\Memo");
    }
}
