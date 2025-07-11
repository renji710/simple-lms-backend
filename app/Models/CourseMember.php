<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'roles',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function completions()
    {
        return $this->hasMany(Completion::class, 'member_id');
    }
    
}
