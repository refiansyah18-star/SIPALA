<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SIPALA Configuration
    |--------------------------------------------------------------------------
    |
    | Application-specific configuration for SIPALA
    | (Sistem Pengaduan dan Layanan Akademik)
    |
    */

    'ticket_prefix' => env('SIPALA_TICKET_PREFIX', 'SIPALA'),

    'max_chat_words' => (int) env('SIPALA_MAX_CHAT_WORDS', 50),

    'default_sla_duration' => (int) env('SIPALA_DEFAULT_SLA_DURATION', 3),

    'default_sla_unit' => env('SIPALA_DEFAULT_SLA_UNIT', 'hari_kerja'),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_file_size' => 2048, // KB
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
        'allowed_mimes' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ],
        'storage_disk' => 'local',
        'storage_path' => 'ticket-attachments',
    ],

    /*
    |--------------------------------------------------------------------------
    | SLA Near Deadline Threshold
    |--------------------------------------------------------------------------
    |
    | Number of hours before deadline to consider a ticket "near deadline".
    |
    */

    'sla_near_deadline_hours' => 24,

];
