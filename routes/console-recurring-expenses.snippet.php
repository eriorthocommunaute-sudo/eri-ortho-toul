<?php

use Illuminate\Support\Facades\Schedule;

// Merge this line into routes/console.php.
Schedule::command('finance:generate-recurring-expenses')->monthlyOn(1, '01:00')->withoutOverlapping();
