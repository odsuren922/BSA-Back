<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        return Teacher::all();
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        return response()->json($teacher);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers',
            'password' => 'required|string|min:6',
        ]);

        $teacher = Teacher::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Багш амжилттай бүртгэгдлээ', 'teacher' => $teacher]);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:teachers,email,' . $teacher->id,
            'password' => 'nullable|string|min:6',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $teacher->update($validated);

        return response()->json(['message' => 'Мэдээлэл шинэчлэгдлээ', 'teacher' => $teacher]);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return response()->json(['message' => 'Багш устгагдлаа']);
    }

    // 🔍 Багшийн дэвшүүлсэн сэдвүүд
    public function getTeacherTopics($teacherId)
    {
        $topics = Topic::where('created_by_id', $teacherId)
            ->where('created_by_type', 'teacher')
            ->get();

        return response()->json($topics);
    }
}
