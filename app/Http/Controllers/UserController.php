<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show(User $user)
    {
        //$user->load('teachingCourses', 'enrolledCourses.course');
        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'fullname' => $user->fullname,
            // 'teaching_courses' => $user->teachingCourses,
            // 'enrolled_courses' => $user->enrolledCourses->map(function ($member) {
            //     return [
            //         'course_id' => $member->course->id,
            //         'course_name' => $member->course->name,
            //         'role' => $member->roles,
            //     ];
            // }),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        try {
            $request->validate([
                'fullname' => ['nullable', 'string', 'max:100'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:200',
                    Rule::unique('users')->ignore($user->id),
                ],
                'username' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('users')->ignore($user->id),
                ],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'], 
                //'profile_picture' => ['nullable', 'image', 'max:2048'],
            ]);

            $user->fullname = $request->fullname;
            $user->email = $request->email;
            $user->username = $request->username;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // if ($request->hasFile('profile_picture')) {
            //     $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            //     $user->profile_picture = $path;
            // }

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully!',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                    // 'profile_picture' => $user->profile_picture,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
