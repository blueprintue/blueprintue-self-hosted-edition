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
        include Application::getFolder('VIEWS') . 'www/parts/nav_only_logo.php';
        ?>
    </header>
    <main class="main">
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Confirm Account</h2>
                <?php if ($data['is_confirmed_account'] === null) { ?>
                    <p>Welcome to <?php echo Security::escHTML($data['site_name']); ?></p><p>Before log in to your account you need to confirm it.<br />You will receive an email with a link for the confirmation.</p>
                <?php } elseif ($data['is_confirmed_account'] === true) { ?>
                    <p>Your account is now confirmed!<br />You can now log to your account.<br /><a class="blog__link" href="/#popin-login">Go back to homepage for log in.</a></p>
                    <script>setTimeout(function(){window.location.href = '/#popin-login'}, 5000);</script>
                <?php } else { ?>
                    <p>Your account is maybe already confirmed or your confirmed token is invalid.<br /><a class="blog__link" href="/#popin-login">Go back to homepage for log in.</a></p>
                    <script>setTimeout(function(){window.location.href = '/#popin-login'}, 5000);</script>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <script src="/site.js"></script>
</body>
