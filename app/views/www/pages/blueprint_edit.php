<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use app\helpers\FormHelper;
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
        <div class="block__container block__container--first block__container--last">
            <div class="block__element edit-blueprint">
                <div class="edit-blueprint__avatar-area">
                    <div class="profile__avatar-container" id="current-thumbnail">
                        <?php if ($data['blueprint']['thumbnail_url'] !== null) { ?>
                        <img alt="blueprint thumbnail" class="profile__avatar-container" id="upload-current-thumbnail" src="<?php echo Security::escAttr($data['blueprint']['thumbnail_url']); ?>"/>
                        <?php } else { ?>
                        <img alt="blueprint thumbnail" class="profile__avatar-container profile__avatar-container--hidden" id="upload-current-thumbnail"/>
                        <div class="profile__avatar-container profile__avatar-container--background" id="upload-fallback-thumbnail">
                            <svg class="profile__avatar-svg">
                                <use href="/sprite/sprite.svg#avatar"></use>
                            </svg>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="edit-profile__cancel">
                        <a class="form__button form__button--small form__button--no_underline edit-profile__cancel-link" href="#popin-upload-thumbnail">Change thumbnail</a>
                        <form id="form-delete_thumbnail" method="post">
                            <?php
                            /** @var FormHelper $formDeleteThumbnail */
                            $formDeleteThumbnail = $data['form-delete_thumbnail'];
                            ?>

                            <?php
                            // @codeCoverageIgnoreStart
                            if ($formDeleteThumbnail->hasErrorMessage()) { ?>
                                <div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert"><?php echo Security::escHTML($formDeleteThumbnail->getErrorMessage()); ?></div>
                            <?php }
                            // @codeCoverageIgnoreEnd?>

                            <?php if ($formDeleteThumbnail->hasSuccessMessage()) { ?>
                                <div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail"><?php echo Security::escHTML($formDeleteThumbnail->getSuccessMessage()); ?></div>
                            <?php } ?>

                            <input name="form-delete_thumbnail-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_thumbnail-hidden-csrf']); ?>"/>
                            <input class="form__button form__button--small form__button--warning form__button--edit_side<?php echo ($data['blueprint']['thumbnail_url'] === null) ? ' form__button--hidden' : ''; ?>" id="form-delete_thumbnail-submit" name="form-delete_thumbnail-submit" type="submit" value="Delete thumbnail"/>
                            <?php unset($formDeleteThumbnail); ?>
                        </form>

                        <a class="block__link block__link--no-margin" href="<?php echo Security::escAttr($data['blueprint_url']); ?>">Back</a>
                    </div>
                </div>

                <div class="edit-blueprint__forms-area">
                    <form action="#form-edit_informations" data-form-speak-error="Form is invalid:" id="form-edit_informations" method="post">
                        <h2 class="block__title block__title--form-first">Blueprint <span class="block__title--emphasis">basic information</span></h2>
                        <hr class="block__hr block__hr--form"/>

                        <?php
                        /** @var FormHelper $formEditBlueprint */
                        $formEditInformations = $data['form-edit_informations'];
                        ?>
                        <?php if ($formEditInformations->hasErrorMessage()) { ?>
                            <div class="block__element">
                                <div class="block__info block__info--error" data-flash-error-for="form-edit_informations" role="alert"><?php echo Security::escHTML($formEditInformations->getErrorMessage()); ?></div>
                            </div>
                        <?php } ?>

                        <?php if ($formEditInformations->hasSuccessMessage()) { ?>
                            <div class="block__element">
                                <div class="block__info block__info--success" data-flash-success-for="form-edit_informations"><?php echo Security::escHTML($formEditInformations->getSuccessMessage()); ?></div>
                            </div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_informations-input-title" id="form-edit_informations-label-title">Title <span class="form__label--info">(required)</span></label>
                            <div class="form__container<?php echo $formEditInformations->getClassError('title', ' form__container--error'); ?>">
                                <input aria-invalid="false" aria-labelledby="form-edit_informations-label-title<?php echo $formEditInformations->getClassError('title', ' form-edit_informations-label-title-error'); ?>" aria-required="true" class="form__input form__input--invisible<?php echo $formEditInformations->getClassError('title', ' form__input--error'); ?>" data-form-error-required="Title is required" data-form-has-container data-form-rules="required" id="form-edit_informations-input-title" name="form-edit_informations-input-title" type="text" value="<?php echo Security::escAttr($formEditInformations->getInputValue('title')); ?>"/>
                                <span class="form__feedback<?php echo $formEditInformations->getClassError('title', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formEditInformations->getInputError('title') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_informations-input-title" id="form-edit_informations-label-title-error"><?php echo Security::escHTML($formEditInformations->getInputError('title')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_informations-textarea-description" id="form-edit_informations-label-description">Description</label>
                            <div class="form__container form__container--textarea">
                                <textarea aria-invalid="false" aria-labelledby="form-edit_informations-label-reason" class="form__input form__input--invisible form__input--textarea" id="form-edit_informations-textarea-description" name="form-edit_informations-textarea-description"><?php echo Security::escHTML($formEditInformations->getInputValue('description')); ?></textarea>
                                <span class="form__feedback"></span>
                            </div>
                        </div>

                        <label class="form__label" for="form-edit_informations-input-tag" id="form-edit_informations-label-tag">Tags</label>
                        <p>Use keys <code class="blueprint__code-copy-manual--code">,</code> or <code class="blueprint__code-copy-manual--code">Enter</code> from keyboard to add a tag.</p>
                        <p>In your tag you can only use certain valid characters as digits <code class="blueprint__code-copy-manual--code">0-9</code>, letters <code class="blueprint__code-copy-manual--code">a-z</code>, space <code class="blueprint__code-copy-manual--code">&nbsp;</code> and symbols <code class="blueprint__code-copy-manual--code">-</code> <code class="blueprint__code-copy-manual--code">_</code> <code class="blueprint__code-copy-manual--code">.</code></p>
                        <p>The maximum number of tags you can attach is 25.</p>
                        <div class="form__element"
                             data-tag
                             data-tag-aria-label="Remove %s from the list"
                             data-tag-form-input-id="form-edit_informations-input-tag"
                             data-tag-form-textarea-id="form-edit_informations-textarea-tags"
                             data-tag-item-class="block__link block__link--delete block__link--tag"
                             data-tag-list-id="form-edit_informations-ul-tags"
                             data-tag-new-id="form-edit_informations-ul-tags-li-add-tag"
                             data-tag-new-keys=",|Enter"
                             data-tag-regex-keys="^[a-zA-Z0-9._ -]{1}$"
                             data-tag-regex-tag="^[a-zA-Z0-9._ -]*$"
                             data-tag-srspeak-add="%s added"
                             data-tag-srspeak-delete="%s deleted">
                            <ul class="tag__items" id="form-edit_informations-ul-tags">
                                <?php foreach ($data['blueprint']['tags'] ?? [] as $tag) { ?>
                                    <li class="tag__item"><span class="sr-only"><?php echo Security::escHTML($tag['name']); ?></span><button aria-label="Remove <?php echo Security::escAttr($tag['name']); ?> from the list" class="block__link block__link--delete block__link--tag"><?php echo Security::escHTML($tag['name']); ?></button></li>
                                <?php } ?>
                                <li class="tag__add" id="form-edit_informations-ul-tags-li-add-tag">
                                    <div class="form__element">
                                        <input aria-labelledby="form-edit_informations-label-tag" class="form__input" id="form-edit_informations-input-tag" placeholder="Add a new tag" type="text">
                                    </div>
                                </li>
                            </ul>
                            <textarea aria-hidden="true" aria-label="List of tags" hidden id="form-edit_informations-textarea-tags" name="form-edit_informations-textarea-tags"><?php echo Security::escHTML($data['blueprint']['tags_textarea']); ?></textarea>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_informations-input-video" id="form-edit_informations-label-video">Video</label>
                            <div class="form__container<?php echo $formEditInformations->getClassError('video', ' form__container--error'); ?>">
                                <input aria-invalid="false" aria-describedby="form-edit_informations-span-video_help" aria-labelledby="form-edit_informations-label-video<?php echo $formEditInformations->getClassError('video', ' form-edit_informations-label-video-error'); ?>" class="form__input form__input--invisible<?php echo $formEditInformations->getClassError('video', ' form__input--error'); ?>" data-form-error-aria_invalid="Cannot detect video to embed" data-form-has-container data-form-rules="aria_invalid" id="form-edit_informations-input-video" name="form-edit_informations-input-video" type="text" value="<?php echo Security::escAttr($formEditInformations->getInputValue('video')); ?>"/>
                                <span class="form__feedback<?php echo $formEditInformations->getClassError('video', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formEditInformations->getInputError('video') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_informations-input-video" id="form-edit_informations-label-video-error"><?php echo Security::escHTML($formEditInformations->getInputError('video')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_informations-span-video_help">Accepts only <span class="form__help--emphasis">YouTube</span>, <span class="form__help--emphasis">Vimeo</span>, <span class="form__help--emphasis">Dailymotion</span>, <span class="form__help--emphasis">PeerTube</span>, <span class="form__help--emphasis">Bilibili</span> or <span class="form__help--emphasis">Niconico</span> urls</span>
                        </div>

                        <input name="form-edit_informations-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-edit_informations-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-edit_informations-submit" name="form-edit_informations-submit" type="submit" value="Update informations"/>
                        <?php unset($formEditInformations); ?>
                    </form>

                    <form action="#form-edit_properties" data-form-speak-error="Form is invalid:" id="form-edit_properties" method="post">
                        <h2 class="block__title block__title--form-first">Edit Blueprint <span class="block__title--emphasis">properties</span></h2>
                        <hr class="block__hr block__hr--form"/>

                        <?php
                        /** @var FormHelper $formEditProperties */
                        $formEditProperties = $data['form-edit_properties'];
                        ?>
                        <?php if ($formEditProperties->hasErrorMessage()) { ?>
                            <div class="block__element">
                                <div class="block__info block__info--error" data-flash-error-for="form-edit_properties" role="alert"><?php echo Security::escHTML($formEditProperties->getErrorMessage()); ?></div>
                            </div>
                        <?php } ?>

                        <?php if ($formEditProperties->hasSuccessMessage()) { ?>
                            <div class="block__element">
                                <div class="block__info block__info--success" data-flash-success-for="form-edit_properties"><?php echo Security::escHTML($formEditProperties->getSuccessMessage()); ?></div>
                            </div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_properties-select-exposure" id="form-edit_properties-label-exposure">Exposure</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-edit_properties-label-exposure<?php echo $formEditProperties->getClassError('exposure', ' form-edit_properties-label-exposure-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formEditProperties->getClassError('exposure', ' form__input--error'); ?>" id="form-edit_properties-select-exposure" name="form-edit_properties-select-exposure">
                                    <option value="public"<?php echo $formEditProperties->getSelectedValue('exposure', 'public'); ?>>Public</option>
                                    <option value="unlisted"<?php echo $formEditProperties->getSelectedValue('exposure', 'unlisted'); ?>>Unlisted</option>
                                    <option value="private"<?php echo $formEditProperties->getSelectedValue('exposure', 'private'); ?>>Private</option>
                                </select>
                            </div>
                            <?php if ($formEditProperties->getInputError('exposure') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_properties-select-exposure" id="form-edit_properties-label-exposure-error"><?php echo Security::escHTML($formEditProperties->getInputError('exposure')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_properties-select-expiration" id="form-edit_properties-label-expiration">Expiration</label>
                            <div class="form__container form__container--select">
                                <select<?php echo ($data['blueprint']['expiration'] !== null) ? ' aria-describedby="form-edit_properties-span-help"' : ''; ?> aria-invalid="false" aria-labelledby="form-edit_properties-label-expiration<?php echo $formEditProperties->getClassError('expiration', ' form-edit_properties-label-expiration-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formEditProperties->getClassError('expiration', ' form__input--error'); ?>" id="form-edit_properties-select-expiration" name="form-edit_properties-select-expiration">
                                    <?php if ($data['blueprint']['expiration'] !== null) { ?>
                                    <option value="keep"<?php echo $formEditProperties->getSelectedValue('expiration', 'keep'); ?>>Keep expiration time</option>
                                    <option value="1h"<?php echo $formEditProperties->getSelectedValue('expiration', '1h'); ?>>Add 1 hour</option>
                                    <option value="1d"<?php echo $formEditProperties->getSelectedValue('expiration', '1d'); ?>>Add 1 day</option>
                                    <option value="1w"<?php echo $formEditProperties->getSelectedValue('expiration', '1w'); ?>>Add 1 week</option>
                                    <option value="remove"<?php echo $formEditProperties->getSelectedValue('expiration', 'remove'); ?>>Remove expiration time</option>
                                    <?php } else { ?>
                                    <option value="keep"<?php echo $formEditProperties->getSelectedValue('expiration', 'keep'); ?>>No expiration</option>
                                    <option value="1h"<?php echo $formEditProperties->getSelectedValue('expiration', '1h'); ?>>Set expiration to 1 hour</option>
                                    <option value="1d"<?php echo $formEditProperties->getSelectedValue('expiration', '1d'); ?>>Set expiration to 1 day</option>
                                    <option value="1w"<?php echo $formEditProperties->getSelectedValue('expiration', '1w'); ?>>Set expiration to 1 week</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php if ($formEditProperties->getInputError('expiration') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_properties-select-expiration" id="form-edit_properties-label-expiration-error"><?php echo Security::escHTML($formEditProperties->getInputError('expiration')); ?></label>
                            <?php } ?>
                            <?php if ($data['blueprint']['expiration'] !== null) { ?>
                                <span class="form__help" id="form-edit_properties-span-help">Blueprint expired at <span class="form__help--emphasis"><?php echo Security::escHTML($data['blueprint']['expiration']); ?></span></span>
                            <?php } ?>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_properties-select-ue_version" id="form-edit_properties-label-ue_version">UE version</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-edit_properties-label-ue_version<?php echo $formEditProperties->getClassError('ue_version', ' form-edit_properties-label-ue_version-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formEditProperties->getClassError('ue_version', ' form__input--error'); ?>" id="form-edit_properties-select-ue_version" name="form-edit_properties-select-ue_version">
                                    <?php
                                    $selectedUEVersion = $formEditProperties->getInputValue('ue_version');
                                    foreach (Helper::getAllUEVersion() as $ueVersion) { ?>
                                        <option value="<?php echo Security::escAttr($ueVersion); ?>"<?php echo ($selectedUEVersion === $ueVersion) ? ' selected="selected"' : ''; ?>><?php echo Security::escHTML($ueVersion); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php if ($formEditProperties->getInputError('ue_version') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_properties-select-ue_version" id="form-edit_properties-label-ue_version-error"><?php echo Security::escHTML($formEditProperties->getInputError('ue_version')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_properties-select-comment" id="form-edit_properties-label-comment">Comment sections</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-edit_properties-label-comment<?php echo $formEditProperties->getClassError('comment', ' form-edit_properties-label-comment-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formEditProperties->getClassError('comment', ' form__input--error'); ?>" id="form-edit_properties-select-comment" name="form-edit_properties-select-comment">
                                    <option value="open"<?php echo $formEditProperties->getSelectedValue('comment', 'open'); ?>>Open - All members can comment and see other comments</option>
                                    <option value="close"<?php echo $formEditProperties->getSelectedValue('comment', 'close'); ?>>Close - No one can comment but the comments are still visible</option>
                                    <option value="hide"<?php echo $formEditProperties->getSelectedValue('comment', 'hide'); ?>>Hide - No one can comment and the comments are hidden</option>
                                </select>
                            </div>
                            <?php if ($formEditProperties->getInputError('comment') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_properties-select-comment" id="form-edit_properties-label-comment-error"><?php echo Security::escHTML($formEditProperties->getInputError('comment')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-edit_properties-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-edit_properties-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-edit_properties-submit" name="form-edit_properties-submit" type="submit" value="Update properties"/>
                        <?php unset($formEditProperties); ?>
                    </form>

                    <form action="#form-add_version" data-form-speak-error="Form is invalid:" id="form-add_version" method="post">
                        <h2 class="block__title block__title--form-first">Add new version <span class="block__title--emphasis">blueprint</span> — Current version: <?php echo Security::escHTML($data['blueprint']['current_version']); ?></h2>
                        <hr class="block__hr block__hr--form"/>

                        <?php
                        /** @var FormHelper $formAddVersion */
                        $formAddVersion = $data['form-add_version'];
                        ?>

                        <?php if ($formAddVersion->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert"><?php echo Security::escHTML($formAddVersion->getErrorMessage()); ?></div>
                        <?php } ?>

                        <?php if ($formAddVersion->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-add_version"><?php echo Security::escHTML($formAddVersion->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-add_version-textarea-blueprint" id="form-add_version-label-blueprint">New version <span class="form__label--info">(required)</span></label>
                            <div class="form__container form__container--blueprint form__container--textarea<?php echo $formAddVersion->getClassError('blueprint', ' form__container--error'); ?>">
                                <textarea aria-invalid="false" aria-labelledby="form-add_version-label-blueprint<?php echo $formAddVersion->getClassError('blueprint', ' form-add_version-label-blueprint-error'); ?>" aria-required="true" class="form__input form__input--blueprint form__input--invisible form__input--textarea<?php echo $formAddVersion->getClassError('blueprint', ' form__input--error'); ?>" data-form-error-required="Blueprint is required" data-form-has-container data-form-rules="required" id="form-add_version-textarea-blueprint" name="form-add_version-textarea-blueprint"><?php echo Security::escHTML($formAddVersion->getInputValue('blueprint')); ?></textarea>
                                <span class="form__feedback<?php echo $formAddVersion->getClassError('blueprint', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formAddVersion->getInputError('blueprint') !== '') { ?>
                                <label class="form__label form__label--error" for="form-add_version-textarea-blueprint" id="form-add_version-label-blueprint-error"><?php echo Security::escHTML($formAddVersion->getInputError('blueprint')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-add_version-textarea-reason" id="form-add_version-label-reason">Reason <span class="form__label--info">(required)</span></label>
                            <div class="form__container form__container--textarea<?php echo $formAddVersion->getClassError('reason', ' form__container--error'); ?>">
                                <textarea aria-invalid="false" aria-labelledby="form-add_version-label-reason<?php echo $formAddVersion->getClassError('reason', ' form-add_version-label-reason-error'); ?>" aria-required="true" class="form__input form__input--invisible form__input--textarea<?php echo $formAddVersion->getClassError('reason', ' form__input--error'); ?>" data-form-error-required="Reason is required" data-form-has-container data-form-rules="required" id="form-add_version-textarea-reason" name="form-add_version-textarea-reason"><?php echo Security::escHTML($formAddVersion->getInputValue('reason')); ?></textarea>
                                <span class="form__feedback<?php echo $formAddVersion->getClassError('reason', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formAddVersion->getInputError('reason') !== '') { ?>
                                <label class="form__label form__label--error" for="form-add_version-textarea-reason" id="form-add_version-label-reason-error"><?php echo Security::escHTML($formAddVersion->getInputError('reason')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-add_version-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-add_version-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-add_version-submit" name="form-add_version-submit" type="submit" value="Add new version"/>
                        <?php unset($formAddVersion); ?>
                    </form>

                    <form action="#form-delete_blueprint" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure?" data-form-confirm-yes="Yes" data-form-speak-error="Form is invalid:" id="form-delete_blueprint" method="post">
                        <h2 class="block__title block__title--form"><span class="block__title--emphasis">Give or delete blueprint</span></h2>
                        <hr class="block__hr block__hr--form"/>
                        <p>Give the blueprint to anonymous user will keep content, properties and exposure. Private exposure will disable option.</p>

                        <?php
                        /** @var FormHelper $formDeleteProfile */
                        $formDeleteBlueprint = $data['form-delete_blueprint'];
                        ?>

                        <?php if ($formDeleteBlueprint->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert"><?php echo Security::escHTML($formDeleteBlueprint->getErrorMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-delete_blueprint-select-ownership" id="form-delete_blueprint-label-ownership">Blueprints ownership</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-delete_blueprint-label-ownership<?php echo $formDeleteBlueprint->getClassError('ownership', ' form-delete_blueprint-label-ownership-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formDeleteBlueprint->getClassError('ownership', ' form__input--error'); ?>" id="form-delete_blueprint-select-ownership" name="form-delete_blueprint-select-ownership">
                                    <option value="give"<?php echo ($data['blueprint']['exposure'] === 'private') ? ' disabled="disabled"' : $formDeleteBlueprint->getSelectedValue('ownership', 'give'); ?>>Give my blueprint to anonymous user</option>
                                    <option value="delete"<?php echo $formDeleteBlueprint->getSelectedValue('ownership', 'delete'); ?>>Delete my blueprint</option>
                                </select>
                            </div>
                            <?php if ($formDeleteBlueprint->getInputError('ownership') !== '') { ?>
                                <label class="form__label form__label--error" for="form-delete_blueprint-select-ownership" id="form-delete_blueprint-label-ownership-error"><?php echo Security::escHTML($formDeleteBlueprint->getInputError('ownership')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-delete_blueprint-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_blueprint-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--warning" id="form-delete_blueprint-submit-delete" name="form-delete_blueprint-submit-delete" type="submit" value="Give or Delete blueprint"/>
                        <?php unset($formDeleteBlueprint); ?>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <div aria-labelledby="popin-confirm-h2" class="popin" id="popin-confirm" role="dialog">
        <div class="popin__mask">
            <div class="popin__container">
                <div class="popin__body">
                    <div class="popin__header">
                        <h2 class="popin__title" id="popin-confirm-h2">Oops</h2>
                    </div>
                    <div class="popin__content popin__content--buttons" id="popin-confirm-div-content">
                        <a class="form__button form__button--no_underline" id="popin-confirm-button-yes" href="#">Close</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div aria-labelledby="popin-upload-thumbnail-h2" class="popin" id="popin-upload-thumbnail" role="dialog">
        <div class="popin__mask">
            <a class="popin__back" href="#" tabindex="-1"></a>
            <div class="popin__container">
                <div class="popin__body">
                    <div class="popin__header">
                        <h2 class="popin__title" id="popin-upload-thumbnail-h2">Change <span class="popin__title--span">your thumbnail</span></h2>
                    </div>
                    <div class="popin__content"
                         data-uploader
                         data-uploader-btn_cancel-id="popin-upload-thumbnail-cancel"
                         data-uploader-btn_save-id="popin-upload-thumbnail-save"
                         data-uploader-callback-save-success="window.blueprintUE.www.callbackSave"
                         data-uploader-callback-zoom-init="window.blueprintUE.www.callbackInitZoom"
                         data-uploader-callback-zoom-update="window.blueprintUE.www.callbackUpdateZoom"
                         data-uploader-canvas-id="popin-upload-thumbnail-canvas"
                         data-uploader-css-canvas_moving="uploader__canvas--moving"
                         data-uploader-div_error-id="popin-upload-thumbnail-error"
                         data-uploader-div_preview-id="popin-upload-thumbnail-preview"
                         data-uploader-div_upload-id="popin-upload-thumbnail-upload"
                         data-uploader-input_file-id="popin-upload-thumbnail-input_file"
                         data-uploader-input_zoom-id="popin-upload-thumbnail-zoom"
                         data-uploader-mask-color="rgba(255,255,255,0.7)"
                         data-uploader-mask-radius="20"
                         data-uploader-mask-size="200"
                         data-uploader-upload-url="/upload/blueprint/<?php echo (int) $data['blueprint']['id']; ?>/thumbnail/"
                         data-uploader-upload-name="thumbnail"
                         data-uploader-upload-params-csrf="<?php echo Security::escAttr($data['data-uploader-upload-params-csrf']); ?>"
                    >
                        <div id="popin-upload-thumbnail-upload">
                            <div class="form__element form__element--center">
                                <label class="form__button form__button--small" for="popin-upload-thumbnail-input_file">Upload photo</label>
                                <input accept="image/*" class="form__input form__input--hidden" id="popin-upload-thumbnail-input_file" type="file">
                            </div>
                        </div>
                        <div id="popin-upload-thumbnail-preview">
                            <canvas class="uploader__canvas" height="310" id="popin-upload-thumbnail-canvas" width="310"></canvas>
                            <div class="uploader__zoom">
                                <div class="uploader__zoom-icon">
                                    <svg height="100%" viewBox="0 0 20 20" width="100%" x="0px" y="0px">
                                        <g>
                                            <path d="M12 10V8H6v2h6z"></path>
                                            <path clip-rule="evenodd" d="M9 16a6.969 6.969 0 004.192-1.394l3.101 3.101 1.414-1.414-3.1-3.1A7 7 0 109 16zm0-2A5 5 0 109 4a5 5 0 000 10z" fill-rule="evenodd"></path>
                                        </g>
                                    </svg>
                                </div>
                                <div class="uploader__zoom-input-container">
                                    <input aria-label="Range input for zooming photo" class="uploader__zoom-input" id="popin-upload-thumbnail-zoom" max="50" min="1" step="1" type="range" value="1">
                                </div>
                                <div class="uploader__zoom-icon">
                                    <svg height="100%" viewBox="0 0 20 20" width="100%" x="0px" y="0px">
                                        <g>
                                            <path d="M8 8V6h2v2h2v2h-2v2H8v-2H6V8h2z"></path>
                                            <path clip-rule="evenodd" d="M9 16a6.969 6.969 0 004.192-1.394l3.101 3.101 1.414-1.414-3.1-3.1A7 7 0 109 16zm0-2A5 5 0 109 4a5 5 0 000 10z" fill-rule="evenodd"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <div class="uploader__buttons">
                                <button class="form__button form__button--small" id="popin-upload-thumbnail-save">Save</button>
                                <button class="form__button form__button--secondary form__button--small" id="popin-upload-thumbnail-cancel">Cancel</button>
                            </div>
                        </div>
                        <div class="uploader__error" id="popin-upload-thumbnail-error"></div>
                    </div>
                </div>

                <a aria-label="Close" class="popin__close" href="#">
                    <svg aria-hidden="true" class="popin__close-svg">
                        <use href="/sprite/sprite.svg#icon-close"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <script src="/site.js"></script>
</body>