<?php

/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Security\Security;

?>
<nav class="nav">
    <div class="nav__container" id="nav__container">
        <div class="nav__left-side-container">
            <a aria-label="Home of <?php echo Security::escAttr(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')); ?>" href="/">
                <img class="nav__logo-svg" src="/blueprintue-self-hosted-edition_logo.png"/>
            </a>
        </div>
    </div>
</nav>
