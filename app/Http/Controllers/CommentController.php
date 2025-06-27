<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\CourseContent;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // public function store(Request $request, CourseContent $content)
    // {
    //     // ...
    // }

    public function moderate(Request $request, CourseContent $content, Comment $comment)
    {
        $user = Auth::user();

        if ($comment->content_id !== $content->id) {
            return response()->json(['message' => 'Comment does not belong to this content.'], 404);
        }

        $course = $content->course;

        if (!$course || $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: You are not the teacher of this course.'], 403);
        }

        try {
            $request->validate([
                'action' => 'required|string|in:approve,reject',
            ]);

            if ($request->action === 'approve') {
                $comment->is_moderated = true;
            } else {
                $comment->is_moderated = false;
            }
            $comment->save();

            return response()->json([
                'message' => 'Comment moderated successfully!',
                'comment' => $comment,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to moderate comment', 'error' => $e->getMessage()], 500);
        }
    }

    // public function index(CourseContent $content)
    // {
    //     // ...
    // }
}
