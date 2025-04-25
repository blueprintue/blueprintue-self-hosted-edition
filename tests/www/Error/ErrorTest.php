<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Error;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class ErrorTest extends TestCase
{
    use Common;

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
     * @throws SecurityException
     */
    public function testErrorGET(): void
    {
        // no error message -> redirect
        $response = $this->getResponseFromApplication('GET', '/error/', [], ['set' => ['flash_data' => ['error_message' => '<script>alert(1)</script>']], 'remove' => []]);
        $this->doTestHasResponseWithStatusCode($response, 301);
        static::assertSame('/', $response->getHeaderLine('Location'));

        // has error message -> show error message
        $response = $this->getResponseFromApplication('GET', '/error/', [], ['set' => [], 'remove' => []]);
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => 'Error | This is a base title',
            'description' => 'Error'
        ]);
        $v = Security::escHTML('<script>alert(1)</script>');

        $this->doTestNavBarIsLogoOnly($response);
        $this->doTestHtmlBody($response, <<<HTML
<main class="main">
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Error</h2>
<div class="block__markdown">
<p>{$v}</p>
</div>
<a class="blog__link" href="/">Back to homepage</a>
</div>
</div>
</main>
HTML);
    }
}
