<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue backlog threshold
    |--------------------------------------------------------------------------
    |
    | When the number of queued jobs exceeds this value the health-check
    | endpoint will return a degraded status to highlight delayed processing.
    |
    */
    'queue_backlog_threshold' => env('MONITORING_QUEUE_BACKLOG_THRESHOLD', 50),
];





