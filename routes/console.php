<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('attendance:generate-code')->daily();
Schedule::command('reports:remind-instructors')->weekdays()->timezone('Africa/Kampala')->at('16:00');
