<?php

namespace App\Http\Controllers\Proposal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal\ProposalField;
use App\Http\Resources\Proposal\ProposalFieldResource;

class ProposalFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function index(Request $request)
     {
         $query = ProposalField::query();
     
         if ($request->filled('dep_id')) {
             $query->where('dep_id', $request->dep_id);
         } else {
             $user = $request->user();
             if ($user && $user->dep_id) {
                 $query->where('dep_id', $user->dep_id);
             }
         }
        // $query->where('dep_id', '1');
         $fields = $query->get();
     
         // Хоосон ч байсан [] буцаана
         return ProposalFieldResource::collection($fields);
     }

     public function activeOnly(Request $request)
     {
         $query = ProposalField::where('status', 'active');
     
         // dep_id ирсэн бол түүнд үндэслэж шүүнэ
         if ($request->filled('dep_id')) {
             $query->where('dep_id', $request->dep_id);
         }
         // Хэрвээ ирээгүй бол хэрэглэгчийн dep_id-г ашиглана
         else {
             $user = $request->user();
             if ($user && $user->dep_id) {
                 $query->where('dep_id', $user->dep_id);
             }
         }
     
         $fields = $query->get(); // ямар ч тохиолдолд [] массив буцна
     
         // Resource-оор ороосон массив буцаах
         return response()->json(
             ProposalFieldResource::collection($fields)->resolve(),
             200
         );
     }
          

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thesis_cycle_id' => 'nullable|integer|exists:thesis_cycles,id',
            'dep_id' => 'nullable|integer',
            'type' => 'required|string|in:text,textarea,date,select',
            'targeted_to' => 'required|string|in:student,teacher,both',
            'is_required' => 'boolean',
            'status' => 'required|string|in:active,inactive,archived', 
        ]);
    
        $field = ProposalField::create($validated);
    
        return new ProposalFieldResource($field);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ProposalField $proposalField)
    {
        return new ProposalFieldResource($proposalField);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProposalField $proposalField)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'thesis_cycle_id' => 'nullable|integer|exists:thesis_cycles,id',
            'dep_id' => 'nullable|integer',
            'type' => 'sometimes|string|in:text,textarea,date,select',
            'targeted_to' => 'sometimes|string|in:student,teacher,both',
            'is_required' => 'boolean',
            'status' => 'sometimes|string|in:active,inactive,archived', // ✅ нэмэгдсэн
        ]);
    
        $proposalField->update($validated);
    
        return new ProposalFieldResource($proposalField);
    }
    
    public function bulkUpsert(Request $request)
{
    $validated = $request->validate([
        'fields' => 'required|array',
        'fields.*.id' => 'nullable|integer|exists:proposal_fields,id',
        'fields.*.name' => 'required|string|max:255',
        'fields.*.name_en' => 'nullable|string|max:255',
        'fields.*.description' => 'nullable|string',
        'fields.*.dep_id' => 'required|integer|exists:departments,id',
        'fields.*.type' => 'required|string|in:text,textarea,date,select',
        'fields.*.targeted_to' => 'required|string|in:student,teacher,both',
        'fields.*.is_required' => 'boolean',
        'fields.*.status' => 'required|string|in:active,inactive,archived',
    ]);

    $results = [];

    foreach ($validated['fields'] as $fieldData) {
        // Хэрвээ ID байгаа бол шинэчлэнэ, байхгүй бол шинээр үүсгэнэ
        $field = isset($fieldData['id'])
            ? ProposalField::find($fieldData['id'])
            : new ProposalField();

        $field->fill($fieldData);
        $field->save();

        $results[] = new ProposalFieldResource($field);
    }

    return response()->json($results, 200);
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProposalField $proposalField)
    {
        $proposalField->delete();

        return response()->json(['message' => 'Field deleted.']);
    }
}
