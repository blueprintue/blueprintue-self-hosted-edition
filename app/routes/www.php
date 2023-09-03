<?php

declare(strict_types=1);

$config = [
    'routes' => [
        // region home
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/',
            'callback'    => app\controllers\www\HomeController::class,
            'name'        => 'home',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        // endregion
        // region blueprint
        [
            'methods'              => ['GET', 'POST'],
            'url'                  => '/blueprint/{blueprint_slug}/{version:\d+}/',
            'callback'             => app\controllers\www\BlueprintController::class,
            'name'                 => 'blueprint',
            'optionals_parameters' => [
                'version' => 'last',
            ],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/blueprint/{blueprint_slug}/edit/',
            'callback'    => app\controllers\www\BlueprintEditController::class,
            'name'        => 'blueprint-edit',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
            ]
        ],
        [
            'methods'              => ['GET'],
            'url'                  => '/render/{blueprint_slug}/{version:\d+}/',
            'callback'             => app\controllers\www\RenderController::class,
            'name'                 => 'render',
            'optionals_parameters' => [
                'version' => 'last',
            ],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/blueprint/{blueprint_slug}/{previous_version:\d+}/diff/{current_version:\d+}/',
            'callback'    => app\controllers\www\BlueprintDiffController::class,
            'name'        => 'blueprint-diff',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        // endregion
        // region profile
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/profile/{profile_slug}/',
            'callback'    => app\controllers\www\ProfileController::class,
            'name'        => 'profile',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/profile/{profile_slug}/edit/',
            'callback'    => app\controllers\www\ProfileEditController::class,
            'name'        => 'profile-edit',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
            ]
        ],
        // endregion
        // region search
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/search/',
            'callback'    => app\controllers\www\BlueprintListController::class,
            'name'        => 'search',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        // endregion
        // region listing
        [
            'methods'              => ['GET', 'POST'],
            'url'                  => '/last-blueprints/{page:\d+}/',
            'callback'             => app\controllers\www\BlueprintListController::class,
            'name'                 => 'last-blueprints',
            'optionals_parameters' => [
                'page' => 1
            ],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'              => ['GET', 'POST'],
            'url'                  => '/most-discussed-blueprints/{page:\d+}/',
            'callback'             => app\controllers\www\BlueprintListController::class,
            'name'                 => 'most-discussed-blueprints',
            'optionals_parameters' => [
                'page' => 1
            ],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'              => ['GET', 'POST'],
            'url'                  => '/type/{type}/{page:\d+}/',
            'callback'             => app\controllers\www\BlueprintListController::class,
            'name'                 => 'type-blueprints',
            'optionals_parameters' => [
                'page' => 1
            ],
            'constraints' => ['type' => 'animation|behavior-tree|blueprint|material|metasound|niagara'],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'              => ['GET', 'POST'],
            'url'                  => '/tag/{tag_slug}/{page:\d+}/',
            'callback'             => app\controllers\www\BlueprintListController::class,
            'name'                 => 'tag-blueprints',
            'optionals_parameters' => [
                'page' => 1
            ],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'              => ['GET', 'POST'],
            'url'                  => '/tags/',
            'callback'             => app\controllers\www\TagsController::class,
            'name'                 => 'tags',
            'optionals_parameters' => [
                'page' => 1
            ],
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        // endregion
        // region pages contact / terms of service / privacy policy
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/contact/',
            'callback'    => app\controllers\www\ContactController::class,
            'name'        => 'contact',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/{pageID}/',
            'constraints' => ['pageID' => 'terms-of-service|privacy-policy'],
            'callback'    => app\controllers\www\StaticController::class,
            'name'        => 'static-pages',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
                \app\middlewares\LogoutMiddleware::class,
                \app\middlewares\LoginMiddleware::class,
                \app\middlewares\ForgotPasswordMiddleware::class,
                \app\middlewares\RegisterMiddleware::class,
            ]
        ],
        // endregion
        // region upload
        [
            'methods'     => ['POST'],
            'url'         => '/upload/{entity}/{entityID:\d+}/{subEntity}/',
            'constraints' => [
                'entity'    => 'user|blueprint',
                'subEntity' => 'avatar|thumbnail',
            ],
            'callback'    => app\controllers\www\UploadController::class,
            'name'        => 'upload-image',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
            ]
        ],
        // endregion
        // region error
        [
            'methods'     => ['GET'],
            'url'         => '/error/',
            'callback'    => app\controllers\www\ErrorController::class,
            'name'        => 'error',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
            ]
        ],
        // endregion
        // region reset password
        [
            'methods'     => ['GET', 'POST'],
            'url'         => '/reset-password/',
            'callback'    => app\controllers\www\ResetPasswordController::class,
            'name'        => 'reset-password',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
            ]
        ],
        // endregion
        // region confirm account
        [
            'methods'     => ['GET'],
            'url'         => '/confirm-account/',
            'callback'    => app\controllers\www\ConfirmAccountController::class,
            'name'        => 'confirm-account',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
            ]
        ],
        // endregion
        // region api legacy
        [
            'methods'     => ['POST'],
            'url'         => '/api/upload',
            'callback'    => app\controllers\www\APIController::class,
            'name'        => 'api_upload',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
            ]
        ],
        [
            'methods'     => ['POST'],
            'url'         => '/api/render',
            'callback'    => app\controllers\www\APIController::class,
            'name'        => 'api_render',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
            ]
        ],
        // endregion
        // region crons http
        [
            'methods'     => ['GET'],
            'url'         => '/cron/purge_sessions/',
            'callback'    => app\controllers\www\CronController::class,
            'name'        => 'cron_purge_sessions',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
                \app\middlewares\SessionMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET'],
            'url'         => '/cron/purge_users_not_confirmed/',
            'callback'    => app\controllers\www\CronController::class,
            'name'        => 'cron_purge_users_not_confirmed',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET'],
            'url'         => '/cron/purge_deleted_blueprints/',
            'callback'    => app\controllers\www\CronController::class,
            'name'        => 'cron_purge_deleted_blueprints',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
            ]
        ],
        [
            'methods'     => ['GET'],
            'url'         => '/cron/set_soft_delete_anonymous_private_blueprints/',
            'callback'    => app\controllers\www\CronController::class,
            'name'        => 'cron_set_soft_delete_anonymous_private_blueprints',
            'middlewares' => [
                \app\middlewares\DatabaseMiddleware::class,
            ]
        ]
        // endregion
    ]
];
$subRouter = new \Rancoud\Router\Router();
$subRouter->setupRouterAndRoutesWithConfigArray($config);
/* @var \Rancoud\Router\Router $router */
$router->any('/(.*)', $subRouter);
