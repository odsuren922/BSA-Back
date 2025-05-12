<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExternalReviewer;
use App\Http\Resources\ExternalReviewerResource;
use App\Models\Committee;
use App\Models\CommitteeMember;
class ExternalReviewerController extends Controller
{
    //
    // GET /api/external-reviewers
    public function index()
    {
        return ExternalReviewerResource::collection(ExternalReviewer::all());
    }

    // POST /api/external-reviewers
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'committee_id' => 'required|exists:committees,id',
        ]);
    
        $reviewer = ExternalReviewer::create($validated);
    
        return new ExternalReviewerResource($reviewer);
    }
    

    // GET /api/external-reviewers/{id}
    public function show(ExternalReviewer $externalReviewer)
    {
        return new ExternalReviewerResource($externalReviewer);
    }

    // PUT/PATCH /api/external-reviewers/{id}
    public function update(Request $request, ExternalReviewer $externalReviewer)
    {
        $validated = $request->validate([
            'lastname' => 'sometimes|required|string|max:255',
            'firstname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'committee_id' => 'required|exists:committees,id',
        ]);

        $externalReviewer->update($validated);

        return new ExternalReviewerResource($externalReviewer);
    }

    // DELETE /api/external-reviewers/{id}
    public function destroy(ExternalReviewer $externalReviewer)
    {
        $externalReviewer->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
