<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

/* @var $data array */
?>
<nav class="nav">
    <div class="nav__container" id="nav__container">
        <div class="nav__left-side-container">
            <a aria-label="Home of <?php echo Security::escAttr(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')); ?>" href="/">
                <img class="nav__logo-svg" src="/blueprintue-self-hosted-edition_logo.png"/>
            </a>
            <button aria-expanded="false" aria-label="Toggle navigation" class="nav__toggle" id="nav__toggle" type="button">
                <span class="nav__toggle-inner"></span>
            </button>
        </div>
        <ul class="nav__center-side-container" id="nav__center-side-container">
            <li>
                <a class="nav__link nav__link--add-margin-right<?php echo ($data['navbar_current_page'] === 'home') ? ' nav__link--active' : ''; ?>" href="/">Create pastebin</a>
            </li>
            <li>
                <a class="nav__link<?php echo ($data['navbar_current_page'] === 'blueprints_list' || $data['navbar_current_page'] === 'blueprint') ? ' nav__link--active' : ''; ?>" href="/type/blueprint/">Blueprints</a>
            </li>
        </ul>
        <div class="nav__right-side-container" id="nav__right-side-container">
            <div class="nav__search-container" id="nav__search-container" role="search">
                <button aria-label="Open search" class="nav__search-open" id="nav__search-open" type="button">
                    <svg aria-hidden="true" class="nav__search-open-svg">
                        <use href="/sprite/sprite.svg#icon-search"></use>
                    </svg>
                </button>
                <form action="/search/" class="nav__search-form" id="nav__search-form">
                    <input aria-label="Type your search here" class="nav__search-input" name="query" type="text" value="">
                </form>
                <button aria-label="Close search" class="nav__search-close" id="nav__search-close" type="button">
                    <svg aria-hidden="true" class="nav__search-close-svg">
                        <use href="/sprite/sprite.svg#icon-close"></use>
                    </svg>
                </button>
            </div>
            <div class="nav__theme-switcher">
                <button aria-label="Switch between dark and light mode" class="nav__theme-switcher-btn" data-theme-switcher type="button">
                    <svg aria-hidden="true" class="nav__theme-switcher-svg">
                        <use href="/sprite/sprite.svg#icon-theme-light"></use>
                    </svg>
                </button>
            </div>
            <div class="nav__user-container">
                <?php if (!Session::has('userID')) { ?>
                <a class="nav__user-button nav__user-button--left" href="#popin-login">Login</a>
                <a class="nav__user-button nav__user-button--right" href="#popin-register">Register</a>
                <?php } else { ?>
                <a class="nav__user-button nav__user-button--left" href="/profile/<?php echo Security::escAttr(Session::get('slug')); ?>/">Profile</a>
                <form class="nav__user-button nav__user-button--right" method="post" onclick="submit();">
                    <input name="form-logout-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-logout-hidden-csrf']); ?>"/>
                    <button class="nav__user-button-logout" type="submit">Logout</button>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
</nav>
