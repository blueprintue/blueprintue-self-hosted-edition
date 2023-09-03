<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Security\Security;

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
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Blueprint's <span class="block__title--emphasis">tags</span></h2>
                <?php if (empty($data['tags'])) { ?>
                <p>No tags for the moment</p>
                <?php } else { ?>
                <div class="tags block__markdown">
                    <?php foreach ($data['tags'] as $headerTag => $tags) { ?>
                    <div class="tags__list">
                        <h3><?php echo Security::escHTML($headerTag); ?></h3>
                        <ul>
                        <?php foreach ($tags as $tag) { ?>
                            <li><a href="/tag/<?php echo Security::escAttr($tag['slug']); ?>/1/"><?php echo Security::escHTML($tag['name']); ?></a></li>
                        <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <script src="/site.js"></script>
</body>