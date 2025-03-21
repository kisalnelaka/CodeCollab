<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $challenges = Challenge::with('creator')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('starts_at', '<=', now())
                    ->where(function ($q) {
                        $q->where('ends_at', '>=', now())
                            ->orWhereNull('ends_at');
                    });
            })
            ->latest()
            ->get();
            
        return response()->json($challenges);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'starter_code' => 'nullable|string',
            'test_code' => 'required|string',
            'points' => 'required|integer|min:1',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $challenge = $request->user()->challenges()->create([
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'starter_code' => $request->starter_code ?? '',
            'test_code' => $request->test_code,
            'points' => $request->points,
            'difficulty' => $request->difficulty,
            'starts_at' => $request->starts_at ?? now(),
            'ends_at' => $request->ends_at,
            'is_active' => $request->is_active ?? true,
        ]);
        
        return response()->json([
            'message' => 'Challenge created successfully',
            'challenge' => $challenge,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $challenge = Challenge::with(['creator', 'participants'])->findOrFail($id);
        
        return response()->json($challenge);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $challenge = Challenge::findOrFail($id);
        
        // Check if the user is the creator of the challenge
        if ($challenge->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'instructions' => 'sometimes|required|string',
            'starter_code' => 'nullable|string',
            'test_code' => 'sometimes|required|string',
            'points' => 'sometimes|required|integer|min:1',
            'difficulty' => 'sometimes|required|string|in:easy,medium,hard',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $challenge->update($request->only([
            'title', 'description', 'instructions', 'starter_code', 
            'test_code', 'points', 'difficulty', 'starts_at', 
            'ends_at', 'is_active'
        ]));
        
        return response()->json([
            'message' => 'Challenge updated successfully',
            'challenge' => $challenge,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $challenge = Challenge::findOrFail($id);
        
        // Check if the user is the creator of the challenge
        if ($challenge->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $challenge->delete();
        
        return response()->json([
            'message' => 'Challenge deleted successfully',
        ]);
    }
    
    /**
     * Submit a solution to a challenge.
     */
    public function submit(Request $request, string $id)
    {
        $challenge = Challenge::findOrFail($id);
        
        // Check if the challenge is active and within the time frame
        if (!$challenge->is_active) {
            return response()->json(['message' => 'This challenge is not active'], 400);
        }
        
        if ($challenge->starts_at > now()) {
            return response()->json(['message' => 'This challenge has not started yet'], 400);
        }
        
        if ($challenge->ends_at && $challenge->ends_at < now()) {
            return response()->json(['message' => 'This challenge has ended'], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'submission' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = $request->user();
        
        // Here you would typically run the submission against the test code
        // and calculate a score based on the results
        // For now, we'll just use a random score between 0 and the challenge points
        $score = rand(0, $challenge->points);
        $completed = $score > 0;
        
        // Attach or update the user's submission
        $challenge->participants()->syncWithoutDetaching([
            $user->id => [
                'submission' => $request->submission,
                'score' => $score,
                'completed' => $completed,
                'submitted_at' => now(),
            ]
        ]);
        
        // If the user completed the challenge, award them points
        if ($completed) {
            $user->points += $score;
            $user->save();
        }
        
        return response()->json([
            'message' => 'Solution submitted successfully',
            'score' => $score,
            'completed' => $completed,
        ]);
    }
    
    /**
     * Get the leaderboard for a challenge.
     */
    public function leaderboard(string $id)
    {
        $challenge = Challenge::findOrFail($id);
        
        $leaderboard = $challenge->participants()
            ->where('completed', true)
            ->orderBy('score', 'desc')
            ->get(['users.id', 'users.name', 'users.github_username', 'challenge_user.score', 'challenge_user.submitted_at']);
        
        return response()->json($leaderboard);
    }
}
