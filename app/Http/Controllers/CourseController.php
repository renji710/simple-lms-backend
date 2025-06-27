<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CourseMember;
use App\Models\Course;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{

    public function batchEnrollStudents(Request $request, Course $course)
    {
        $user = Auth::user();

        if ($course->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: You are not the teacher of this course.'], 403);
        }

        try {
            $request->validate([
                'student_identifiers' => 'required|array',
                'student_identifiers.*' => 'required|string',
                'roles' => 'nullable|string|in:std,ast',
            ]);

            $identifiers = $request->input('student_identifiers');
            $roleToAssign = $request->input('roles', 'std');

            $enrolledCount = 0;
            $failedToEnroll = [];

            DB::beginTransaction();

            foreach ($identifiers as $identifier) {
                $studentUser = User::where('email', $identifier)
                                   ->orWhere('username', $identifier)
                                   ->first();

                if (!$studentUser) {
                    $failedToEnroll[] = ['identifier' => $identifier, 'reason' => 'User not found.'];
                    continue; 
                }

                $isAlreadyMember = CourseMember::where('course_id', $course->id)
                                                ->where('user_id', $studentUser->id)
                                                ->exists();

                if ($isAlreadyMember) {
                    $failedToEnroll[] = ['identifier' => $identifier, 'reason' => 'Already enrolled.'];
                    continue;
                }

                try {
                    CourseMember::create([
                        'course_id' => $course->id,
                        'user_id' => $studentUser->id,
                        'roles' => $roleToAssign,
                    ]);
                    $enrolledCount++;
                } catch (\Exception $e) {
                    $failedToEnroll[] = ['identifier' => $identifier, 'reason' => 'Database error: ' . $e->getMessage()];
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Batch enrollment completed.',
                'enrolled_count' => $enrolledCount,
                'failed_to_enroll' => $failedToEnroll,
            ]);

        } catch (ValidationException $e) {
            DB::rollBack(); 
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Batch enrollment failed', 'error' => $e->getMessage()], 500);
        }
    }
}
