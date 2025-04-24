<?php

/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use app\helpers\Helper;
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
        <div class="block__container block__container--first block__container--black block__container--no-padding">
            <div class="block__element--iframe" id="blueprint-render-playground"></div>
        </div>
        <div hidden class="hidden">
            <label for="data-previous-content">Blueprint Previous Content</label>
            <textarea class="hidden" id="data-previous-content"><?php echo Security::escHTML($data['blueprint']['previous_content']); ?></textarea>
            <label for="data-current-content">Blueprint Current Content</label>
            <textarea class="hidden" id="data-current-content"><?php echo Security::escHTML($data['blueprint']['current_content']); ?></textarea>
        </div>
        <div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
            <div class="block__element">
                <a class="block__link block__link--tag" href="<?php echo Security::escAttr($data['blueprint_back_url']); ?>">Back</a>
                <?php if ($data['blueprint']['versions']['count'] > 1) { ?>
                <span class="blueprint__versions-title">Versions</span>
                    <?php foreach ($data['blueprint']['versions']['versions'] as $date => $versions) { ?>
                <div class="blueprint__versions-header">
                    <svg class="blueprint__version-svg">
                        <use href="/sprite/sprite.svg#icon-blueprintue"></use>
                    </svg>
                    <span class="blueprint__version-date"><?php echo Helper::getDateFormattedWithUserTimezone($date, 'F j, Y'); ?></span>
                </div>
                <ol class="blueprint__versions" reversed>
                        <?php foreach ($versions as $version) {
                            $cssClass = ['blueprint__version'];
                            if ($version['last'] === true) {
                                $cssClass[] = 'blueprint__version--last';
                            }
                            if ($version['version'] === $data['blueprint']['previous_version'] && $version['version'] === $data['blueprint']['current_version']) {
                                $cssClass[] = 'blueprint__version--current';
                            } else {
                                if ($version['version'] === $data['blueprint']['previous_version']) {
                                    $cssClass[] = 'blueprint__version--diff-previous';
                                }
                                if ($version['version'] === $data['blueprint']['current_version']) {
                                    $cssClass[] = 'blueprint__version--diff-current';
                                }
                            } ?>
                    <li class="<?php echo \implode(' ', $cssClass); ?>">
                        <div class="blueprint__version-left">
                            <p><?php echo \nl2br(Security::escHTML($version['reason'])); ?></p>
                        </div>
                            <?php if ($version['diff_url'] !== '') { ?>
                        <div>
                            <a class="block__link block__link--no-margin" href="<?php echo Security::escAttr($version['diff_url']); ?>">Diff</a>
                        </div>
                            <?php } ?>
                    </li>
                            <?php } ?>
                </ol>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <link href="/bue-render/render.css" rel="stylesheet">
    <link href="/bue-render/diff.css" rel="stylesheet">
    <script src="/site.js"></script>
    <script src="/bue-render/render.js"></script>
    <script src="/bue-render/diff.js"></script>
    <script>
        new window.blueprintUE.diff.Main(
            document.getElementById('data-previous-content').value,
            document.getElementById('data-current-content').value,
            document.getElementById('blueprint-render-playground'),
            {height:"643px"}
        ).start();
    </script>
</body>
