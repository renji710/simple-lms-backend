<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Completion extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'content_id',
    ];

    public function member()
    {
        return $this->belongsTo(CourseMember::class);
    }

    public function content()
    {
        return $this->belongsTo(CourseContent::class);
    }
}
