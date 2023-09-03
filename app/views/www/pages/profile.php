<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

/* @var $data array */
?>
<body>
    <div class="background"></div>
    <header>
        <?php
        include Application::getFolder('VIEWS') . 'www/parts/nav.php';
        ?>
    </header>
    <main class="main">
        <div class="block__container block__container--first">
            <div class="block__element">
                <div class="profile">
                    <div class="profile__avatar-area">
                        <?php if ($data['avatar'] !== '' && $data['avatar'] !== null) { ?>
                        <img alt="<?php echo Security::escAttr($data['username']); ?> avatar" class="profile__avatar-container" src="<?php echo Security::escAttr($data['avatar']); ?>"/>
                        <?php } else { ?>
                        <div class="profile__avatar-container profile__avatar-container--background">
                            <svg class="profile__avatar-svg">
                                <use href="/sprite/sprite.svg#avatar"></use>
                            </svg>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="profile__name-area">
                        <h2 class="profile__name"><?php echo Security::escHTML($data['username']); ?></h2>
                        <?php if ($data['can_edit']) { ?>
                        <a class="block__link block__link--edit-profile" href="/profile/<?php echo Security::escAttr(Session::get('slug')); ?>/edit/">Edit profile</a>
                        <?php } ?>
                    </div>
                    <div class="profile__stats-area">
                        <ul class="profile__stats">
                            <li class="profile__stat">
                                <span class="profile__stat-number"><?php echo Security::escHTML($data['counters']['blueprints']); ?></span> <?php echo Security::escHTML($data['labels']['blueprints']); ?>
                            </li>
                            <li class="profile__stat profile__stat--last">
                                <span class="profile__stat-number"><?php echo Security::escHTML($data['counters']['comments']); ?></span> <?php echo Security::escHTML($data['labels']['comments']); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="profile__hr-area">
                        <hr class="profile__hr"/>
                    </div>
                    <div class="profile__about-area">
                        <p class="profile__about-bio"><?php echo \nl2br(Security::escHTML($data['bio'])); ?></p>
                        <?php if ($data['links']['website'] !== '') { ?>
                        <p class="profile__about-website">Website: <a class="profile__about-website--link" href="<?php echo Security::escAttr($data['links']['website']); ?>" rel="noopener noreferrer nofollow" target="_blank"><?php echo Security::escHTML($data['website']); ?></a></p>
                        <?php } ?>
                    </div>
                    <div class="profile__networks-area">
                        <ul class="profile__networks">
                            <?php if ($data['links']['facebook'] !== '') { ?>
                            <li class="profile__network">
                                <a aria-label="Facebook" class="profile__network-link" href="<?php echo Security::escAttr($data['links']['facebook']); ?>" rel="noopener noreferrer nofollow" target="_blank">
                                    <svg aria-hidden="true" class="profile__network-svg profile__network-svg--facebook">
                                        <use href="/sprite/sprite.svg#icon-facebook"></use>
                                    </svg>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($data['links']['twitter'] !== '') { ?>
                            <li class="profile__network">
                                <a aria-label="Twitter" class="profile__network-link" href="<?php echo Security::escAttr($data['links']['twitter']); ?>" rel="noopener noreferrer nofollow" target="_blank">
                                    <svg aria-hidden="true" class="profile__network-svg profile__network-svg--twitter">
                                        <use href="/sprite/sprite.svg#icon-twitter"></use>
                                    </svg>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($data['links']['github'] !== '') { ?>
                            <li class="profile__network">
                                <a aria-label="GitHub" class="profile__network-link" href="<?php echo Security::escAttr($data['links']['github']); ?>" rel="noopener noreferrer nofollow" target="_blank">
                                    <svg aria-hidden="true" class="profile__network-svg">
                                        <use href="/sprite/sprite.svg#icon-github"></use>
                                    </svg>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($data['links']['youtube'] !== '') { ?>
                            <li class="profile__network">
                                <a aria-label="Youtube" class="profile__network-link" href="<?php echo Security::escAttr($data['links']['youtube']); ?>" rel="noopener noreferrer nofollow" target="_blank">
                                    <svg aria-hidden="true" class="profile__network-svg">
                                        <use href="/sprite/sprite.svg#icon-youtube"></use>
                                    </svg>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($data['links']['twitch'] !== '') { ?>
                            <li class="profile__network">
                                <a aria-label="Twitch" class="profile__network-link" href="<?php echo Security::escAttr($data['links']['twitch']); ?>" rel="noopener noreferrer nofollow" target="_blank">
                                    <svg aria-hidden="true" class="profile__network-svg profile__network-svg--twitch">
                                        <use href="/sprite/sprite.svg#icon-twitch"></use>
                                    </svg>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($data['links']['unreal'] !== '') { ?>
                            <li class="profile__network">
                                <a aria-label="Unreal" class="profile__network-link" href="<?php echo Security::escAttr($data['links']['unreal']); ?>" rel="noopener noreferrer nofollow" target="_blank">
                                    <svg aria-hidden="true" class="profile__network-svg">
                                        <use href="/sprite/sprite.svg#icon-unreal"></use>
                                    </svg>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
            <div class="block__element">
                <h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
                <hr class="block__hr block__hr--small"/>
            </div>
            <?php
            include Application::getFolder('VIEWS') . 'www/parts/blueprints_list.php';
            ?>
            <div class="block__element">
                <?php echo $data['pagination']; ?>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <script src="/site.js"></script>
</body>