<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    function createdBy(){
        return $this->hasOne(User::class,'id');
    }

    function childTags(){
        return $this->hasMany(Tag::class,'parent_id');
    }
}
