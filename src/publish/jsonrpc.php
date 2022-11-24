<?php

return array(
    'default' => [
        'HOST' => 'localhost',
        'PORT' => 8084,
        'PATH' => '/api/',
        'CLASS' => 'ApiService',
    ],
    FooService::class => [
        'HOST' => 'localhost',
        'PORT' => 8084,
        'PATH' => '',
        'CLASS' => 'ApiService',
    ],
);
