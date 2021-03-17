<?php

namespace BeyondCode\Comments;

use Exception;
use Illuminate\Database\Eloquent\Model;
use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Database\Eloquent\SoftDeletes;
use BeyondCode\Comments\Contracts\Comment as CommentContract;

class Comment extends Model implements CommentContract
{
    use HasComments;
    use SoftDeletes;

    protected $fillable = [
        'comment',
        'user_id',
        'is_approved',
        'commentable_id',
        'commentable_type'
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'commentable_type' => 'string',
        'commentable_id' => 'integer',
        'comment' => 'string',
        'is_approved' => 'boolean',
        'user_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'commentable_type' => 'sometimes|string|max:255',
        'commentable_id' => 'sometimes',
        'comment' => 'required|string',
        'is_approved' => 'sometimes|boolean',
        'user_id' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];


    /**
     * The "booting" method of the model.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($comment) {
                if (auth()->check()) {
                    $comment->user_id = $comment->user_id ?? auth()->id();
                }
            }
        );
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeDisapproved($query)
    {
        return $query->where('is_approved', false);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function commentator()
    {
        return $this->belongsTo($this->getAuthModelName(), 'user_id');
    }

    public function commenter()
    {
        return $this->commentator();
    }

    public function approve()
    {
        $this->update([
            'is_approved' => true,
        ]);

        return $this;
    }

    public function disapprove()
    {
        $this->update([
            'is_approved' => false,
        ]);

        return $this;
    }

    public function scopeForModel($query, Model $model)
    {
        $query->where([
            'commentable_type' => get_class($model),
            'commentable_id' => $model->id ?? -1
        ]);
    }

    protected function getAuthModelName()
    {
        if (config('comments.user_model')) {
            return config('comments.user_model');
        }

        if (!is_null(config('auth.providers.users.model'))) {
            return config('auth.providers.users.model');
        }

        throw new Exception('Could not determine the commentator model name.');
    }
}