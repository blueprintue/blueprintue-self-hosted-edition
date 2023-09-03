<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Profile\Edit;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class ProfileEditPOSTEditSocialsTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     * @throws \Rancoud\Crypt\CryptException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :avatar);
        SQL;

        $userParams = [
            'id'       => 189,
            'username' => 'user_189',
            'hash'     => Crypt::hash('password_user_189'),
            'slug'     => 'user_189',
            'email'    => 'user_189@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 195,
            'username' => 'user_195',
            'hash'     => Crypt::hash('password_user_195'),
            'slug'     => 'user_195',
            'email'    => null,
            'grade'    => 'member',
            'avatar'   => 'formage.jpg',
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 199,
            'username' => 'user_199 <script>alert(1)</script>',
            'hash'     => Crypt::hash('password_user_199'),
            'slug'     => 'user_199',
            'email'    => 'user_199@example.com',
            'grade'    => 'member',
            'avatar'   => 'mem\"><script>alert(1)</script>fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public function dataCasesEditSocials(): array
    {
        return [
            'edit OK' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit OK - missing users_infos' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit KO - xss' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook<script>alert("facebook");</script>',
                    'form-edit_socials-input-twitter'  => 'twitter<script>alert("twitter");</script>',
                    'form-edit_socials-input-github'   => 'github<script>alert("github");</script>',
                    'form-edit_socials-input-youtube'  => 'youtube<script>alert("youtube");</script>',
                    'form-edit_socials-input-twitch'   => 'twitch<script>alert("twitch");</script>',
                    'form-edit_socials-input-unreal'   => 'unreal<script>alert("unreal");</script>',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on Facebook, Twitter, GitHub, Youtube, Twitch, Unreal</div>'
                    ]
                ],
                'fields_has_error'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'facebook' => 'Expected username containing: digits, letters, symbols: - _ .',
                    'twitter'  => 'Expected username containing: digits, letters, symbols: - _ .',
                    'github'   => 'Expected username containing: digits, letters, symbols: - _ .',
                    'youtube'  => 'Expected username containing: digits, letters, symbols: - _ .',
                    'twitch'   => 'Expected username containing: digits, letters, symbols: - _ .',
                    'unreal'   => 'Expected username containing: digits, letters, symbols: - _ .'
                ],
            ],
            'csrf incorrect' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'incorrect_csrf',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no fields' => [
                'sql_queries'           => [],
                'user_id'               => 189,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no csrf' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no facebook' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no twitter' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no github' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no youtube' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no twitch' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no unreal' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'edit OK - empty fields - facebook empty' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => ' ',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'email' => 'Email is invalid'
                ],
            ],
            'edit OK - empty fields - twitter empty' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => ' ',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit OK - empty fields - github empty' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => ' ',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit OK - empty fields - youtube empty' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => ' ',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit OK - empty fields - twitch empty' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => ' ',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit OK - empty fields - unreal empty' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => ' ',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">Your social profiles has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [],
            ],
            'edit KO - invalid facebook' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook<script>alert("facebook");</script>',
                    'form-edit_socials-input-twitter'  => '',
                    'form-edit_socials-input-github'   => '',
                    'form-edit_socials-input-youtube'  => '',
                    'form-edit_socials-input-twitch'   => '',
                    'form-edit_socials-input-unreal'   => '',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on Facebook</div>'
                    ]
                ],
                'fields_has_error'      => ['facebook'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'facebook' => 'Expected username containing: digits, letters, symbols: - _ .',
                ],
            ],
            'edit KO - invalid twitter' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => '',
                    'form-edit_socials-input-twitter'  => 'twitter<script>alert("twitter");</script>',
                    'form-edit_socials-input-github'   => '',
                    'form-edit_socials-input-youtube'  => '',
                    'form-edit_socials-input-twitch'   => '',
                    'form-edit_socials-input-unreal'   => '',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on Twitter</div>'
                    ]
                ],
                'fields_has_error'      => ['twitter'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'twitter'  => 'Expected username containing: digits, letters, symbols: - _ .',
                ],
            ],
            'edit KO - invalid github' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => '',
                    'form-edit_socials-input-twitter'  => '',
                    'form-edit_socials-input-github'   => 'github<script>alert("github");</script>',
                    'form-edit_socials-input-youtube'  => '',
                    'form-edit_socials-input-twitch'   => '',
                    'form-edit_socials-input-unreal'   => '',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on GitHub</div>'
                    ]
                ],
                'fields_has_error'      => ['github'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'github'   => 'Expected username containing: digits, letters, symbols: - _ .',
                ],
            ],
            'edit KO - invalid youtube' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => '',
                    'form-edit_socials-input-twitter'  => '',
                    'form-edit_socials-input-github'   => '',
                    'form-edit_socials-input-youtube'  => 'youtube<script>alert("youtube");</script>',
                    'form-edit_socials-input-twitch'   => '',
                    'form-edit_socials-input-unreal'   => '',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on Youtube</div>'
                    ]
                ],
                'fields_has_error'      => ['youtube'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'youtube'  => 'Expected username containing: digits, letters, symbols: - _ .',
                ],
            ],
            'edit KO - invalid twitch' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => '',
                    'form-edit_socials-input-twitter'  => '',
                    'form-edit_socials-input-github'   => '',
                    'form-edit_socials-input-youtube'  => '',
                    'form-edit_socials-input-twitch'   => 'twitch<script>alert("twitch");</script>',
                    'form-edit_socials-input-unreal'   => '',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on Twitch</div>'
                    ]
                ],
                'fields_has_error'      => ['twitch'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'twitch'   => 'Expected username containing: digits, letters, symbols: - _ .',
                ],
            ],
            'edit KO - invalid unreal' => [
                'sql_queries' => [
                    "INSERT INTO users_infos (`id_user`, `link_facebook`, `link_twitter`, `link_github`, `link_youtube`, `link_twitch`, `link_unreal`) VALUES (189, 'link_facebook_value', 'link_twitter_value', 'link_github_value', 'link_youtube_value', 'link_twitch_value', 'link_unreal_value')"
                ],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => '',
                    'form-edit_socials-input-twitter'  => '',
                    'form-edit_socials-input-github'   => '',
                    'form-edit_socials-input-youtube'  => '',
                    'form-edit_socials-input-twitch'   => '',
                    'form-edit_socials-input-unreal'   => 'unreal<script>alert("1-unreal");</script>',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">Error(s) on Unreal</div>'
                    ]
                ],
                'fields_has_error'      => ['unreal'],
                'fields_has_value'      => ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'],
                'fields_label_error'    => [
                    'unreal'   => 'Expected username containing: digits, letters, symbols: - _ .'
                ],
            ],
            'invalid encoding fields - facebook' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => \chr(99999999),
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - twitter' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => \chr(99999999),
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - github' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => \chr(99999999),
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - youtube' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => \chr(99999999),
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - twitch' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => \chr(99999999),
                    'form-edit_socials-input-unreal'   => 'unreal',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - unreal' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-edit_socials-hidden-csrf'    => 'csrf_is_replaced',
                    'form-edit_socials-input-facebook' => 'facebook',
                    'form-edit_socials-input-twitter'  => 'twitter',
                    'form-edit_socials-input-github'   => 'github',
                    'form-edit_socials-input-youtube'  => 'youtube',
                    'form-edit_socials-input-twitch'   => 'twitch',
                    'form-edit_socials-input-unreal'   => \chr(99999999),
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_socials">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesEditSocials
     *
     * @param array $sqlQueries
     * @param int   $userID
     * @param array $params
     * @param bool  $useCsrfFromSession
     * @param bool  $hasRedirection
     * @param bool  $isFormSuccess
     * @param array $flashMessages
     * @param array $fieldsHasError
     * @param array $fieldsHasValue
     * @param array $fieldsLabelError
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    public function testProfileEditPOSTEditSocials(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        static::setDatabase();
        static::$db->truncateTables('users_infos');

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user session
        $sessionValues = [
            'set'    => ['userID' => $userID],
            'remove' => []
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-edit_socials-hidden-csrf'] = $_SESSION['csrf'];
        }

        // infos before
        $usersInfosBefore = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/profile/user_' . $userID . '/edit/', $params);

        if ($hasRedirection) {
            static::assertSame('/profile/user_' . $userID . '/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        $usersInfosAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);

        if ($isFormSuccess) {
            static::assertNotSame($usersInfosBefore, $usersInfosAfter);
            static::assertSame(\trim($params['form-edit_socials-input-facebook']), $usersInfosAfter['link_facebook']);
            static::assertSame(\trim($params['form-edit_socials-input-twitter']), $usersInfosAfter['link_twitter']);
            static::assertSame(\trim($params['form-edit_socials-input-github']), $usersInfosAfter['link_github']);
            static::assertSame(\trim($params['form-edit_socials-input-youtube']), $usersInfosAfter['link_youtube']);
            static::assertSame(\trim($params['form-edit_socials-input-twitch']), $usersInfosAfter['link_twitch']);
            static::assertSame(\trim($params['form-edit_socials-input-unreal']), $usersInfosAfter['link_unreal']);
        } else {
            static::assertSame($usersInfosBefore, $usersInfosAfter);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['success']['message']);
        }

        // test fields HTML
        $fields = ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'facebook') {
                $value = $hasValue ? \trim($params['form-edit_socials-input-facebook']) : '';
                $this->doTestHtmlForm($response, '#form-edit_socials', $this->getHTMLFieldFacebook($value, $hasError, $labelError));
            }

            if ($field === 'twitter') {
                $value = $hasValue ? \trim($params['form-edit_socials-input-twitter']) : '';
                $this->doTestHtmlForm($response, '#form-edit_socials', $this->getHTMLFieldTwitter($value, $hasError, $labelError));
            }

            if ($field === 'github') {
                $value = $hasValue ? \trim($params['form-edit_socials-input-github']) : '';
                $this->doTestHtmlForm($response, '#form-edit_socials', $this->getHTMLFieldGithub($value, $hasError, $labelError));
            }

            if ($field === 'youtube') {
                $value = $hasValue ? \trim($params['form-edit_socials-input-youtube']) : '';
                $this->doTestHtmlForm($response, '#form-edit_socials', $this->getHTMLFieldYoutube($value, $hasError, $labelError));
            }

            if ($field === 'twitch') {
                $value = $hasValue ? \trim($params['form-edit_socials-input-twitch']) : '';
                $this->doTestHtmlForm($response, '#form-edit_socials', $this->getHTMLFieldTwitch($value, $hasError, $labelError));
            }

            if ($field === 'unreal') {
                $value = $hasValue ? \trim($params['form-edit_socials-input-unreal']) : '';
                $this->doTestHtmlForm($response, '#form-edit_socials', $this->getHTMLFieldUnreal($value, $hasError, $labelError));
            }
        }
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldFacebook(string $value, bool $hasError, string $labelError): string
    {
        $vAttr = Security::escAttr($value);
        $vHTML = Security::escHTML($value);
        $vHTML = ($vHTML !== '' && $hasError === false) ? $vHTML : 'username';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-facebook" id="form-edit_socials-label-facebook">Facebook</label>
<div class="form__container form__container--error">
<input aria-describedby="form-edit_socials-span-facebook_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-facebook form-edit_socials-label-facebook-error" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-facebook" name="form-edit_socials-input-facebook" type="text" value="$vAttr"/>
<span class="form__feedback form__feedback--error"></span>
<svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--facebook">
<use href="/sprite/sprite.svg#icon-facebook"></use>
</svg>
</div>
<label class="form__label form__label--error" for="form-edit_socials-input-facebook" id="form-edit_socials-label-facebook-error">$labelError</label>
<span class="form__help" id="form-edit_socials-span-facebook_help">https://www.facebook.com/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-facebook" id="form-edit_socials-label-facebook">Facebook</label>
<div class="form__container">
<input aria-describedby="form-edit_socials-span-facebook_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-facebook" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-facebook" name="form-edit_socials-input-facebook" type="text" value="$vAttr"/>
<span class="form__feedback"></span>
<svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--facebook">
<use href="/sprite/sprite.svg#icon-facebook"></use>
</svg>
</div>
<span class="form__help" id="form-edit_socials-span-facebook_help">https://www.facebook.com/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldTwitter(string $value, bool $hasError, string $labelError): string
    {
        $vAttr = Security::escAttr($value);
        $vHTML = Security::escHTML($value);
        $vHTML = ($vHTML !== '' && $hasError === false) ? $vHTML : 'username';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-twitter" id="form-edit_socials-label-twitter">Twitter</label>
<div class="form__container form__container--error">
<input aria-describedby="form-edit_socials-span-twitter_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-twitter form-edit_socials-label-twitter-error" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-twitter" name="form-edit_socials-input-twitter" type="text" value="$vAttr"/>
<span class="form__feedback form__feedback--error"></span>
<svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--twitter">
<use href="/sprite/sprite.svg#icon-twitter"></use>
</svg>
</div>
<label class="form__label form__label--error" for="form-edit_socials-input-twitter" id="form-edit_socials-label-twitter-error">$labelError</label>
<span class="form__help" id="form-edit_socials-span-twitter_help">https://twitter.com/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-twitter" id="form-edit_socials-label-twitter">Twitter</label>
<div class="form__container">
<input aria-describedby="form-edit_socials-span-twitter_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-twitter" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-twitter" name="form-edit_socials-input-twitter" type="text" value="$vAttr"/>
<span class="form__feedback"></span>
<svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--twitter">
<use href="/sprite/sprite.svg#icon-twitter"></use>
</svg>
</div>
<span class="form__help" id="form-edit_socials-span-twitter_help">https://twitter.com/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldGithub(string $value, bool $hasError, string $labelError): string
    {
        $vAttr = Security::escAttr($value);
        $vHTML = Security::escHTML($value);
        $vHTML = ($vHTML !== '' && $hasError === false) ? $vHTML : 'username';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-github" id="form-edit_socials-label-github">GitHub</label>
<div class="form__container form__container--error">
<input aria-describedby="form-edit_socials-span-github_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-github form-edit_socials-label-github-error" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-github" name="form-edit_socials-input-github" type="text" value="$vAttr"/>
<span class="form__feedback form__feedback--error"></span>
<svg aria-hidden="true" class="edit-profile__social-icon">
<use href="/sprite/sprite.svg#icon-github"></use>
</svg>
</div>
<label class="form__label form__label--error" for="form-edit_socials-input-github" id="form-edit_socials-label-github-error">$labelError</label>
<span class="form__help" id="form-edit_socials-span-github_help">https://github.com/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-github" id="form-edit_socials-label-github">GitHub</label>
<div class="form__container">
<input aria-describedby="form-edit_socials-span-github_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-github" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-github" name="form-edit_socials-input-github" type="text" value="$vAttr"/>
<span class="form__feedback"></span>
<svg aria-hidden="true" class="edit-profile__social-icon">
<use href="/sprite/sprite.svg#icon-github"></use>
</svg>
</div>
<span class="form__help" id="form-edit_socials-span-github_help">https://github.com/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldYoutube(string $value, bool $hasError, string $labelError): string
    {
        $vAttr = Security::escAttr($value);
        $vHTML = Security::escHTML($value);
        $vHTML = ($vHTML !== '' && $hasError === false) ? $vHTML : 'channel_id';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-youtube" id="form-edit_socials-label-youtube">Youtube</label>
<div class="form__container form__container--error">
<input aria-describedby="form-edit_socials-span-youtube_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-youtube form-edit_socials-label-youtube-error" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="channel_id" id="form-edit_socials-input-youtube" name="form-edit_socials-input-youtube" type="text" value="$vAttr"/>
<span class="form__feedback form__feedback--error"></span>
<svg aria-hidden="true" class="edit-profile__social-icon">
<use href="/sprite/sprite.svg#icon-youtube"></use>
</svg>
</div>
<label class="form__label form__label--error" for="form-edit_socials-input-youtube" id="form-edit_socials-label-youtube-error">$labelError</label>
<span class="form__help" id="form-edit_socials-span-youtube_help">https://www.youtube.com/channel/<span class="form__help--emphasis">$vHTML</span><br />
Find your channel id: <a href="https://www.youtube.com/account_advanced" rel="noopener noreferrer nofollow" target="_blank">https://www.youtube.com/account_advanced</a></span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-youtube" id="form-edit_socials-label-youtube">Youtube</label>
<div class="form__container">
<input aria-describedby="form-edit_socials-span-youtube_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-youtube" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="channel_id" id="form-edit_socials-input-youtube" name="form-edit_socials-input-youtube" type="text" value="$vAttr"/>
<span class="form__feedback"></span>
<svg aria-hidden="true" class="edit-profile__social-icon">
<use href="/sprite/sprite.svg#icon-youtube"></use>
</svg>
</div>
<span class="form__help" id="form-edit_socials-span-youtube_help">https://www.youtube.com/channel/<span class="form__help--emphasis">$vHTML</span><br />
Find your channel id: <a href="https://www.youtube.com/account_advanced" rel="noopener noreferrer nofollow" target="_blank">https://www.youtube.com/account_advanced</a></span>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldTwitch(string $value, bool $hasError, string $labelError): string
    {
        $vAttr = Security::escAttr($value);
        $vHTML = Security::escHTML($value);
        $vHTML = ($vHTML !== '' && $hasError === false) ? $vHTML : 'username';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-twitch" id="form-edit_socials-label-twitch">Twitch</label>
<div class="form__container form__container--error">
<input aria-describedby="form-edit_socials-span-twitch_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-twitch form-edit_socials-label-twitch-error" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-twitch" name="form-edit_socials-input-twitch" type="text" value="$vAttr"/>
<span class="form__feedback form__feedback--error"></span>
<svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--twitch">
<use href="/sprite/sprite.svg#icon-twitch"></use>
</svg>
</div>
<label class="form__label form__label--error" for="form-edit_socials-input-twitch" id="form-edit_socials-label-twitch-error">$labelError</label>
<span class="form__help" id="form-edit_socials-span-twitch_help">https://www.twitch.tv/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-twitch" id="form-edit_socials-label-twitch">Twitch</label>
<div class="form__container">
<input aria-describedby="form-edit_socials-span-twitch_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-twitch" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-twitch" name="form-edit_socials-input-twitch" type="text" value="$vAttr"/>
<span class="form__feedback"></span>
<svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--twitch">
<use href="/sprite/sprite.svg#icon-twitch"></use>
</svg>
</div>
<span class="form__help" id="form-edit_socials-span-twitch_help">https://www.twitch.tv/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFieldUnreal(string $value, bool $hasError, string $labelError): string
    {
        $vAttr = Security::escAttr($value);
        $vHTML = Security::escHTML($value);
        $vHTML = ($vHTML !== '' && $hasError === false) ? $vHTML : 'username';

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-unreal" id="form-edit_socials-label-unreal">Unreal Engine Forum</label>
<div class="form__container form__container--error">
<input aria-describedby="form-edit_socials-span-unreal_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-unreal form-edit_socials-label-unreal-error" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-unreal" name="form-edit_socials-input-unreal" type="text" value="$vAttr"/>
<span class="form__feedback form__feedback--error"></span>
<svg aria-hidden="true" class="edit-profile__social-icon">
<use href="/sprite/sprite.svg#icon-unreal"></use>
</svg>
</div>
<label class="form__label form__label--error" for="form-edit_socials-input-unreal" id="form-edit_socials-label-unreal-error">$labelError</label>
<span class="form__help" id="form-edit_socials-span-unreal_help">https://forums.unrealengine.com/u/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_socials-input-unreal" id="form-edit_socials-label-unreal">Unreal Engine Forum</label>
<div class="form__container">
<input aria-describedby="form-edit_socials-span-unreal_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-unreal" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-unreal" name="form-edit_socials-input-unreal" type="text" value="$vAttr"/>
<span class="form__feedback"></span>
<svg aria-hidden="true" class="edit-profile__social-icon">
<use href="/sprite/sprite.svg#icon-unreal"></use>
</svg>
</div>
<span class="form__help" id="form-edit_socials-span-unreal_help">https://forums.unrealengine.com/u/<span class="form__help--emphasis">$vHTML</span></span>
</div>
HTML;
        // phpcs:enable
    }
}
