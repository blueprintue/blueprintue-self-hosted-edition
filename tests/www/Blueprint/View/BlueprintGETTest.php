<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintGETTest extends TestCase
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

    public function dataCasesBlueprintGET(): array
    {
        return [
            'no blueprint - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                ],
                'user_id'            => null,
                'slug'               => 'slug_incorrect',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'no blueprints - no published_at - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), null, 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'no blueprints - public but expiration passed - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, expiration) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public', '2020-01-01 01:01:01')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'no blueprints - deleted - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'visitor user - public blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at) VALUES (1, 'slug_public', 'file', 'visitor user - public blueprint - OK', 1, utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_public',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'visitor user - public blueprint - OK',
                'header_title'       => 'visitor user - public blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'visitor user - unlisted blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'visitor user - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_unlisted',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'visitor user - unlisted blueprint - OK',
                'header_title'       => 'visitor user - unlisted blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'visitor user - private blueprint - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_private', 'file', 'visitor user - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_private',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'visitor user - deleted blueprint - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 'slug_private', 'file', 'visitor user - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_private',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'user lambda - public blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'user lambda - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_public',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'user lambda - public blueprint - OK',
                'header_title'       => 'user lambda - public blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'user lambda - unlisted blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'user lambda - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_unlisted',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'user lambda - unlisted blueprint - OK',
                'header_title'       => 'user lambda - unlisted blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'user lambda - private blueprint - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_private', 'file', 'user lambda - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_private',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'user lambda - deleted blueprint - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 'slug_private', 'file', 'user lambda - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_private',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'author - public blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'author - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_public',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'author - public blueprint - OK',
                'header_title'       => 'author - public blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'author - unlisted blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'author - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_unlisted',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'author - unlisted blueprint - OK',
                'header_title'       => 'author - unlisted blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'author - private blueprint - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, description) VALUES (1, 'slug_private', 'file', 'author - private blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'private', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_private',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'author - private blueprint - OK',
                'header_title'       => 'author - private blueprint - OK posted by member | This is a base title',
                'header_description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has',
            ],
            'author - deleted blueprint - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, description, deleted_at) VALUES (1, 'slug_private', 'file', 'author - private blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'private', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_private',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
        ];
    }

    public function dataCasesBlueprintGETVersionAccess(): array
    {
        return [
            'visitor user - public blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at) VALUES (1, 'slug_public', 'file', 'visitor user - public blueprint - OK', 1, utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_public/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'visitor user - public blueprint - OK',
                'header_title'       => 'visitor user - public blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'visitor user - public blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at) VALUES (1, 'slug_public', 'file', 'visitor user - public blueprint - OK', 1, utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_public/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'visitor user - public blueprint - no blueprints versions (not realistic case) - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at) VALUES (1, 'slug_public', 'file', 'visitor user - public blueprint - OK', 1, utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_public',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'visitor user - unlisted blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'visitor user - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_unlisted/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'visitor user - unlisted blueprint - OK',
                'header_title'       => 'visitor user - unlisted blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'visitor user - unlisted blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'visitor user - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => null,
                'slug'               => 'slug_unlisted/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'user lambda - public blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'user lambda - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_public/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'user lambda - public blueprint - OK',
                'header_title'       => 'user lambda - public blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'user lambda - public blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'user lambda - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_public/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'user lambda - unlisted blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'user lambda - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_unlisted/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'user lambda - unlisted blueprint - OK',
                'header_title'       => 'user lambda - unlisted blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'user lambda - unlisted blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'user lambda - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 10,
                'slug'               => 'slug_unlisted/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'author - public blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'author - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_public/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'author - public blueprint - OK',
                'header_title'       => 'author - public blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'author - public blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'author - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_public/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'author - unlisted blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'author - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_unlisted/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'author - unlisted blueprint - OK',
                'header_title'       => 'author - unlisted blueprint - OK posted by member | This is a base title',
                'header_description' => 'No description provided',
            ],
            'author - unlisted blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'author - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_unlisted/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
            'author - private blueprint - valid version - OK' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, description) VALUES (1, 'slug_private', 'file', 'author - private blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'private', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_private/1',
                'status_code'        => 200,
                'location'           => null,
                'title'              => 'author - private blueprint - OK',
                'header_title'       => 'author - private blueprint - OK posted by member | This is a base title',
                'header_description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has',
            ],
            'author - private blueprint - invalid version - KO' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, description) VALUES (1, 'slug_private', 'file', 'author - private blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'private', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'user_id'            => 1,
                'slug'               => 'slug_private/2',
                'status_code'        => 301,
                'location'           => '/',
                'title'              => null,
                'header_title'       => null,
                'header_description' => null,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintGET
     * @dataProvider dataCasesBlueprintGETVersionAccess
     *
     * @param array       $sqlQueries
     * @param int|null    $userID
     * @param string      $slugBlueprint
     * @param int         $statusCode
     * @param string|null $location
     * @param string|null $title
     * @param string|null $headerTitle
     * @param string|null $headerDescription
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    public function testBlueprintGET(array $sqlQueries, ?int $userID, string $slugBlueprint, int $statusCode, ?string $location, ?string $title, ?string $headerTitle, ?string $headerDescription): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user in $_SESSION
        $session = ['remove' => [], 'set' => []];
        if ($userID !== null) {
            $session['set']['userID'] = $userID;
        } else {
            $session['remove'][] = 'userID';
        }

        // init session
        $this->getResponseFromApplication('GET', '/', [], $session);

        // get blueprint
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slugBlueprint . '/');
        $this->doTestHasResponseWithStatusCode($response, $statusCode);
        if ($location !== null) {
            static::assertSame($location, $response->getHeaderLine('Location'));
        }

        if ($title !== null) {
            $this->doTestHtmlBody($response, '<h1 class="blueprint__title">' . Security::escHTML($title) . '</h1>');
        }

        if ($statusCode !== 200) {
            return;
        }

        $this->doTestHtmlHead($response, [
            'title'       => Security::escHTML($headerTitle),
            'description' => Security::escAttr($headerDescription),
        ]);

        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasLinkBlueprintActive($response);
    }
}
