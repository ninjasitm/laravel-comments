<?php

namespace BeyondCode\Comments\Traits;


trait CanComment
{
    /**
     * Check if a comment for a specific model needs to be approved.
     * @param mixed $model
     * @return bool
     */
    public function needsCommentApproval($model): bool
    {
        return true;
    }

    /**
     * Return all comments for this model.
     *
     * @return MorphMany
     */
    public function comments()
    {
        return $this->hasMany(config('comments.comment_class'), 'user_id');
    }

    /**
     * Return all approved comments for this model.
     *
     * @return MorphMany
     */
    public function approvedComments()
    {
        return $this->comments()->approved();
    }

    /**
     * Return all approved comments for this model.
     *
     * @return MorphMany
     */
    public function disApprovedComments()
    {
        return $this->comments()->disapproved();
    }
}