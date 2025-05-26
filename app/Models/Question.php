<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function users(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Models\Answer');
    }

    public function tagMasters(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\TagMaster', 'tag_masters', 'id', 'id');
    }
}
