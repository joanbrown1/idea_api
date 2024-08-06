<?php

namespace App\Http\Controllers;

use App\Models\Ideas;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class IdeaController extends Controller
{
    protected $encryptionKey;

    public function __construct()
    {
        $this->encryptionKey = env('JWT_KEY');
    }

    public function allIdeas(Request $request)
    {
        $ideas = Ideas::all();
        foreach ($ideas as $idea) {
            $idea->picture = $idea->picture ? 'https://bloomx.live/' . $idea->picture : null;
            $idea->proposal = $idea->proposal ? 'https://bloomx.live/' . $idea->proposal : null;
        }

        return response(['data' => $ideas], 200);
    }

    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string',
            'category' => 'nullable|string',
            'division' => 'nullable|string',
            'innovation' => 'nullable|string',
            'improvement' => 'nullable|string',
            'problem' => 'nullable|string',
            'scope' => 'nullable|string',
            'effectuate' => 'nullable|string',
            'others' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'proposal' => 'nullable|mimes:pdf,doc,docx|max:10240',
            'description' => 'required|string',
            'status' => 'nullable|string',
            'submitted_name' => 'required|string',
            'submitted_department' => 'nullable|string',
            'submitted_zone' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $picturePath = $request->file('picture') ? $request->file('picture')->store('images', env('DEFAULT_DISK')) : null;
        $filePath = $request->file('proposal') ? $request->file('proposal')->store('proposals', env('DEFAULT_DISK')) : null;

        $idea = new Ideas([
            'user_id' => $request->input('user_id'),
            'name' => $request->input('name'),
            'category' => $request->input('category'),
            'division' => $request->input('division'),
            'innovation' => $request->input('innovation'),
            'improvement' => $request->input('improvement'),
            'problem' => $request->input('problem'),
            'effectuate' => $request->input('effectuate'),
            'others' => $request->input('others'),
            'picture' => $picturePath,
            'proposal' => $filePath,
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'submitted_name' => $request->input('submitted_name'),
            'submitted_department' => $request->input('submitted_department'),
            'submitted_zone' => $request->input('submitted_zone'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date')
        ]);
        $idea->save();

        return response(['message' => 'Idea successfully created!'], 200);
    }

    public function IdeasByUserId(Request $request, $user_id)
    {
        $idea = Ideas::where('user_id', $user_id)->get();
        if ($idea->isNotEmpty()) {
            return response(['data' => $idea], 200);
        } else {
            return response(['message' => 'No ideas for this user!'], 400);
        }
    }

    public function IdeasById(Request $request, $id)
    {
        $idea = Ideas::where('id', $id)->first();
        if ($idea) {
            return response(['data' => $idea], 200);
        } else {
            return response(['message' => 'This idea does not exist!'], 400);
        }
    }

    public function IdeasByDepartment(Request $request, $department)
    {
        $idea = Ideas::where('department', $department)->get();
        if ($idea->isNotEmpty()) {
            return response(['data' => $idea], 200);
        } else {
            return response(['message' => 'The department has no ideas saved!'], 400);
        }
    }

    public function IdeasByZone(Request $request, $zone)
    {
        $idea = Ideas::where('zone', $zone)->get();
        if ($idea->isNotEmpty()) {
            return response(['data' => $idea], 200);
        } else {
            return response(['message' => 'The zone has no ideas saved!'], 400);
        }
    }

    public function IdeasByCategory(Request $request, $category)
    {
        $idea = Ideas::where('category', $category)->get();
        if ($idea->isNotEmpty()) {
            return response(['data' => $idea], 200);
        } else {
            return response(['message' => 'No ideas saved in this category!'], 400);
        }
    }

    public function IdeasByDivision(Request $request, $division)
    {
        $idea = Ideas::where('division', $division)->get();
        if ($idea->isNotEmpty()) {
            return response(['data' => $idea], 200);
        } else {
            return response(['message' => 'No ideas saved in this division!'], 400);
        }
    }


    public function update(Request $request, $id)
    {
        $idea = Ideas::find($id);
        if (!$idea) {
            return response(['message' => 'Idea not found'], 404);
        }

        $data = $request->only([
            'user_id', 'name', 'category', 'division', 'innovation',
            'improvement', 'problem', 'effectuate', 'others', 'picture',
            'proposal', 'description', 'status', 'submitted_name',
            'submitted_department', 'submitted_zone', 'start_date', 'end_date'
        ]);

        if ($request->hasFile('picture')) {
            // Delete the old picture if it exists
            if ($idea->picture) {
                Storage::disk(env('DEFAULT_DISK'))->delete($idea->picture);
            }
            $data['picture'] = $request->file('picture')->store('images', env('DEFAULT_DISK'));
        }

        if ($request->hasFile('proposal')) {
            // Delete the old proposal if it exists
            if ($idea->proposal) {
                Storage::disk(env('DEFAULT_DISK'))->delete($idea->proposal);
            }
            $data['proposal'] = $request->file('proposal')->store('proposals', env('DEFAULT_DISK'));
        }

        $idea->update(array_filter($data, fn($value) => !is_null($value)));

        return response(['message' => 'Idea updated successfully', 'data' => $idea], 200);
    }
}
