<?php

return [
    'swiftmailer' => [
        'driver' => env('MAIL_DRIVER', 'smtp'),
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'from' => [
            'name' => getenv('SMTP_FROM_NAME'),
            'address' => getenv('SMTP_FROM_ADDRESS')
        ],
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),

        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'sendmail' => '/usr/sbin/sendmail -bs',
        'pretend' => false,
    ],
];
