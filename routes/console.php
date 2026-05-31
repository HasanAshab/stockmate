<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('activitylog:clean --days=30 --force')->daily();
