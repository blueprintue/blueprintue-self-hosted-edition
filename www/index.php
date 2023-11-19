<?php

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;

$ds = \DIRECTORY_SEPARATOR;
$rootDir = \dirname(__DIR__);
require $rootDir . $ds . 'vendor' . $ds . 'autoload.php';

$folders = [
    'ROOT'              => $rootDir,
    'ROUTES'            => $rootDir . $ds . 'app' . $ds . 'routes',
    'VIEWS'             => $rootDir . $ds . 'app' . $ds . 'views',
    'STORAGE'           => $rootDir . $ds . 'storage',
    'MEDIAS_AVATARS'    => __DIR__ . $ds . 'medias' . $ds . 'avatars',
    'MEDIAS_BLUEPRINTS' => __DIR__ . $ds . 'medias' . $ds . 'blueprints',
];

$request = null;

try {
    $env = new Rancoud\Environment\Environment($folders['ROOT']);
    $env->enableCache();
    $env->override(Rancoud\Environment\Environment::GETENV_ALL);

    // remove port from host name
    if (isset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT'])) {
        $pos = \mb_strpos($_SERVER['HTTP_HOST'], ':' . $_SERVER['SERVER_PORT']);
        if ($pos + \mb_strlen(':' . $_SERVER['SERVER_PORT']) === \mb_strlen($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = \mb_substr($_SERVER['HTTP_HOST'], 0, $pos);
        }
    }

    $app = new Application($folders, $env);
    $request = (new Factory())->createServerRequestFromGlobals();
    $response = $app->run($request);

    if ($response !== null) {
        $response->send();
    } else {
        (new Factory())->createResponse(404)->withBody(Rancoud\Http\Message\Stream::create('404'))->send();
    }
} catch (\Throwable $t) {
    // phpcs:disable
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error - blueprintUE self-hosted edition</title>

    <meta content="Error" name="description"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport"/>
    <link href="/site.css" rel="stylesheet">

    <!--[if IE]>
    <meta HTTP-EQUIV="REFRESH" content="0; url=/ie.html">
    <![endif]-->

    <!-- favicons -->
    <link href="/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180">
    <link href="/favicon-32x32.png" rel="icon" sizes="32x32" type="image/png">
    <link href="/favicon-16x16.png" rel="icon" sizes="16x16" type="image/png">
    <link crossorigin="use-credentials" href="/site.webmanifest" rel="manifest">
    <link color="#50e3c2" href="/safari-pinned-tab.svg" rel="mask-icon">
    <meta content="#1a1c1f" name="msapplication-TileColor">
    <meta content="#ffffff" name="theme-color">
</head>
<body>
    <div class="background"></div>
    <header>
        <nav class="nav">
            <div class="nav__container" id="nav__container">
                <div class="nav__left-side-container">
                    <a aria-label="Home of blueprintUE self-hosted edition" href="/">
                        <img class="nav__logo-svg" src="/blueprintue-self-hosted-edition_logo.png"/>
                    </a>
                </div>
            </div>
        </nav>
    </header>
    <main class="main">
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Error</h2>
                <div class="block__markdown">
                    <p>An error occured, please try later.</p>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__logo">
                <a aria-label="Home of blueprintUE self-hosted edition" href="/" title="Home of blueprintUE self-hosted edition">
                    <img class="nav__logo-svg" src="/blueprintue-self-hosted-edition_logo.png"/>
                </a>
            </div>
            <div class="footer__legals">
                <p>
                    Portions of the materials used are trademarks and/or copyrighted works of Epic Games, Inc. All rights reserved by Epic. This material is not official and is not endorsed by Epic.<br />
                    Unreal, Unreal Engine, the circle-U logo and the Powered by Unreal Engine logo are trademarks or registered trademarks of Epic Games, Inc. in the United States and elsewhere.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
HTML;
    // phpcs:enable
}
