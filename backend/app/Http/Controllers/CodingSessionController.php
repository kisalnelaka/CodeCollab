<?php

namespace App\Http\Controllers;

use App\Models\CodingSession;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CodingSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Check if the user has access to the project
        if ($project->user_id !== $request->user()->id && !$project->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $codingSessions = $project->codingSessions()->latest()->get();
        
        return response()->json($codingSessions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Check if the user has access to the project
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $userId = $request->user()->id;
        
        $codingSession = $project->codingSessions()->create([
            'name' => $request->name,
            'content' => $request->content ?? '',
            'participants' => [$userId],
            'started_at' => now(),
            'is_active' => true,
        ]);
        
        return response()->json([
            'message' => 'Coding session created successfully',
            'coding_session' => $codingSession,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $codingSession = CodingSession::with('project')->findOrFail($id);
        $project = $codingSession->project;
        
        // Check if the user has access to the project
        if ($project->user_id !== $request->user()->id && !$project->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json($codingSession);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $codingSession = CodingSession::with('project')->findOrFail($id);
        $project = $codingSession->project;
        
        // Check if the user has access to the project
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'is_active']);
        
        if (isset($data['is_active']) && $data['is_active'] === false) {
            $data['ended_at'] = now();
        }
        
        $codingSession->update($data);
        
        return response()->json([
            'message' => 'Coding session updated successfully',
            'coding_session' => $codingSession,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $codingSession = CodingSession::with('project')->findOrFail($id);
        $project = $codingSession->project;
        
        // Check if the user has access to the project
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $codingSession->delete();
        
        return response()->json([
            'message' => 'Coding session deleted successfully',
        ]);
    }
    
    /**
     * Join a coding session.
     */
    public function join(Request $request, string $id)
    {
        $codingSession = CodingSession::with('project')->findOrFail($id);
        $project = $codingSession->project;
        
        // Check if the user has access to the project
        if (!$project->is_public && $project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Check if the session is active
        if (!$codingSession->is_active) {
            return response()->json(['message' => 'This coding session is no longer active'], 400);
        }
        
        $userId = $request->user()->id;
        $participants = $codingSession->participants;
        
        // Add user to participants if not already present
        if (!in_array($userId, $participants)) {
            $participants[] = $userId;
            $codingSession->participants = $participants;
            $codingSession->save();
        }
        
        return response()->json([
            'message' => 'Joined coding session successfully',
            'coding_session' => $codingSession,
        ]);
    }
    
    /**
     * Leave a coding session.
     */
    public function leave(Request $request, string $id)
    {
        $codingSession = CodingSession::findOrFail($id);
        $userId = $request->user()->id;
        $participants = $codingSession->participants;
        
        // Remove user from participants
        if (in_array($userId, $participants)) {
            $participants = array_values(array_diff($participants, [$userId]));
            $codingSession->participants = $participants;
            $codingSession->save();
        }
        
        return response()->json([
            'message' => 'Left coding session successfully',
        ]);
    }
    
    /**
     * Update the content of a coding session.
     */
    public function updateContent(Request $request, string $id)
    {
        $codingSession = CodingSession::with('project')->findOrFail($id);
        $project = $codingSession->project;
        
        // Check if the user is a participant
        $userId = $request->user()->id;
        if (!in_array($userId, $codingSession->participants)) {
            return response()->json(['message' => 'You are not a participant in this session'], 403);
        }
        
        // Check if the session is active
        if (!$codingSession->is_active) {
            return response()->json(['message' => 'This coding session is no longer active'], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $codingSession->content = $request->content;
        $codingSession->save();
        
        // Here you would typically broadcast the content update to other participants
        // using a real-time service like Pusher
        
        return response()->json([
            'message' => 'Content updated successfully',
        ]);
    }
}
