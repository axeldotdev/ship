<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('auth:clear-resets')->daily();
Schedule::command('model:prune')->daily();
