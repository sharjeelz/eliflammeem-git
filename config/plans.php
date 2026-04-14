<?php

return [

    'starter' => [
        'label'        => 'Starter',
        'max_branches' => 1,
        'max_users'    => 5,
        'max_contacts' => 100,
        'features'     => [
            'ai_analysis'      => false,
            'ai_trends'        => false,
            'chatbot'          => false,
            'chatbot_daily'    => 0,
            'broadcasting'     => false,
            'whatsapp'         => false,
            'document_library' => false,
            'custom_smtp'      => false,
            'reports_full'     => false,
            'csv_export'       => false,
            'csat'             => false,
            'two_factor'       => false,
            'api_access'       => false,
            'api_daily_limit'  => 0,
        ],
    ],

    'growth' => [
        'label'        => 'Growth',
        'max_branches' => 3,
        'max_users'    => 15,
        'max_contacts' => 500,
        'features'     => [
            'ai_analysis'      => true,
            'ai_trends'        => true,
            'chatbot'          => true,
            'chatbot_daily'    => 50,
            'broadcasting'     => true,
            'whatsapp'         => false,
            'document_library' => true,
            'custom_smtp'      => true,
            'reports_full'     => true,
            'csv_export'       => true,
            'csat'             => true,
            'two_factor'       => true,
            'api_access'       => true,
            'api_daily_limit'  => 500,
        ],
    ],

    'pro' => [
        'label'        => 'Pro',
        'max_branches' => 10,
        'max_users'    => 50,
        'max_contacts' => 2000,
        'features'     => [
            'ai_analysis'      => true,
            'ai_trends'        => true,
            'chatbot'          => true,
            'chatbot_daily'    => 200,
            'broadcasting'     => true,
            'whatsapp'         => true,
            'document_library' => true,
            'custom_smtp'      => true,
            'reports_full'     => true,
            'csv_export'       => true,
            'csat'             => true,
            'two_factor'       => true,
            'api_access'       => true,
            'api_daily_limit'  => 5000,
        ],
    ],

    'enterprise' => [
        'label'        => 'Enterprise',
        'max_branches' => null, // null = unlimited
        'max_users'    => null,
        'max_contacts' => null,
        'features'     => [
            'ai_analysis'      => true,
            'ai_trends'        => true,
            'chatbot'          => true,
            'chatbot_daily'    => null, // null = unlimited
            'broadcasting'     => true,
            'whatsapp'         => true,
            'document_library' => true,
            'custom_smtp'      => true,
            'reports_full'     => true,
            'csv_export'       => true,
            'csat'             => true,
            'two_factor'       => true,
            'api_access'       => true,
            'api_daily_limit'  => null, // null = unlimited
        ],
    ],

];
