<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseContent;
use App\Models\CourseMember;
use App\Models\Comment;
use Illuminate\Validation\ValidationException;

class CourseContentController extends Controller
{
    public function addComment(Request $request, CourseContent $content)
    {
        $user = Auth::user();

        $isMember = CourseMember::where('course_id', $content->course_id)
                                ->where('user_id', $user->id)
                                ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Unauthorized: You must be a member of this course to comment.'], 403);
        }

        try {
            $request->validate([
                'comment' => 'required|string|max:1000',
            ]);

            $courseMember = CourseMember::where('course_id', $content->course_id)
                                        ->where('user_id', $user->id)
                                        ->first();

            $comment = Comment::create([
                'content_id' => $content->id,
                'member_id' => $courseMember->id,
                'comment' => $request->comment,
                'is_moderated' => false,
            ]);

            return response()->json([
                'message' => 'Comment added successfully! Waiting for moderation.',
                'comment' => $comment,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add comment', 'error' => $e->getMessage()], 500);
        }
    }


    public function show(CourseContent $content)
    {
        $user = Auth::user();

        $isTeacher = ($content->course->teacher_id === $user->id);
        $isMember = $content->course->members()->where('user_id', $user->id)->exists();

        if (!$isTeacher && !$isMember) {
            return response()->json(['message' => 'Unauthorized: You are not a member or teacher of this course.'], 403);
        }

        $commentsQuery = $content->comments();

        if (!$isTeacher) {
            $commentsQuery->where('is_moderated', true);
        }

        $comments = $commentsQuery->with('member.user')->get();

        return response()->json([
            'id' => $content->id,
            'name' => $content->name,
            'description' => $content->description,
            'video_url' => $content->video_url,
            'file_attachment' => $content->file_attachment,
            'course' => [
                'id' => $content->course->id,
                'name' => $content->course->name,
            ],
            'comments' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'is_moderated' => $comment->is_moderated,
                    'commenter' => [
                        'id' => $comment->member->user->id,
                        'username' => $comment->member->user->username,
                        'fullname' => $comment->member->user->fullname,
                    ],
                    'created_at' => $comment->created_at,
                ];
            }),
        ]);
    }
}
