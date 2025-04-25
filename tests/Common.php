<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests;

use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\Environment;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Http\Message\Response;
use Rancoud\Http\Message\ServerRequest;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

trait Common
{
    protected static int $userID = 1;
    protected static int $anonymousID = 2;

    protected string $navBarLogoOnly = <<<'HTML'
<nav class="nav">
    <div class="nav__container" id="nav__container">
        <div class="nav__left-side-container">
            <a aria-label="Home of this_site_name" href="/">
                <img class="nav__logo-svg" src="/blueprintue-self-hosted-edition_logo.png"/>
            </a>
        </div>
    </div>
</nav>
HTML;

    protected static ?Database $db = null;

    /** @throws DatabaseException */
    protected static function setDatabase(): void
    {
        if (static::$db !== null) {
            return;
        }

        $params = [
            'driver'   => 'mysql',
            'user'     => 'blueprintue-self-hosted-edition',
            'password' => 'blueprintue-self-hosted-edition',
            'host'     => '127.0.0.1',
            'database' => 'blueprintue-self-hosted-edition'
        ];

        static::$db = new Database(new Configurator($params));
    }

    /** @throws DatabaseException */
    protected static function setDatabaseEmptyStructure(): void
    {
        $ds = \DIRECTORY_SEPARATOR;
        static::setDatabase();
        static::$db->dropTables('blueprints', 'blueprints_version', 'comments', 'sessions', 'tags', 'users', 'users_api', 'users_infos');
        static::$db->useSqlFile(__DIR__ . $ds . 'sql' . $ds . 'start.sql');
    }

    /** @throws DatabaseException */
    protected static function addUsers(): void
    {
        static::setDatabase();
        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (1, 'member', null, 'member', 'member@mail', now())");
        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', now())");

        static::$db->insert('replace into users_infos (id_user, count_public_blueprint, count_public_comment, count_private_blueprint, count_private_comment) values (1, 0, 0, 0, 0)');
        static::$db->insert('replace into users_infos (id_user, count_public_blueprint, count_public_comment, count_private_blueprint, count_private_comment) values (2, 0, 0, 0, 0)');
    }

    protected function getContentBetweenTags(string $body, string $tagStart, string $tagEnd): string
    {
        $startPos = \mb_strpos($body, $tagStart);
        $subBody = \mb_substr($body, $startPos);

        $stopPos = \mb_strpos($subBody, $tagEnd);

        return \mb_substr($subBody, 0, $stopPos + \mb_strlen($tagEnd));
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws \Exception
     */
    protected function getResponseFromApplication(string $method, string $url, array $params = [], array $session = [], array $cookies = [], array $queryParams = [], array $uploadedFiles = [], array $additionalsFolders = [], array $headers = [], string $envFile = 'tests.env'): ?Response
    {
        $ds = \DIRECTORY_SEPARATOR;
        $folders = [
            'ROOT'    => \dirname(__DIR__),
            'ROUTES'  => \dirname(__DIR__) . $ds . 'app' . $ds . 'routes',
            'VIEWS'   => \dirname(__DIR__) . $ds . 'app' . $ds . 'views',
            'STORAGE' => \dirname(__DIR__) . $ds . 'tests' . $ds . 'storage_test',
        ];

        $folders += $additionalsFolders;

        $env = new Environment(__DIR__, $envFile);

        $_SERVER['HTTP_HOST'] = $env->get('HOST');
        $_SERVER['HTTPS'] = ($env->get('HTTPS') === true) ? 'on' : 'off';

        $app = new Application($folders, $env);

        // for better perf, reuse the same database connexion
        static::setDatabase();
        Application::setDatabase(static::$db);

        $request = new ServerRequest($method, $url);
        if (\count($params) > 0) {
            $request = $request->withParsedBody($params);
        }

        if (\count($cookies) > 0) {
            $request = $request->withCookieParams($cookies);
        }

        if (\count($queryParams) > 0) {
            $request = $request->withQueryParams($queryParams);
        }

        if (\count($uploadedFiles) > 0) {
            $request = $request->withUploadedFiles($uploadedFiles);
        }

        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        $response = $app->run($request);

        if (!empty($session)) {
            Session::setReadWrite();
            foreach ($session['remove'] as $key) {
                Session::remove($key);
            }

            foreach ($session['set'] as $key => $value) {
                Session::set($key, $value);
            }
        }

        if (Session::isReadOnly() === false) {
            Session::commit();
        }

        return $response;
    }

    protected function doTestHasResponseWithStatusCode(Response $response, int $code): void
    {
        static::assertNotNull($response);
        static::assertSame($code, $response->getStatusCode());
    }

    protected function doTestHtmlHead(Response $response, array $headers): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<head>', '</head>');

        static::assertStringContainsString('<title>' . $headers['title'] . '</title>', $body);
        static::assertStringContainsString('<meta content="' . $headers['description'] . '" name="description">', $body);
    }

    protected function doTestHtmlBody(Response $response, string $content): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<body>', '</body>');
        $lines = \preg_split("/\r\n|\n|\r/", $body);
        $lines = \array_map('trim', $lines);
        $body = \implode("\n", $lines);

        static::assertStringContainsString($content, $body);
    }

    protected function doTestHtmlBodyNot(Response $response, string $content): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<body>', '</body>');
        $lines = \preg_split("/\r\n|\n|\r/", $body);
        $lines = \array_map('trim', $lines);
        $body = \implode("\n", $lines);

        static::assertStringNotContainsString($content, $body);
    }

    protected function doTestHtmlForm(Response $response, string $action, string $content): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<form action="' . $action . '"', '</form>');
        $lines = \preg_split("/\r\n|\n|\r/", $body);
        $lines = \array_map('trim', $lines);
        $body = \implode("\n", $lines);

        static::assertStringContainsString($content, $body);
    }

    protected function doTestHtmlFormNot(Response $response, string $action, string $content): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<form action="' . $action . '"', '</form>');
        $lines = \preg_split("/\r\n|\n|\r/", $body);
        $lines = \array_map('trim', $lines);
        $body = \implode("\n", $lines);

        static::assertStringNotContainsString($content, $body);
    }

    protected function doTestHtmlMain(Response $response, string $content): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<main', '</main>');
        $lines = \preg_split("/\r\n|\n|\r/", $body);
        $lines = \array_map('trim', $lines);
        $body = \implode("\n", $lines);

        static::assertStringContainsString($content, $body);
    }

    protected function doTestHtmlMainNot(Response $response, string $content): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<main', '</main>');
        $lines = \preg_split("/\r\n|\n|\r/", $body);
        $lines = \array_map('trim', $lines);
        $body = \implode("\n", $lines);

        static::assertStringNotContainsString($content, $body);
    }

    protected function doTestNavBarIsComplete(Response $response): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<header>', '</header>');

        static::assertStringNotContainsString($this->navBarLogoOnly, $body);
    }

    protected function doTestNavBarIsLogoOnly(Response $response): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<header>', '</header>');

        static::assertStringContainsString($this->navBarLogoOnly, $body);
    }

    protected function doTestNavBarHasNoLinkActive(Response $response): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<header>', '</header>');
        $body = $this->getContentBetweenTags($body, '<nav class="nav">', '</nav>');
        $body = $this->getContentBetweenTags($body, '<ul class="nav__center-side-container" id="nav__center-side-container">', '</ul>');

        static::assertStringNotContainsString('nav__link--active', $body);
    }

    protected function doTestNavBarHasLinkHomeActive(Response $response): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<header>', '</header>');
        $body = $this->getContentBetweenTags($body, '<nav class="nav">', '</nav>');
        $body = $this->getContentBetweenTags($body, '<ul class="nav__center-side-container" id="nav__center-side-container">', '</ul>');
        $linkHome = $this->getContentBetweenTags($body, '<a', '>Create blueprint</a>');
        $linkBlueprints = $this->getContentBetweenTags($body, 'blueprint</a>', '>Blueprints</a>');

        static::assertStringContainsString('nav__link--active', $linkHome);
        static::assertStringNotContainsString('nav__link--active', $linkBlueprints);
    }

    protected function doTestNavBarHasLinkBlueprintActive(Response $response): void
    {
        $body = (string) $response->getBody();

        $body = $this->getContentBetweenTags($body, '<header>', '</header>');
        $body = $this->getContentBetweenTags($body, '<nav class="nav">', '</nav>');
        $body = $this->getContentBetweenTags($body, '<ul class="nav__center-side-container" id="nav__center-side-container">', '</ul>');
        $linkHome = $this->getContentBetweenTags($body, '<a', '>Create blueprint</a>');
        $linkBlueprints = $this->getContentBetweenTags($body, '<a', '>Blueprints</a>');

        static::assertStringNotContainsString('nav__link--active', $linkHome);
        static::assertStringContainsString('nav__link--active', $linkBlueprints);
    }

    protected function createBlueprintFile(string $fileID, string $version = '1'): string
    {
        $ds = \DIRECTORY_SEPARATOR;
        $storageFolder = \dirname(__DIR__) . $ds . 'tests' . $ds . 'storage_test' . $ds;

        $caracters = \mb_str_split($fileID);
        $subfolder = '';
        foreach ($caracters as $c) {
            $subfolder .= $c . $ds;

            $dir = $storageFolder . $subfolder;
            if (!\is_dir($dir) && !\mkdir($dir) && !\is_dir($dir)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dir));
            }
        }
        $subfolder = \mb_strtolower($subfolder);
        $fullpath = $storageFolder . $subfolder . $fileID . '-' . $version . '.txt';

        $content = $fullpath . '<script>alert(1)</script>';
        \file_put_contents($fullpath, $content);

        return $content;
    }

    protected function getHostname(): string
    {
        static $hostname;
        if ($hostname !== null) {
            return $hostname;
        }

        $scheme = ($_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $hostname = $scheme . $_SERVER['HTTP_HOST'];

        return $scheme . $_SERVER['HTTP_HOST'];
    }

    /**
     * @throws \Rancoud\Security\SecurityException
     * @throws \Exception
     */
    protected static function getEmailHTMLConfirmAccount(string $username): string
    {
        \ob_start();
        $ds = \DIRECTORY_SEPARATOR;
        require \dirname(__DIR__) . $ds . 'app' . $ds . 'views/emails/confirm_account.html';

        $html = \ob_get_clean();

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $search = [
            '{{URL}}',
            '{{YEAR}}',
            '{{SITE_NAME_HTML}}',
            '{{SITE_NAME_ATTR}}',
            '{{USERNAME}}',
            '{{MAIL_HEADER_LOGO_PATH}}',
        ];

        $replace = [
            'https://blueprintue.test/confirm-account/?confirmed_token={{TOKEN}}',
            $now->format('Y'),
            Security::escHTML('this_site_name'),
            Security::escAttr('this_site_name'),
            Security::escHTML($username),
            Security::escAttr('https://blueprintue.test/full-logo.png'),
        ];

        return \str_replace($search, $replace, $html);
    }

    protected static function getEmailTextConfirmAccount(string $username): string
    {
        $text = 'Welcome to this_site_name' . "\n\n";
        $text .= 'We are excited to have you on board!' . "\n";
        $text .= 'To get started ' . $username . ', please copy the URL below to confirm your account:' . "\n\n";
        $text .= 'https://blueprintue.test/confirm-account/?confirmed_token={{TOKEN}}' . "\n";

        return $text;
    }

    protected static function cleanFiles(): void
    {
        $ds = \DIRECTORY_SEPARATOR;
        $storageFolder = \dirname(__DIR__) . $ds . 'tests' . $ds . 'storage_test' . $ds;

        $dir = new \RecursiveDirectoryIterator($storageFolder);
        $ite = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($ite, '/^.+\.txt$/i', \RegexIterator::GET_MATCH);
        foreach ($files as $file) {
            \unlink($file[0]);
        }
    }

    /** @throws \Exception */
    public static function getSince(string $publishedAt): string
    {
        $publishedAtObject = new \DateTime($publishedAt);
        $nowObject = new \DateTime();
        if ($publishedAtObject > $nowObject) {
            return 'few seconds ago';
        }

        $strings = [' years ago', ' months ago', ' days ago', ' hours ago', ' mins ago'];
        $diffDateObject = $publishedAtObject->diff($nowObject);
        $dateTrick = \explode('|', $diffDateObject->format('%y|%m|%a|%h|%i'));

        for ($i = 0; $i < 5; ++$i) {
            $value = (int) $dateTrick[$i];
            if ($value >= 1) {
                return $value . $strings[$i];
            }
        }

        return 'few seconds ago';
    }
}

function isPHPUnit(): void
{
}
