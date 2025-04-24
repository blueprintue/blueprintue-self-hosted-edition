<?php

/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Security\Security;

/* @var $data array */
?>
<footer class="footer">
    <div class="footer__container">
        <div class="footer__logo">
            <a aria-label="Home of <?php echo Security::escAttr(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')); ?>" href="/" title="Home of <?php echo Security::escAttr(Application::getConfig()->get('SITE_NAME', 'blueprintUE')); ?>">
                <img class="nav__logo-svg" src="/blueprintue-self-hosted-edition_logo.png"/>
            </a>
        </div>

        <div class="footer__legals">
            <p>
                Portions of the materials used are trademarks and/or copyrighted works of Epic Games, Inc. All rights reserved by Epic. This material is not official and is not endorsed by Epic.<br />
                Unreal, Unreal Engine, the circle-U logo and the Powered by Unreal Engine logo are trademarks or registered trademarks of Epic Games, Inc. in the United States and elsewhere.
            </p>
        </div>

        <ul class="footer__links" id="footer__links">
            <li class="footer__link-container">
                <a class="footer__link" href="/contact/">Contact</a>
            </li>
            <li class="footer__link-container">
                <a class="footer__link" href="/terms-of-service/">Terms of service</a>
            </li>
            <li class="footer__link-container">
                <a class="footer__link" href="/privacy-policy/">Privacy policy</a>
            </li>
        </ul>
    </div>
</footer>
