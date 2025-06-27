<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bookmark;
use App\Models\CourseMember;
use App\Models\CourseContent;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function store(Request $request, CourseContent $content)
    {
        $user = Auth::user();

        $isMember = CourseMember::where('course_id', $content->course_id)
                                ->where('user_id', $user->id)
                                ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Unauthorized: You must be a member of this course to bookmark content.'], 403);
        }

        try {
            $existingBookmark = Bookmark::where('user_id', $user->id)
                                        ->where('content_id', $content->id)
                                        ->first();

            if ($existingBookmark) {
                return response()->json(['message' => 'Content already bookmarked.'], 409); // 409 Conflict
            }

            $bookmark = Bookmark::create([
                'user_id' => $user->id,
                'content_id' => $content->id,
            ]);

            return response()->json([
                'message' => 'Content bookmarked successfully!',
                'bookmark' => $bookmark,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to bookmark content', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $bookmarks = $user->bookmarks()->with('content.course')->get();

        return response()->json([
            'user_id' => $user->id,
            'total_bookmarks' => $bookmarks->count(),
            'bookmarks' => $bookmarks->map(function ($bookmark) {
                return [
                    'bookmark_id' => $bookmark->id,
                    'content_id' => $bookmark->content->id,
                    'content_name' => $bookmark->content->name,
                    'course_id' => $bookmark->content->course->id,
                    'course_name' => $bookmark->content->course->name,
                    'bookmarked_at' => $bookmark->created_at,
                ];
            }),
        ]);
    }

    public function destroy(Bookmark $bookmark)
    {
        $user = Auth::user();

        if ($bookmark->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: You can only delete your own bookmarks.'], 403);
        }

        $bookmark->delete();

        return response()->json(['message' => 'Bookmark deleted successfully!'], 200);
    }
}
