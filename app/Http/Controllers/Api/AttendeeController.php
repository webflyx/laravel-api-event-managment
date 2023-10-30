<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;

class AttendeeController extends Controller
{

    use CanLoadRelationships;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index', 'show');
        $this->authorizeResource(AttendeeResource::class, 'attendee');
    }
    
    private array $relations = ['user'];

    public function index(Event $event)
    {
        $attendees = $this->loadRelationships($event->attendees()->latest(), $this->relations);

        return AttendeeResource::collection($attendees->paginate());
    }

    public function store(Event $event, Request $request)
    {
        return $event->attendees()->create([
            'user_id' => 1,
        ]);
    }

    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee, $this->relations));
    }

    public function destroy(Event $event, Attendee $attendee)
    {
        $this->authorize('attendee-delete', [$event, $attendee]);

        $attendee->delete();
        return response(status: 204);
    }
}
