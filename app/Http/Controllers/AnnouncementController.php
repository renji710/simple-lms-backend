<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Announcement;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        if ($course->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: You are not the teacher of this course.'], 403);
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'publish_date' => 'nullable|date_format:Y-m-d H:i:s', // Format YYYY-MM-DD HH:MM:SS
            ]);

            $announcement = $course->announcements()->create([
                'user_id' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'publish_date' => $request->publish_date ? Carbon::parse($request->publish_date) : null,
            ]);

            return response()->json([
                'message' => 'Announcement created successfully!',
                'announcement' => $announcement,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create announcement', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Course $course)
    {
        $user = Auth::user();

        $isTeacher = ($course->teacher_id === $user->id);
        $isMember = $course->members()->where('user_id', $user->id)->exists();

        if (!$isTeacher && !$isMember) {
            return response()->json(['message' => 'Unauthorized: You are not a member or teacher of this course.'], 403);
        }

        $announcements = $course->announcements()
            ->where(function($query) use ($user, $isTeacher) {
                $query->whereNull('publish_date') 
                      ->orWhere('publish_date', '<=', Carbon::now());
                if ($isTeacher) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->orderBy('publish_date', 'desc')
            ->get();

        return response()->json([
            'course_id' => $course->id,
            'announcements' => $announcements,
        ]);
    }

    public function update(Request $request, Course $course, Announcement $announcement)
    {
        $user = Auth::user();

        if ($course->teacher_id !== $user->id || $announcement->user_id !== $user->id || $announcement->course_id !== $course->id) {
            return response()->json(['message' => 'Unauthorized: You are not authorized to edit this announcement.'], 403);
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'publish_date' => 'nullable|date_format:Y-m-d H:i:s',
            ]);

            $announcement->title = $request->title;
            $announcement->content = $request->content;
            $announcement->publish_date = $request->publish_date ? Carbon::parse($request->publish_date) : null;
            $announcement->save();

            return response()->json([
                'message' => 'Announcement updated successfully!',
                'announcement' => $announcement,
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update announcement', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Course $course, Announcement $announcement)
    {
        $user = Auth::user();

        if ($course->teacher_id !== $user->id || $announcement->user_id !== $user->id || $announcement->course_id !== $course->id) {
            return response()->json(['message' => 'Unauthorized: You are not authorized to delete this announcement.'], 403);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully!'], 200);
    }
}
