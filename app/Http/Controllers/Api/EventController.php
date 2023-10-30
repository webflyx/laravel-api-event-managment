<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user', 'attendees', 'user.attendees'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index', 'show');
        $this->authorizeResource(Event::class, 'event');
    }

    public function index()
    {
        $query = $this->loadRelationships(Event::query(), $this->relations);

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
            'user_id' => $request->user()->id,
        ]);

        return new EventResource($this->loadRelationships($event, $this->relations));
    }

    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event, $this->relations));
    }

    public function update(Request $request, Event $event)
    {

        //$this->authorize('event-update', $event);

        $event->update([
            ...$request->validate([
                'name' => 'sometimes|string|min:5|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time',
            ]),
            'user_id' => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event, $this->relations));
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
    }
}
