<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 
        'user_id',
        'question_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function users()
    {
        return $this->belongsTo('App\User');
    }

    public function answers()
    {
        return $this->belongsTo('App\Question');
    }

    public function tagMasters()
    {
        return $this->belongsToMany('App\TagMaster', 'tag_masters', 'id', 'id');
    }
}
