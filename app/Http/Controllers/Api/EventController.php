<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    
    protected function withIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');
        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations);
    }

    public function index()
    {
        $query = Event::query();

        $access_relations = ['user', 'attendees', 'user.attendees'];

        foreach( $access_relations as $relation ) {
            $query->when(
                $this->withIncludeRelation($relation),
                fn($q) => $q->with($relation)
            );
        }

        return EventResource::collection($query->latest()->paginate());
    }

    public function store(Request $request)
    {
        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|min:5|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
            ]),
            'user_id' => 1,
        ]);

        return $event;
    }

    public function show(Event $event)
    {
        $event->load(['user','attendees']);
        return new EventResource($event);
    }

    public function update(Request $request, Event $event)
    {
        $event->update([
            ...$request->validate([
                'name' => 'sometimes|string|min:5|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time',
            ]),
            'user_id'=> 1,
        ]);

        return $event;
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
    }
}
