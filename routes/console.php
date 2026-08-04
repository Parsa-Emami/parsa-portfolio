<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('portfolio:backup')
    ->dailyAt((string) config('production.backup.time', '02:15'))
    ->withoutOverlapping(120);

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::command('queue:prune-batches --hours=168 --unfinished=168 --cancelled=168')
    ->dailyAt('03:15')
    ->withoutOverlapping();


Schedule::command('model:prune --model=App\\Models\\ActivityLog')
    ->dailyAt('03:30')
    ->withoutOverlapping();
