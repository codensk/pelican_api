<?php

Schedule::command('sanctum:prune-expired --hours=24')->everyFiveMinutes()->withoutOverlapping();
