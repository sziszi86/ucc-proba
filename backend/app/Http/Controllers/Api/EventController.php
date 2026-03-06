<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $events = $user->events()->orderBy('occurrence', 'asc')->get();

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'occurrence' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = auth()->user()->events()->create($request->all());

        return response()->json($event, 201);
    }

    public function show($id): JsonResponse
    {
        $event = Event::where('user_id', auth()->id())->find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json($event);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $event = Event::where('user_id', auth()->id())->find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'occurrence' => 'sometimes|required|date',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->only(['title', 'occurrence', 'description']));

        return response()->json($event);
    }

    public function destroy($id): JsonResponse
    {
        $event = Event::where('user_id', auth()->id())->find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
