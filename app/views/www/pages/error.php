<?php

/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Rancoud\Application\Application;
use Rancoud\Security\Security;

/* @var $data array */
?>
<body>
    <div class="background"></div>
    <header>
        <?php
        include Application::getFolder('VIEWS') . 'www/parts/nav_only_logo.php';
        ?>
    </header>
    <main class="main">
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Error</h2>
                <div class="block__markdown">
                    <p><?php echo \nl2br(Security::escHTML($data['error_message'])); ?></p>
                </div>
                <a class="blog__link" href="/">Back to homepage</a>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <script src="/site.js"></script>
</body>