<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'video_url',
        'file_attachment',
        'course_id',
        'parent_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function parent()
    {
        return $this->belongsTo(CourseContent::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CourseContent::class, 'parent_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'content_id');
    }

    public function completions()
    {
        return $this->hasMany(Completion::class, 'content_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'content_id');
    }
}
