<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifying users about an upcoming event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')
            ->whereBetween('start_time', [now(), now()->addDay()]);

        
        $eventsCount = $events->count();
        $eventsLabel = Str::plural('event', $eventsCount);
        $this->info("Found {$eventsCount} {$eventsLabel}" );

        $events->each(function (Event $event) {
            $event->attendees->each(function (Attendee $attendee) {
                $this->info("User {$attendee->user->id} has beed notified!");
            });
        });

        $this->info('All users successfully notified!');
    }
}
