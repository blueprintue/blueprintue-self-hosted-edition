<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Blueprint\Edit;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
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

class BlueprintEditPOSTEditInformationsTest extends TestCase
{
    use Common;

    /**
     * @throws \Rancoud\Crypt\CryptException
     * @throws DatabaseException
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

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function dataCasesEditInformations(): array
    {
        return [
            'update OK - title' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'new title',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - description to text' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'description', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - description to null' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `description`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private', 'description')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - tags added (forbidden tags is not added) - limit to 25' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'new title',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => <<<'TEXTAREA'
                    tag-1
                    tag-2
                    invalid@tag
                    TAG 3
                    TAG 3

                    tag 4
                    123
                          yo     lo
                    tag 6
                    tag 7
                    tag 8
                    tag 9
                    tag 10
                    tag 11
                    tag 12
                    tag 13
                    tag 14
                    tag 15
                    tag 16
                    tag 17
                    tag 18
                    tag 19
                    tag 20
                    tag 21
                    tag 22
                    tag 23
                    tag 24
                    tag 25
                    tag 26
                    tag 27
                    tag 28
                    tag 29
                    tag 30
                    TEXTAREA,
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => <<<'TEXTAREA'
                tag-1
                tag-2
                tag 3
                tag 4
                123
                yo lo
                tag 6
                tag 7
                tag 8
                tag 9
                tag 10
                tag 11
                tag 12
                tag 13
                tag 14
                tag 15
                tag 16
                tag 17
                tag 18
                tag 19
                tag 20
                tag 21
                tag 22
                tag 23
                tag 24
                TEXTAREA,
                'tags' => [
                    ['id' => '1', 'name' => 'tag-1', 'slug' => 'tag-1'], ['id' => '2', 'name' => 'tag-2', 'slug' => 'tag-2'],
                    ['id' => '3', 'name' => 'tag 3', 'slug' => 'tag-3'], ['id' => '4', 'name' => 'tag 4', 'slug' => 'tag-4'],
                    ['id' => '5', 'name' => '123', 'slug' => '123'], ['id' => '6', 'name' => 'yo lo', 'slug' => 'yo-lo'],
                    ['id' => '7', 'name' => 'tag 6', 'slug' => 'tag-6'], ['id' => '8', 'name' => 'tag 7', 'slug' => 'tag-7'],
                    ['id' => '9', 'name' => 'tag 8', 'slug' => 'tag-8'], ['id' => '10', 'name' => 'tag 9', 'slug' => 'tag-9'],
                    ['id' => '11', 'name' => 'tag 10', 'slug' => 'tag-10'], ['id' => '12', 'name' => 'tag 11', 'slug' => 'tag-11'],
                    ['id' => '13', 'name' => 'tag 12', 'slug' => 'tag-12'], ['id' => '14', 'name' => 'tag 13', 'slug' => 'tag-13'],
                    ['id' => '15', 'name' => 'tag 14', 'slug' => 'tag-14'], ['id' => '16', 'name' => 'tag 15', 'slug' => 'tag-15'],
                    ['id' => '17', 'name' => 'tag 16', 'slug' => 'tag-16'], ['id' => '18', 'name' => 'tag 17', 'slug' => 'tag-17'],
                    ['id' => '19', 'name' => 'tag 18', 'slug' => 'tag-18'], ['id' => '20', 'name' => 'tag 19', 'slug' => 'tag-19'],
                    ['id' => '21', 'name' => 'tag 20', 'slug' => 'tag-20'], ['id' => '22', 'name' => 'tag 21', 'slug' => 'tag-21'],
                    ['id' => '23', 'name' => 'tag 22', 'slug' => 'tag-22'], ['id' => '24', 'name' => 'tag 23', 'slug' => 'tag-23'],
                    ['id' => '25', 'name' => 'tag 24', 'slug' => 'tag-24']
                ],
            ],
            'update OK - tags present then removed' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO tags (`id`, `name`, `slug`) VALUES (1, 'tag 1', 'tag-1'), (5, 'tag 0', 'tag-0'), (10, 'aze', 'rty')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'new title',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - tags added (already created in tags table)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO tags (`id`, `name`, `slug`) VALUES (1, 'tag 1', 'tag-1')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'new title',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => <<<'TEXTAREA'
                    tag 1
                    TEXTAREA,
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => <<<'TEXTAREA'
                tag 1
                TEXTAREA,
                'tags' => [
                    ['id' => '1', 'name' => 'tag 1', 'slug' => 'tag-1']
                ],
            ],
            'update OK - video to text' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'youtu.be/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags', 'video'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - video to text (peertube)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'https://vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags', 'video'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - video to null' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `video`, `video_provider`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private', 'youtu.be/5qap5aO4i9A', 'youtube')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => '',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'update OK - xss' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => '<script>alert(1)</script>"/><script>alert(1)</script>',
                    'form-edit_informations-textarea-description' => '<script>alert(1)</script>"/><script>alert(1)</script>',
                    'form-edit_informations-textarea-tags'        => '<script>alert(1)</script>"/><script>alert(1)</script>',
                    'form-edit_informations-input-video'          => 'youtu.be/5qap5aO4i9A&=<script>alert(1)</script>"/><script>alert(1)</script>',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">Informations has been updated</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'description', 'tags', 'video'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'csrf incorrect' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'incorrect_csrf',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'youtu.be/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'missing fields - no csrf' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'youtu.be/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'missing fields - no title' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'youtu.be/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'missing fields - no description' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_informations-input-title'   => 'title_1',
                    'form-edit_informations-textarea-tags' => '',
                    'form-edit_informations-input-video'   => 'youtu.be/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'missing fields - no tags' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-input-video'          => 'youtu.be/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'missing fields - no video' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title', 'tags'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'empty fields - title' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => ' ',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => '//www.youtube.com/embed/5qap5aO4i9A',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">Error(s) on title</div>'
                    ]
                ],
                'fieldsHasError'   => ['title'],
                'fieldsHasValue'   => ['title', 'description', 'tags', 'video'],
                'fieldsLabelError' => [
                    'title' => 'Title is required'
                ],
                'tagsTextarea' => '',
                'tags'         => [],
            ],
            'invalid fields - video' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'new title',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'invalid',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">Error(s) on video</div>'
                    ]
                ],
                'fieldsHasError'   => ['video'],
                'fieldsHasValue'   => ['title', 'description', 'tags', 'video'],
                'fieldsLabelError' => [
                    'video' => 'Video is invalid'
                ],
                'tagsTextarea' => '',
                'tags'         => [],
            ],
            'invalid encoding fields - title' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => \chr(99999999),
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'invalid',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'invalid encoding fields - description' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => \chr(99999999),
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => 'invalid',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'invalid encoding fields - tags' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => \chr(99999999),
                    'form-edit_informations-input-video'          => 'invalid',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ],
            'invalid encoding fields - video' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID' => 189,
                'params' => [
                    'form-edit_informations-hidden-csrf'          => 'csrf_is_replaced',
                    'form-edit_informations-input-title'          => 'title_1',
                    'form-edit_informations-textarea-description' => 'new' . "\n" . 'description',
                    'form-edit_informations-textarea-tags'        => '',
                    'form-edit_informations-input-video'          => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_informations">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['title'],
                'fieldsLabelError' => [],
                'tagsTextarea'     => '',
                'tags'             => [],
            ]
        ];
    }

    /**
     * @dataProvider dataCasesEditInformations
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesEditInformations')]
    public function testBlueprintEditPOSTEditInformations(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError, string $tagsTextarea, array $tags): void
    {
        static::setDatabase();
        static::$db->truncateTables('tags');

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
            $params['form-edit_informations-hidden-csrf'] = $_SESSION['csrf'];
        }

        // database before
        $blueprintBefore = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/blueprint/slug_1/edit/', $params);

        if ($hasRedirection) {
            static::assertSame('/blueprint/slug_1/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // database after
        $blueprintAfter = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');

        if ($isFormSuccess) {
            // title
            static::assertSame(Helper::trim($params['form-edit_informations-input-title']), $blueprintAfter['title']);

            // description
            if (Helper::trim($params['form-edit_informations-textarea-description']) === '') {
                static::assertNull($blueprintAfter['description']);
            } else {
                static::assertSame(Helper::trim($params['form-edit_informations-textarea-description']), $blueprintAfter['description']);
            }

            if (Helper::trim($params['form-edit_informations-textarea-tags']) === '') {
                static::assertNull($blueprintAfter['tags']);
            } else {
                $tagIDs = [];
                foreach ($tags as $tag) {
                    $tagIDs[] = $tag['id'];
                }
                $tagsStr = \implode(',', $tagIDs);
                if ($tagsStr === '') {
                    $tagsStr = null;
                }
                static::assertSame($tagsStr, $blueprintAfter['tags']);
            }

            if (!empty($tags)) {
                $tagRows = static::$db->selectAll('SELECT * FROM tags');
                if (\PHP_MAJOR_VERSION >= 8 && \PHP_MINOR_VERSION >= 1) {
                    foreach ($tagRows as $key => $value) {
                        $tagRows[$key]['id'] = (string) $value['id'];
                    }
                }

                static::assertSame($tags, $tagRows);
            }

            // video
            if (Helper::trim($params['form-edit_informations-input-video']) === '') {
                static::assertNull($blueprintAfter['video']);
                static::assertNull($blueprintAfter['video_provider']);
            } elseif ($params['form-edit_informations-input-video'] === 'youtu.be/5qap5aO4i9A' || $params['form-edit_informations-input-video'] === 'youtu.be/5qap5aO4i9A&=<script>alert(1)</script>"/><script>alert(1)</script>') {
                static::assertSame('//www.youtube.com/embed/5qap5aO4i9A', $blueprintAfter['video']);
                static::assertSame('youtube', $blueprintAfter['video_provider']);
            } elseif ($params['form-edit_informations-input-video'] === 'https://vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954') {
                static::assertSame('//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954', $blueprintAfter['video']);
                static::assertSame('peertube', $blueprintAfter['video_provider']);
            } else {
                static::assertNull($blueprintAfter['video']);
                static::assertNull($blueprintAfter['video_provider']);
            }
        } else {
            static::assertSame($blueprintBefore, $blueprintAfter);
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
        $fields = ['title', 'description', 'tags', 'video'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'title') {
                if (isset($params['form-edit_informations-input-title'])) {
                    $value = $hasValue ? Helper::trim($params['form-edit_informations-input-title']) : '';
                    if ($params['form-edit_informations-input-title'] === \chr(99999999)) {
                        $value = 'title_1';
                    }
                } else {
                    $value = 'title_1';
                }
                $this->doTestHtmlForm($response, '#form-edit_informations', $this->getHTMLFieldTitle($value, $hasError, $labelError));
            }

            if ($field === 'description') {
                $value = $hasValue ? Helper::trim($params['form-edit_informations-textarea-description']) : '';
                if (isset($params['form-edit_informations-textarea-description']) && $params['form-edit_informations-textarea-description'] === \chr(99999999)) {
                    $value = '';
                }
                $this->doTestHtmlForm($response, '#form-edit_informations', $this->getHTMLFieldDescription($value));
            }

            if ($field === 'tags') {
                if ($tagsTextarea === \chr(99999999)) {
                    $tagsTextarea = '';
                }
                $this->doTestHtmlForm($response, '#form-edit_informations', $this->getHTMLFieldTag($tagsTextarea, $tags));
            }

            if ($field === 'video') {
                $value = $hasValue ? Helper::trim($params['form-edit_informations-input-video']) : '';
                if (isset($params['form-edit_informations-input-video']) && $params['form-edit_informations-input-video'] === \chr(99999999)) {
                    $value = '';
                }
                $this->doTestHtmlForm($response, '#form-edit_informations', $this->getHTMLFieldVideo($value, $hasError, $labelError));
            }
        }
    }

    /** @throws SecurityException */
    protected function getHTMLFieldTitle(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_informations-input-title" id="form-edit_informations-label-title">Title <span class="form__label--info">(required)</span></label>
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-edit_informations-label-title form-edit_informations-label-title-error" aria-required="true" class="form__input form__input--invisible form__input--error" data-form-error-required="Title is required" data-form-has-container data-form-rules="required" id="form-edit_informations-input-title" name="form-edit_informations-input-title" type="text" value="{$v}"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-edit_informations-input-title" id="form-edit_informations-label-title-error">{$labelError}</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_informations-input-title" id="form-edit_informations-label-title">Title <span class="form__label--info">(required)</span></label>
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-edit_informations-label-title" aria-required="true" class="form__input form__input--invisible" data-form-error-required="Title is required" data-form-has-container data-form-rules="required" id="form-edit_informations-input-title" name="form-edit_informations-input-title" type="text" value="{$v}"/>
<span class="form__feedback"></span>
</div>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldDescription(string $value): string
    {
        $v = Security::escHTML($value);

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_informations-textarea-description" id="form-edit_informations-label-description">Description</label>
<div class="form__container form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-edit_informations-label-reason" class="form__input form__input--invisible form__input--textarea" id="form-edit_informations-textarea-description" name="form-edit_informations-textarea-description">{$v}</textarea>
<span class="form__feedback"></span>
</div>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldTag(string $value, array $tags): string
    {
        $v = Security::escHTML($value);

        $items = [];
        foreach ($tags as $tag) {
            $items[] = '<li class="tag__item"><span class="sr-only">' . Security::escHTML($tag['name']) . '</span><button aria-label="Remove ' . Security::escAttr($tag['name']) . ' from the list" class="block__link block__link--delete block__link--tag">' . Security::escHTML($tag['name']) . '</button></li>';
        }
        $itemsStr = \implode("\n", $items);
        if (!empty($itemsStr)) {
            $itemsStr = "\n" . $itemsStr;
        }

        return <<<HTML
<div class="form__element"
data-tag
data-tag-aria-label="Remove %s from the list"
data-tag-form-input-id="form-edit_informations-input-tag"
data-tag-form-textarea-id="form-edit_informations-textarea-tags"
data-tag-item-class="block__link block__link--delete block__link--tag"
data-tag-list-id="form-edit_informations-ul-tags"
data-tag-new-id="form-edit_informations-ul-tags-li-add-tag"
data-tag-new-keys=",|Enter"
data-tag-regex-keys="^[a-zA-Z0-9._ -]{1}$"
data-tag-regex-tag="^[a-zA-Z0-9._ -]*$"
data-tag-srspeak-add="%s added"
data-tag-srspeak-delete="%s deleted">
<ul class="tag__items" id="form-edit_informations-ul-tags">{$itemsStr}
<li class="tag__add" id="form-edit_informations-ul-tags-li-add-tag">
<div class="form__element">
<input aria-labelledby="form-edit_informations-label-tag" class="form__input" id="form-edit_informations-input-tag" placeholder="Add a new tag" type="text">
</div>
</li>
</ul>
<textarea aria-hidden="true" aria-label="List of tags" hidden id="form-edit_informations-textarea-tags" name="form-edit_informations-textarea-tags">{$v}</textarea>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldVideo(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($v === 'youtu.be&#x2F;5qap5aO4i9A') {
            $v = '&#x2F;&#x2F;www.youtube.com&#x2F;embed&#x2F;5qap5aO4i9A';
        }

        if ($v === 'https&#x3A;&#x2F;&#x2F;vloggers.social&#x2F;videos&#x2F;watch&#x2F;5636c3ff-7009-47da-af53-5f0857a26954') {
            $v = '&#x2F;&#x2F;vloggers.social&#x2F;videos&#x2F;embed&#x2F;5636c3ff-7009-47da-af53-5f0857a26954';
        }

        if ($v === 'youtu.be&#x2F;5qap5aO4i9A&amp;&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&quot;&#x2F;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;') {
            $v = '&#x2F;&#x2F;www.youtube.com&#x2F;embed&#x2F;5qap5aO4i9A';
        }

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_informations-input-video" id="form-edit_informations-label-video">Video</label>
<div class="form__container form__container--error">
<input aria-invalid="false" aria-describedby="form-edit_informations-span-video_help" aria-labelledby="form-edit_informations-label-video form-edit_informations-label-video-error" class="form__input form__input--invisible form__input--error" data-form-error-aria_invalid="Cannot detect video to embed" data-form-has-container data-form-rules="aria_invalid" id="form-edit_informations-input-video" name="form-edit_informations-input-video" type="text" value="{$v}"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-edit_informations-input-video" id="form-edit_informations-label-video-error">{$labelError}</label>
<span class="form__help" id="form-edit_informations-span-video_help">Accepts only <span class="form__help--emphasis">YouTube</span>, <span class="form__help--emphasis">Vimeo</span>, <span class="form__help--emphasis">Dailymotion</span>, <span class="form__help--emphasis">PeerTube</span>, <span class="form__help--emphasis">Bilibili</span> or <span class="form__help--emphasis">Niconico</span> urls</span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_informations-input-video" id="form-edit_informations-label-video">Video</label>
<div class="form__container">
<input aria-invalid="false" aria-describedby="form-edit_informations-span-video_help" aria-labelledby="form-edit_informations-label-video" class="form__input form__input--invisible" data-form-error-aria_invalid="Cannot detect video to embed" data-form-has-container data-form-rules="aria_invalid" id="form-edit_informations-input-video" name="form-edit_informations-input-video" type="text" value="{$v}"/>
<span class="form__feedback"></span>
</div>
<span class="form__help" id="form-edit_informations-span-video_help">Accepts only <span class="form__help--emphasis">YouTube</span>, <span class="form__help--emphasis">Vimeo</span>, <span class="form__help--emphasis">Dailymotion</span>, <span class="form__help--emphasis">PeerTube</span>, <span class="form__help--emphasis">Bilibili</span> or <span class="form__help--emphasis">Niconico</span> urls</span>
</div>
HTML;
    }
}
