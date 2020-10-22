<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 
        'content', 
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function users()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function tagMasters()
    {
        return $this->belongsToMany('App\Models\TagMaster', 'tag_masters', 'id', 'id');
    }
}
