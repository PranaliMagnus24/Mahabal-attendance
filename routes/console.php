<?php

use Illuminate\Support\Facades\Schedule as ScheduleFacade;

ScheduleFacade::command('app:auto-checkout-users')
    ->everyMinute();
