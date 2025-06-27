<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'member_id',
        'comment',
        'is_moderated',
    ];

    public function content()
    {
        return $this->belongsTo(CourseContent::class, 'content_id');
    }

    public function member()
    {
        return $this->belongsTo(CourseMember::class, 'member_id');
    }
    
}
