<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseContent;
use App\Models\CourseMember;
use App\Models\Completion;

class CompletionController extends Controller
{
    public function store(Request $request, CourseContent $content)
    {
        $user = Auth::user();

        $courseMember = CourseMember::where('course_id', $content->course_id)
                                    ->where('user_id', $user->id)
                                    ->first();

        if (!$courseMember) {
            return response()->json(['message' => 'Unauthorized: You must be a member of this course to mark content as complete.'], 403);
        }

        try {
            $existingCompletion = Completion::where('member_id', $courseMember->id)
                                            ->where('content_id', $content->id)
                                            ->first();

            if ($existingCompletion) {
                return response()->json(['message' => 'Content already marked as complete.'], 409); // 409 Conflict
            }

            $completion = Completion::create([
                'member_id' => $courseMember->id,
                'content_id' => $content->id,
            ]);

            return response()->json([
                'message' => 'Content marked as complete successfully!',
                'completion' => $completion,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to mark content as complete', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request, CourseMember $member)
    {
        $user = Auth::user();

        if ($member->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: You can only view your own completions.'], 403);
        }

        $completions = $member->completions()->with('content.course')->get();

        return response()->json([
            'member_id' => $member->id,
            'course_id' => $member->course_id,
            'user_id' => $member->user_id,
            'completed_contents' => $completions->map(function ($completion) {
                return [
                    'completion_id' => $completion->id,
                    'content_id' => $completion->content->id,
                    'content_name' => $completion->content->name,
                    'course_name' => $completion->content->course->name,
                    'completed_at' => $completion->created_at,
                ];
            }),
        ]);
    }


    public function destroy(Completion $completion)
    {
        $user = Auth::user();

        $courseMember = $completion->member;

        if (!$courseMember || $courseMember->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: You can only delete your own completions.'], 403);
        }

        $completion->delete();

        return response()->json(['message' => 'Completion record deleted successfully!'], 200);
    }
}
