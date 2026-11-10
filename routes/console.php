<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('attendance:generate-code')->daily();
Schedule::command('activity-logs:prune')->daily()->timezone('Africa/Kampala')->at('02:00');
Schedule::command('reports:remind-instructors')->weekdays()->timezone('Africa/Kampala')->at('16:00');
Schedule::command('attendance:auto-checkout')->everyFifteenMinutes()->timezone('Africa/Kampala');
