<?php

use Illuminate\Support\Facades\Schedule;

// Generate daily attendance code at midnight
Schedule::command('attendance:generate-code')->daily();
