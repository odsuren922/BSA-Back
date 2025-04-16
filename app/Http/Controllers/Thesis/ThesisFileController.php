<?php

namespace App\Http\Controllers\Thesis;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Models\Thesis;
use App\Models\ThesisFile;


class ThesisFileController extends Controller
{
    //
    public function index($id)
{
    $files = ThesisFile::where('thesis_id', $id)->get();
    return response()->json($files);
}
public function store(Request $request, $id)
{
    $request->validate([
        'file' => 'required|mimes:pdf|max:10240', // 10MB
    ]);

    // Get latest file
    $existingFile = ThesisFile::where('thesis_id', $id)
        ->latest()
        ->first();

    // If last file exists & not accepted → delete
    if ($existingFile && $existingFile->status !== 'accepted') {
        Storage::disk('public')->delete($existingFile->file_path);
        $existingFile->delete();
    }

    $path = $request->file('file')->store('thesis-files', 'public');

    $file = ThesisFile::create([
        'thesis_id' => $id,
        'file_path' => $path,
        'original_name' => $request->file('file')->getClientOriginalName(),
        'uploaded_by' => auth()->user()->id,
        'status' => 'pending', // or your default status
    ]);

    return response()->json(['message' => 'Амжилттай илгээлээ', 'file' => $file]);
}



// public function store(Request $request, $id)
// {
//     $request->validate([
//         'file' => 'required|mimes:pdf|max:10240', // 10MB
//     ]);

//     $path = $request->file('file')->store('thesis-files', 'public');

//     $file = ThesisFile::create([
//         'thesis_id' => $id,
//         'file_path' => $path,
//         'original_name' => $request->file('file')->getClientOriginalName(),
//         'uploaded_by' => auth()->user()->id, // if student
//     ]);

//     return response()->json(['message' => 'Амжилттай илгээлээ', 'file' => $file]);
// }


public function approve(Request $request, $fileId)
{
    $file = ThesisFile::findOrFail($fileId);

    $file->update([
        'status' => 'accepted',
        'approved_by' => $request->approved_by, // get from frontend
        'approved_at' => now(),
    ]);

    return response()->json(['message' => 'File accepted successfully']);
}


public function reject($fileId)
{
    $file = ThesisFile::findOrFail($fileId);

    $file->update([
        'status' => 'rejected',
        'approved_by' => auth()->user()->id,
        'approved_at' => now(),
    ]);

    return response()->json(['message' => 'File rejected']);
}
public function destroy($id)
{
    // Find the file or fail with 404
    $file = ThesisFile::findOrFail($id);

    // Delete the file from storage
    Storage::disk('public')->delete($file->file_path);

    // Delete the database record
    $file->delete();

    return response()->json([
        'message' => 'Файл амжилттай устгагдлаа',
    ]);
}



}
