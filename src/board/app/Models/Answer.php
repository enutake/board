<?php

namespace App\Models;

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
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function questions()
    {
        return $this->belongsTo('App\Models\Question', 'question_id', 'id');
    }

    public function tagMasters()
    {
        return $this->belongsToMany('App\Models\TagMaster', 'tag_masters', 'id', 'id');
    }
}
