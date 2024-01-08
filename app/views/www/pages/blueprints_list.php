<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

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
        <?php if ($data['show_search']) { ?>
        <div class="block__container block__container--first">
            <div class="block__element">
                <h2 class="block__title">Search Parameters <span class="block__title--emphasis">blueprint</span></h2>
                <hr class="block__hr"/>

                <form action="/search/" id="form-search">
                    <div class="home__form">
                        <div class="form__element home__form--title">
                            <label class="form__label" for="form-search-input-query" id="form-search-label-query">Terms to search</label>
                            <input aria-invalid="false" aria-labelledby="form-search-label-query" class="form__input" id="form-search-input-query" name="form-search-input-query" type="text" value="<?php echo Security::escAttr($data['form-search-input-query']); ?>"/>
                        </div>
                        <div class="form__element home__form--selectors">
                            <label class="form__label" for="form-search-select-type" id="form-search-label-type">Type</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-search-label-type" aria-required="true" class="form__input form__input--select" id="form-search-select-type" name="form-search-select-type">
                                    <option value=""<?php echo ($data['form-search-select-type'] === '') ? ' selected="selected"' : ''; ?>>All</option>
                                    <option value="animation"<?php echo ($data['form-search-select-type'] === 'animation') ? ' selected="selected"' : ''; ?>>Animation</option>
                                    <option value="behavior-tree"<?php echo ($data['form-search-select-type'] === 'behavior-tree') ? ' selected="selected"' : ''; ?>>Behavior Tree</option>
                                    <option value="blueprint"<?php echo ($data['form-search-select-type'] === 'blueprint') ? ' selected="selected"' : ''; ?>>Blueprint</option>
                                    <option value="material"<?php echo ($data['form-search-select-type'] === 'material') ? ' selected="selected"' : ''; ?>>Material</option>
                                    <option value="metasound"<?php echo ($data['form-search-select-type'] === 'metasound') ? ' selected="selected"' : ''; ?>>Metasound</option>
                                    <option value="niagara"<?php echo ($data['form-search-select-type'] === 'niagara') ? ' selected="selected"' : ''; ?>>Niagara</option>
                                    <option value="pcg"<?php echo ($data['form-search-select-type'] === 'pcg') ? ' selected="selected"' : ''; ?>>PCG</option>
                                </select>
                            </div>
                        </div>
                        <div class="form__element home__form--selectors">
                            <label class="form__label" for="form-search-select-ue_version" id="form-search-label-ue_version">UE version</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-search-label-ue_version" aria-required="true" class="form__input form__input--select" id="form-search-select-ue_version" name="form-search-select-ue_version">
                                    <option value=""<?php echo ($data['form-search-select-ue_version'] === '') ? ' selected="selected"' : ''; ?>>All</option>
                                    <?php
                                    foreach (Helper::getAllUEVersion() as $ueVersion) { ?>
                                        <option value="<?php echo Security::escAttr($ueVersion); ?>"<?php echo ($data['form-search-select-ue_version'] === $ueVersion) ? ' selected="selected"' : ''; ?>><?php echo Security::escHTML($ueVersion); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form__element form__element--align-button">
                            <input class="form__button form__button--primary" id="form-search-submit" name="form-search-submit" type="submit" value="Search"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php } ?>
        <div class="block__container<?php echo ($data['show_search']) ? ' block__container--white-grey block__container--shadow-top ' : ' block__container--first '; ?>block__container--last">
            <div class="block__element">
                <h2 class="block__title"><?php echo Security::escHTML($data['title']); ?><span class="block__title--emphasis"><?php echo Security::escHTML($data['title_emphasis']); ?></span></h2>
                <hr class="block__hr block__hr--small"/>
            </div>
            <?php
            include Application::getFolder('VIEWS') . 'www/parts/blueprints_list.php';
            ?>
            <div class="block__element">
                <?php echo $data['pagination']; ?>
            </div>
            <?php if ($data['show_links_other_types']) { ?>
            <div class="block__element">
                <h2 class="block__title block__title--center">Other Types of Blueprints</h2>
                <div class="block__links block__links--center">
                    <?php if ($data['type'] !== 'animation') { // phpcs:ignore?><a class="block__link" href="/type/animation/">Animation</a><?php } // phpcs:ignore?>
                    <?php if ($data['type'] !== 'behavior-tree') { // phpcs:ignore?><a class="block__link" href="/type/behavior-tree/">Behavior Tree</a><?php } // phpcs:ignore?>
                    <?php if ($data['type'] !== 'blueprint') { // phpcs:ignore?><a class="block__link" href="/type/blueprint/">Blueprint</a><?php } // phpcs:ignore?>
                    <?php if ($data['type'] !== 'material') { // phpcs:ignore?><a class="block__link" href="/type/material/">Material</a><?php } // phpcs:ignore?>
                    <?php if ($data['type'] !== 'metasound') { // phpcs:ignore?><a class="block__link" href="/type/metasound/">Metasound</a><?php } // phpcs:ignore?>
                    <?php if ($data['type'] !== 'niagara') { // phpcs:ignore?><a class="block__link" href="/type/niagara/">Niagara</a><?php } // phpcs:ignore?>
                    <?php if ($data['type'] !== 'pcg') { // phpcs:ignore?><a class="block__link" href="/type/pcg/">PCG</a><?php } // phpcs:ignore?>
                </div>
            </div>
            <?php } ?>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <script src="/site.js"></script>
</body>
