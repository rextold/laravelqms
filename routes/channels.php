<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('queues', function ($user) {
    return true; // Public channel for queue updates
});
