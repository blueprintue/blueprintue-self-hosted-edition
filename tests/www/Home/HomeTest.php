<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Home;

use app\helpers\Helper;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class HomeTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
        static::addUsers();
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testHomeGET(): void
    {
        $response = $this->getResponseFromApplication('GET', '/');
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => 'This is a base title',
            'description' => 'This&#x20;is&#x20;a&#x20;description',
        ]);
        $this->doTestHtmlBody($response, '<h2 class="block__title">Paste your <span class="block__title--emphasis">blueprint</span></h2>');
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasLinkHomeActive($response);
    }

    public function dataCasesCreateBlueprint(): array
    {
        return [
            'xss - create blueprint OK' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '<script>alert("title")</script>',
                    'form-add_blueprint-select-exposure'    => 'public',
                    'form-add_blueprint-select-expiration'  => 'never',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object <script>alert("blueprint")</script>'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => ['exposure', 'expiration', 'ue_version'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [],
            ],
            'xss form - KO' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '<script>alert("title")</script>',
                    'form-add_blueprint-select-exposure'    => '<script>alert("exposure")</script>',
                    'form-add_blueprint-select-expiration'  => '<script>alert("expiration")</script>',
                    'form-add_blueprint-select-ue_version'  => '<script>alert("ue_version")</script>',
                    'form-add_blueprint-textarea-blueprint' => '<script>alert("blueprint")</script>'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'exposure'   => 'Exposure is invalid',
                    'expiration' => 'Expiration is invalid',
                    'ue_version' => 'UE version is invalid',
                    'blueprint'  => 'Blueprint is invalid',
                ],
            ],
            'anonymous - create blueprint OK - 1 hour' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => 'title 1 hour',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1h',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object 1'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'anonymous - create blueprint OK - 1 day' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => 'title 1 day',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object 2'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'anonymous - create blueprint OK - 1 week' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => 'title 1 week',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1w',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object 3'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'user - create blueprint OK' => [
                'user_id' => static::$userID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => 'title never',
                    'form-add_blueprint-select-exposure'    => 'private',
                    'form-add_blueprint-select-expiration'  => 'never',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object 2'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'csrf incorrect' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'incorrect_csrf',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no fields' => [
                'user_id'               => static::$anonymousID,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no csrf' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no title' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'incorrect_csrf',
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no exposure' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'incorrect_csrf',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-input-expiration'   => '0',
                    'form-add_blueprint-input-ue_version'   => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no ue_version' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'         => 'incorrect_csrf',
                    'form-add_blueprint-input-title'         => '0',
                    'form-add_blueprint-select-exposure'     => '0',
                    'form-add_blueprint-select-expiration'   => '0',
                    'form-add_blueprint-selectrea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no blueprint' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'       => 'incorrect_csrf',
                    'form-add_blueprint-input-title'       => '0',
                    'form-add_blueprint-select-exposure'   => '0',
                    'form-add_blueprint-select-expiration' => '0',
                    'form-add_blueprint-select-ue_version' => '0',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'empty fields - title empty' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => ' ',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['title'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'title' => 'Title is required'
                ],
            ],
            'empty fields - exposure empty' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => ' ',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['exposure'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'exposure' => 'Exposure is required'
                ],
            ],
            'empty fields - expiration empty' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => ' ',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['expiration'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'expiration' => 'Expiration is required'
                ],
            ],
            'empty fields - ue_version empty' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => ' ',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['ue_version'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'ue_version' => 'UE version is required'
                ],
            ],
            'empty fields - blueprint empty' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => ' '
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['blueprint'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'blueprint' => 'Blueprint is required'
                ],
            ],
            'invalid fields - exposure invalid' => [
                'user_id' => static::$userID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'invalid',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['exposure'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'exposure' => 'Exposure is invalid'
                ],
            ],
            'invalid fields - exposure private for anonymous' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'private',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['exposure'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'exposure' => 'Private is for member only'
                ],
            ],
            'invalid fields - expiration invalid' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1500s',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['expiration'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'expiration' => 'Expiration is invalid'
                ],
            ],
            'invalid fields - ue_version invalid' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => 'invalid',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['ue_version'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'ue_version' => 'UE version is invalid'
                ],
            ],
            'invalid fields - blueprint invalid' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => '1d',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'spam'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fields_has_error'      => ['blueprint'],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [
                    'blueprint' => 'Blueprint is invalid'
                ],
            ],
            'throw exception in blueprint creation' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => 'title 1 hour',
                    'form-add_blueprint-select-exposure'    => 'unlisted',
                    'form-add_blueprint-select-expiration'  => 'never',
                    'form-add_blueprint-select-ue_version'  => '4.14',
                    'form-add_blueprint-textarea-blueprint' => 'Begin Object throw exception'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">Error, could not create blueprint (#200)</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - title' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => \chr(99999999),
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - exposure' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => \chr(99999999),
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - expiration' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => \chr(99999999),
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - ue_version' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => \chr(99999999),
                    'form-add_blueprint-textarea-blueprint' => '0'
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - blueprint' => [
                'user_id' => static::$anonymousID,
                'params'  => [
                    'form-add_blueprint-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_blueprint-input-title'        => '0',
                    'form-add_blueprint-select-exposure'    => '0',
                    'form-add_blueprint-select-expiration'  => '0',
                    'form-add_blueprint-select-ue_version'  => '0',
                    'form-add_blueprint-textarea-blueprint' => \chr(99999999)
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesCreateBlueprint
     *
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
     * @throws DatabaseException
     * @throws \Exception
     */
    public function testHomePOSTCreateBlueprint(int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        // set user in $_SESSION
        $session = ['remove' => ['anonymous_blueprints'], 'set' => []];
        if ($userID === static::$userID) {
            $session['set']['userID'] = $userID;
        } else {
            $session['remove'][] = 'userID';
        }

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $session);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-add_blueprint-hidden-csrf'] = $_SESSION['csrf'];
        }

        // test response / redirection
        static::setDatabase();
        $userInfosBefore = static::$db->selectRow('SELECT * FROM users_infos where id_user = :userID', ['userID' => $userID]);

        if (isset($params['form-add_blueprint-textarea-blueprint']) && $params['form-add_blueprint-textarea-blueprint'] === 'Begin Object throw exception') {
            static::$db->exec('UPDATE blueprints SET expiration = utc_timestamp() WHERE id > 0');
            static::$db->exec('ALTER TABLE blueprints CHANGE COLUMN `expiration` `expiration` DATETIME NOT NULL ;');
        }

        $response = $this->getResponseFromApplication('POST', '/', $params);

        if (isset($params['form-add_blueprint-textarea-blueprint']) && $params['form-add_blueprint-textarea-blueprint'] === 'Begin Object throw exception') {
            static::$db->exec('ALTER TABLE blueprints CHANGE COLUMN `expiration` `expiration` DATETIME NULL ;');
        }

        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);

            if ($isFormSuccess) {
                // database check - default values OK + user counts OK
                $blueprint = static::$db->selectRow('SELECT * FROM blueprints WHERE title = :title ORDER BY id DESC', ['title' => $params['form-add_blueprint-input-title']]);
                static::assertSame('/blueprint/' . $blueprint['slug'] . '/', $response->getHeaderLine('Location'));
                static::assertSame($userID, (int) $blueprint['id_author']);
                static::assertSame($params['form-add_blueprint-input-title'], $blueprint['title']);
                static::assertSame('blueprint', $blueprint['type']);
                static::assertSame($params['form-add_blueprint-select-ue_version'], $blueprint['ue_version']);
                static::assertSame('1', $blueprint['current_version']);
                static::assertNull($blueprint['thumbnail']);
                static::assertNull($blueprint['description']);
                static::assertNotNull($blueprint['created_at']);
                static::assertNotNull($blueprint['published_at']);
                static::assertSame($blueprint['created_at'], $blueprint['published_at']);
                static::assertNull($blueprint['updated_at']);
                static::assertSame($params['form-add_blueprint-select-exposure'], $blueprint['exposure']);
                if ($params['form-add_blueprint-select-expiration'] === 'never') {
                    static::assertNull($blueprint['expiration']);
                } else {
                    static::assertNotNull($blueprint['expiration']);

                    $date = new \DateTime($blueprint['created_at']);
                    if ($params['form-add_blueprint-select-expiration'] === '1h') {
                        $expireAt = $date->add(new \DateInterval('PT1H'))->format('Y-m-d H:i:s');
                        static::assertSame($expireAt, $blueprint['expiration']);
                    }
                    if ($params['form-add_blueprint-select-expiration'] === '1d') {
                        $expireAt = $date->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s');
                        static::assertSame($expireAt, $blueprint['expiration']);
                    }
                    if ($params['form-add_blueprint-select-expiration'] === '1w') {
                        $expireAt = $date->add(new \DateInterval('P7D'))->format('Y-m-d H:i:s');
                        static::assertSame($expireAt, $blueprint['expiration']);
                    }
                }
                static::assertNull($blueprint['tags']);
                static::assertNull($blueprint['video']);
                static::assertNull($blueprint['video_provider']);
                static::assertSame(0, (int) $blueprint['comments_hidden']);
                static::assertSame(0, (int) $blueprint['comments_closed']);
                if ($userID === static::$anonymousID) {
                    static::assertNotNull($_SESSION['anonymous_blueprints']);
                    static::assertContains((int) $blueprint['id'], $_SESSION['anonymous_blueprints']);
                } else {
                    static::assertArrayNotHasKey('anonymous_blueprints', $_SESSION);
                }

                $blueprintVersion = static::$db->selectRow('SELECT * FROM blueprints_version WHERE id_blueprint = :blueprintID', ['blueprintID' => $blueprint['id']]);
                static::assertSame('1', $blueprintVersion['version']);
                static::assertSame('First commit', $blueprintVersion['reason']);
                static::assertNotNull($blueprintVersion['created_at']);
                static::assertNotNull($blueprintVersion['published_at']);
                static::assertSame($blueprintVersion['created_at'], $blueprintVersion['published_at']);
                static::assertNull($blueprintVersion['updated_at']);

                $userInfosAfter = static::$db->selectRow('SELECT * FROM users_infos where id_user = :userID', ['userID' => $userID]);
                if ($params['form-add_blueprint-select-exposure'] === 'public') {
                    static::assertSame((int) $userInfosBefore['count_public_blueprint'] + 1, (int) $userInfosAfter['count_public_blueprint']);
                    static::assertSame((int) $userInfosBefore['count_private_blueprint'] + 1, (int) $userInfosAfter['count_private_blueprint']);
                } else {
                    static::assertSame((int) $userInfosBefore['count_public_blueprint'], (int) $userInfosAfter['count_public_blueprint']);
                    static::assertSame((int) $userInfosBefore['count_private_blueprint'] + 1, (int) $userInfosAfter['count_private_blueprint']);
                }

                // file check - must have file present + content blueprint inside
                $caracters = \mb_str_split($blueprint['file_id']);
                $subfolder = '';
                foreach ($caracters as $c) {
                    $subfolder .= $c . \DIRECTORY_SEPARATOR;
                }
                $subfolder = \mb_strtolower($subfolder);

                $storageFolder = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'storage_test' . \DIRECTORY_SEPARATOR;
                $fullpath = $storageFolder . $subfolder . $blueprint['file_id'] . '-' . $blueprint['current_version'] . '.txt';
                static::assertFileExists($fullpath);

                static::assertSame($params['form-add_blueprint-textarea-blueprint'], \file_get_contents($fullpath));

                return;
            }

            static::assertSame('/', $response->getHeaderLine('Location'));

            $response = $this->getResponseFromApplication('GET', '/');
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
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
        $fields = ['title', 'exposure', 'expiration', 'ue_version', 'blueprint'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'title') {
                $value = $hasValue ? \trim($params['form-add_blueprint-input-title']) : '';
                $this->doTestHtmlForm($response, '/', $this->getHTMLFieldTitle($value, $hasError, $labelError));
            }

            if ($field === 'exposure') {
                $value = $hasValue ? \trim($params['form-add_blueprint-select-exposure']) : '';
                $this->doTestHtmlForm($response, '/', $this->getHTMLFieldExposure($value, $hasError, $labelError));
            }

            if ($field === 'expiration') {
                $value = $hasValue ? \trim($params['form-add_blueprint-select-expiration']) : '';
                $this->doTestHtmlForm($response, '/', $this->getHTMLFieldExpiration($value, $hasError, $labelError));
            }

            if ($field === 'ue_version') {
                $value = $hasValue ? \trim($params['form-add_blueprint-select-ue_version']) : '';
                $this->doTestHtmlForm($response, '/', $this->getHTMLFieldUEVersion($value, $hasError, $labelError));
            }

            if ($field === 'blueprint') {
                $value = $hasValue ? \trim($params['form-add_blueprint-textarea-blueprint']) : '';
                $this->doTestHtmlForm($response, '/', $this->getHTMLFieldBlueprint($value, $hasError, $labelError));
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
    protected function getHTMLFieldTitle(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-add_blueprint-label-title form-add_blueprint-label-title-error" aria-required="true" class="form__input form__input--invisible form__input--error" data-form-error-required="Title is required" data-form-has-container data-form-rules="required" id="form-add_blueprint-input-title" name="form-add_blueprint-input-title" type="text" value="$v"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-add_blueprint-input-title" id="form-add_blueprint-label-title-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-add_blueprint-label-title" aria-required="true" class="form__input form__input--invisible" data-form-error-required="Title is required" data-form-has-container data-form-rules="required" id="form-add_blueprint-input-title" name="form-add_blueprint-input-title" type="text" value="$v"/>
<span class="form__feedback"></span>
</div>
HTML;
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
    protected function getHTMLFieldExposure(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        $selectedPublic = ($v === '' || $v === 'public') ? ' selected="selected"' : '';
        $selectedUnlisted = ($v === 'unlisted') ? ' selected="selected"' : '';
        $selectedPrivate = ($v === 'private') ? ' selected="selected"' : '';
        $privateOption = (isset($_SESSION['userID']) && $_SESSION['userID'] === static::$userID) ? '<option value="private"' . $selectedPrivate . '>Private</option>' : '<option value="private" disabled>Private (member only)</option>';

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-add_blueprint-label-exposure form-add_blueprint-label-exposure-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-add_blueprint-select-exposure" name="form-add_blueprint-select-exposure">
<option value="public"$selectedPublic>Public</option>
<option value="unlisted"$selectedUnlisted>Unlisted</option>
$privateOption
</select>
</div>
<label class="form__label form__label--error" for="form-add_blueprint-select-exposure" id="form-add_blueprint-label-exposure-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-add_blueprint-label-exposure" aria-required="true" class="form__input form__input--select" id="form-add_blueprint-select-exposure" name="form-add_blueprint-select-exposure">
<option value="public"$selectedPublic>Public</option>
<option value="unlisted"$selectedUnlisted>Unlisted</option>
$privateOption
</select>
</div>
HTML;
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
    protected function getHTMLFieldExpiration(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);

        $selectedNever = ($v === '' || $v === 'never') ? ' selected="selected"' : '';
        $selected1Hour = ($v === '1h') ? ' selected="selected"' : '';
        $selected1Day = ($v === '1d') ? ' selected="selected"' : '';
        $selected1Week = ($v === '1w') ? ' selected="selected"' : '';

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-add_blueprint-label-expiration form-add_blueprint-label-expiration-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-add_blueprint-select-expiration" name="form-add_blueprint-select-expiration">
<option value="never"$selectedNever>Never</option>
<option value="1h"$selected1Hour>1 hour</option>
<option value="1d"$selected1Day>1 day</option>
<option value="1w"$selected1Week>1 week</option>
</select>
</div>
<label class="form__label form__label--error" for="form-add_blueprint-select-expiration" id="form-add_blueprint-label-expiration-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-add_blueprint-label-expiration" aria-required="true" class="form__input form__input--select" id="form-add_blueprint-select-expiration" name="form-add_blueprint-select-expiration">
<option value="never"$selectedNever>Never</option>
<option value="1h"$selected1Hour>1 hour</option>
<option value="1d"$selected1Day>1 day</option>
<option value="1w"$selected1Week>1 week</option>
</select>
</div>
HTML;
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
    protected function getHTMLFieldUEVersion(string $value, bool $hasError, string $labelError): string
    {
        $listOptions = [];
        $selectedUEVersion = ($value === '') ? Helper::getCurrentUEVersion() : $value;
        foreach (Helper::getAllUEVersion() as $ueVersion) {
            $listOptions[] = '<option value="' . Security::escAttr($ueVersion) . '"' . (($selectedUEVersion === $ueVersion) ? ' selected="selected"' : '') . '>' . Security::escHTML($ueVersion) . '</option>';
        }
        $listOptionsStr = \implode("\n", $listOptions);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-add_blueprint-label-ue_version form-add_blueprint-label-ue_version-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-add_blueprint-select-ue_version" name="form-add_blueprint-select-ue_version">
$listOptionsStr
</select>
</div>
<label class="form__label form__label--error" for="form-add_blueprint-select-ue_version" id="form-add_blueprint-label-ue_version-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-add_blueprint-label-ue_version" aria-required="true" class="form__input form__input--select" id="form-add_blueprint-select-ue_version" name="form-add_blueprint-select-ue_version">
$listOptionsStr
</select>
</div>
HTML;
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
    protected function getHTMLFieldBlueprint(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--blueprint form__container--textarea form__container--error">
<textarea aria-invalid="false" aria-labelledby="form-add_blueprint-label-blueprint form-add_blueprint-label-blueprint-error" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--blueprint form__input--error" data-form-error-required="Blueprint is required" data-form-has-container data-form-rules="required" id="form-add_blueprint-textarea-blueprint" name="form-add_blueprint-textarea-blueprint">$v</textarea>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-add_blueprint-textarea-blueprint" id="form-add_blueprint-label-blueprint-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--blueprint form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-add_blueprint-label-blueprint" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--blueprint" data-form-error-required="Blueprint is required" data-form-has-container data-form-rules="required" id="form-add_blueprint-textarea-blueprint" name="form-add_blueprint-textarea-blueprint">$v</textarea>
<span class="form__feedback"></span>
</div>
HTML;
    }
}
