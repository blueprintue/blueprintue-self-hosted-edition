<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use app\helpers\FormHelper;
use app\helpers\Helper;
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
                <h2 class="block__title">Paste your <span class="block__title--emphasis">blueprint</span></h2>
                <h3 class="block__subtitle">Copy and paste your blueprint in the form below and click on "Create your blueprint" to share your blueprint in new visual way!</h3>
                <hr class="block__hr"/>

                <?php
                /** @var FormHelper $formAddBlueprint */
                $formAddBlueprint = $data['form-add_blueprint'];
                ?>

                <?php if ($formAddBlueprint->hasErrorMessage()) { ?>
                <div class="block__info block__info--error" data-flash-error-for="form-add_blueprint" role="alert"><?php echo Security::escHTML($formAddBlueprint->getErrorMessage()); ?></div>
                <?php } ?>

                <form action="/" data-form-speak-error="Form is invalid:" id="form-add_blueprint" method="post">
                    <div class="home__form">
                        <div class="form__element home__form--title">
                            <label class="form__label" for="form-add_blueprint-input-title" id="form-add_blueprint-label-title">Title <span class="form__label--info">(required)</span></label>
                            <div class="form__container<?php echo $formAddBlueprint->getClassError('title', ' form__container--error'); ?>">
                                <input aria-invalid="false" aria-labelledby="form-add_blueprint-label-title<?php echo $formAddBlueprint->getClassError('title', ' form-add_blueprint-label-title-error'); ?>" aria-required="true" class="form__input form__input--invisible<?php echo $formAddBlueprint->getClassError('title', ' form__input--error'); ?>" data-form-error-required="Title is required" data-form-has-container data-form-rules="required" id="form-add_blueprint-input-title" name="form-add_blueprint-input-title" type="text" value="<?php echo Security::escAttr($formAddBlueprint->getInputValue('title')); ?>"/>
                                <span class="form__feedback<?php echo $formAddBlueprint->getClassError('title', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formAddBlueprint->getInputError('title') !== '') { ?>
                            <label class="form__label form__label--error" for="form-add_blueprint-input-title" id="form-add_blueprint-label-title-error"><?php echo Security::escHTML($formAddBlueprint->getInputError('title')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element home__form--selectors">
                            <label class="form__label" for="form-add_blueprint-select-exposure" id="form-add_blueprint-label-exposure">Exposure</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-add_blueprint-label-exposure<?php echo $formAddBlueprint->getClassError('exposure', ' form-add_blueprint-label-exposure-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formAddBlueprint->getClassError('exposure', ' form__input--error'); ?>" id="form-add_blueprint-select-exposure" name="form-add_blueprint-select-exposure">
                                    <option value="public"<?php echo $formAddBlueprint->getSelectedValue('exposure', 'public'); ?><?php echo ($formAddBlueprint->getInputValue('exposure') === '') ? ' selected="selected"' : ''; ?>>Public</option>
                                    <option value="unlisted"<?php echo $formAddBlueprint->getSelectedValue('exposure', 'unlisted'); ?>>Unlisted</option>
                                    <?php if (Session::has('userID')) { ?>
                                    <option value="private"<?php echo $formAddBlueprint->getSelectedValue('exposure', 'private'); ?>>Private</option>
                                    <?php } else { ?>
                                    <option value="private" disabled>Private (member only)</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php if ($formAddBlueprint->getInputError('exposure') !== '') { ?>
                            <label class="form__label form__label--error" for="form-add_blueprint-select-exposure" id="form-add_blueprint-label-exposure-error"><?php echo Security::escHTML($formAddBlueprint->getInputError('exposure')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element home__form--selectors">
                            <label class="form__label" for="form-add_blueprint-select-expiration" id="form-add_blueprint-label-expiration">Expiration</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-add_blueprint-label-expiration<?php echo $formAddBlueprint->getClassError('expiration', ' form-add_blueprint-label-expiration-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formAddBlueprint->getClassError('expiration', ' form__input--error'); ?>" id="form-add_blueprint-select-expiration" name="form-add_blueprint-select-expiration">
                                    <option value="never"<?php echo $formAddBlueprint->getSelectedValue('expiration', 'never'); ?><?php echo ($formAddBlueprint->getInputValue('expiration') === '') ? ' selected="selected"' : ''; ?>>Never</option>
                                    <option value="1h"<?php echo $formAddBlueprint->getSelectedValue('expiration', '1h'); ?>>1 hour</option>
                                    <option value="1d"<?php echo $formAddBlueprint->getSelectedValue('expiration', '1d'); ?>>1 day</option>
                                    <option value="1w"<?php echo $formAddBlueprint->getSelectedValue('expiration', '1w'); ?>>1 week</option>
                                </select>
                            </div>
                            <?php if ($formAddBlueprint->getInputError('expiration') !== '') { ?>
                            <label class="form__label form__label--error" for="form-add_blueprint-select-expiration" id="form-add_blueprint-label-expiration-error"><?php echo Security::escHTML($formAddBlueprint->getInputError('expiration')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element home__form--selectors">
                            <label class="form__label" for="form-add_blueprint-select-ue_version" id="form-add_blueprint-label-ue_version">UE version</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-add_blueprint-label-ue_version<?php echo $formAddBlueprint->getClassError('ue_version', ' form-add_blueprint-label-ue_version-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formAddBlueprint->getClassError('ue_version', ' form__input--error'); ?>" id="form-add_blueprint-select-ue_version" name="form-add_blueprint-select-ue_version">
                                    <?php
                                    $selectedUEVersion = ($formAddBlueprint->getInputValue('ue_version') === '') ? Helper::getCurrentUEVersion() : $formAddBlueprint->getInputValue('ue_version');
                                    foreach (Helper::getAllUEVersion() as $ueVersion) { ?>
                                    <option value="<?php echo Security::escAttr($ueVersion); ?>"<?php echo ($selectedUEVersion === $ueVersion) ? ' selected="selected"' : ''; ?>><?php echo Security::escHTML($ueVersion); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php if ($formAddBlueprint->getInputError('ue_version') !== '') { ?>
                            <label class="form__label form__label--error" for="form-add_blueprint-select-ue_version" id="form-add_blueprint-label-ue_version-error"><?php echo Security::escHTML($formAddBlueprint->getInputError('ue_version')); ?></label>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form__element">
                        <label class="form__label" for="form-add_blueprint-textarea-blueprint" id="form-add_blueprint-label-blueprint">Blueprint <span class="form__label--info">(required)</span></label>
                        <div class="form__container form__container--blueprint form__container--textarea<?php echo $formAddBlueprint->getClassError('blueprint', ' form__container--error'); ?>">
                            <textarea aria-invalid="false" aria-labelledby="form-add_blueprint-label-blueprint<?php echo $formAddBlueprint->getClassError('blueprint', ' form-add_blueprint-label-blueprint-error'); ?>" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--blueprint<?php echo $formAddBlueprint->getClassError('blueprint', ' form__input--error'); ?>" data-form-error-required="Blueprint is required" data-form-has-container data-form-rules="required" id="form-add_blueprint-textarea-blueprint" name="form-add_blueprint-textarea-blueprint"><?php echo Security::escHTML($formAddBlueprint->getInputValue('blueprint')); ?></textarea>
                            <span class="form__feedback<?php echo $formAddBlueprint->getClassError('blueprint', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formAddBlueprint->getInputError('blueprint') !== '') { ?>
                        <label class="form__label form__label--error" for="form-add_blueprint-textarea-blueprint" id="form-add_blueprint-label-blueprint-error"><?php echo Security::escHTML($formAddBlueprint->getInputError('blueprint')); ?></label>
                        <?php } ?>
                    </div>
                    <input name="form-add_blueprint-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-add_blueprint-hidden-csrf']); ?>"/>
                    <input class="form__button form__button--primary" id="form-add_blueprint-submit" name="form-add_blueprint-submit" type="submit" value="Create your blueprint"/>
                </form>
            </div>
        </div>
        <div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
            <div class="block__element block__element--home">
                <h2 class="block__title">Last public pasted <span class="block__title--emphasis">blueprints</span></h2>
                <div>
                    <a class="block__link block__link--home" href="/last-blueprints/">Last blueprints</a>
                    <a class="block__link block__link--home" href="/search/">Advanced Search</a>
                    <a class="block__link block__link--home" href="/most-discussed-blueprints/">Most discussed</a>
                    <a class="block__link block__link--home" href="/type/material/">Material blueprint</a>
                    <a class="block__link block__link--home-last" href="/tags/">Tags</a>
                </div>
                <hr class="block__hr block__hr--small"/>
            </div>
            <?php
            include Application::getFolder('VIEWS') . 'www/parts/blueprints_list.php';
            ?>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <script src="/site.js"></script>
</body>
