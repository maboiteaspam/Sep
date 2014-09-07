<? return [
    "distrib"=>[
        "app_name"=>"",
        "intl_path"=>[
            __DIR__."/backend/intl/"
        ],
        "view_models_path"=>[
            __DIR__."/backend/view_models/"
        ],
        "www_path"=>[
            __DIR__."/backend/www/"
        ],
        "router"=>[
            'mode' => 'development',
            'debug' => !true,
            'view'=>'\Sep\View\SlimView',
            'templates.path' => [
                __DIR__."/backend/templates/"
            ],
            'log.level' => 8 //\Slim\Log::DEBUG
        ],
    ]
];