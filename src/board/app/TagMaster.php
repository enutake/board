<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TagMaster extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'created_at',
        'updated_at',
    ];
    
    public function questions()
    {
        return $this->belongsToMany('App\Question', 'questions', 'id', 'id');
    }
    
    public function answers()
    {
        return $this->belongsToMany('App\Answer', 'answers', 'id', 'id');
    }
}
