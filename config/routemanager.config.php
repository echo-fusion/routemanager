<?php

return array(
    'health_check' => [
        'method' => 'GET',
        'path' => '/health-check',
        'action' => fn() => 'healthy',
        'constraints' => [],
        'middlewares' => [],
    ],
);