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
        <div class="block__container block__container--first block__container--black block__container--no-padding">
            <div class="block__element--iframe" id="blueprint-render-playground"></div>
        </div>
        <div class="block__container">
            <?php
            /** @var FormHelper $formDeleteBlueprint */
            $formDeleteBlueprint = $data['form-delete_blueprint'];
            ?>
            <?php if ($formDeleteBlueprint->hasErrorMessage()) { ?>
            <div class="block__element">
                <div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert"><?php echo Security::escHTML($formDeleteBlueprint->getErrorMessage()); ?></div>
            </div>
            <?php } unset($formDeleteBlueprint); ?>

            <?php
            /** @var FormHelper $formClaimBlueprint */
            $formClaimBlueprint = $data['form-claim_blueprint'];
            ?>
            <?php if ($formClaimBlueprint->hasErrorMessage()) { ?>
            <div class="block__element">
                <div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert"><?php echo Security::escHTML($formClaimBlueprint->getErrorMessage()); ?></div>
            </div>
            <?php } ?>

            <?php if ($formClaimBlueprint->hasSuccessMessage()) { ?>
            <div class="block__element">
                <div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint"><?php echo Security::escHTML($formClaimBlueprint->getSuccessMessage()); ?></div>
            </div>
            <?php } unset($formClaimBlueprint); ?>

            <?php
            /** @var FormHelper $formDeleteVersionBlueprint */
            $formDeleteVersionBlueprint = $data['form-delete_version_blueprint'];
            ?>
            <?php if ($formDeleteVersionBlueprint->hasErrorMessage()) { ?>
            <div class="block__element">
                <div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert"><?php echo Security::escHTML($formDeleteVersionBlueprint->getErrorMessage()); ?></div>
            </div>
            <?php } ?>

            <?php if ($formDeleteVersionBlueprint->hasSuccessMessage()) { ?>
            <div class="block__element">
                <div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint"><?php echo Security::escHTML($formDeleteVersionBlueprint->getSuccessMessage()); ?></div>
            </div>
            <?php } unset($formDeleteVersionBlueprint); ?>
            <div class="block__element">
                <div class="blueprint__infos">
                    <div class="blueprint__summary">
                        <?php if ($data['blueprint']['thumbnail_url'] !== null) { ?>
                        <img alt="thumbnail blueprint" class="blueprint__avatar-container" src="<?php echo Security::escAttr($data['blueprint']['thumbnail_url']); ?>"/>
                        <?php } ?>
                        <div>
                            <span class="blueprint__type"><?php echo ($data['blueprint']['type'] === 'behavior_tree') ? 'behavior tree' : Security::escHTML($data['blueprint']['type']); ?></span>
                            <h1 class="blueprint__title"><?php echo Security::escHTML($data['blueprint']['title']); ?></h1>
                            <?php if ($data['can_edit'] === true) { ?>
                            <a class="block__link block__link--edit-blueprint" href="<?php echo Security::escAttr($data['blueprint']['edit_url']); ?>">Edit blueprint</a>
                            <?php } ?>
                            <?php if ($data['can_delete'] === true) { ?>
                            <form action="<?php echo Security::escAttr($data['page_url'] . '#delete'); ?>" class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to delete this blueprint?" data-form-confirm-yes="Yes" method="post">
                                <input name="form-delete_blueprint-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_blueprint-hidden-csrf']); ?>"/>
                                <button class="form__button form__button--warning" type="submit">Delete blueprint</button>
                            </form>
                            <?php } ?>
                            <?php if ($data['can_claim'] === true) { ?>
                            <form action="<?php echo Security::escAttr($data['page_url'] . '#claim'); ?>" class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to claim this blueprint? (it will be added to your profile)" data-form-confirm-yes="Yes" method="post">
                                <input name="form-claim_blueprint-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-claim_blueprint-hidden-csrf']); ?>"/>
                                <button class="form__button" type="submit">Claim blueprint</button>
                            </form>
                            <?php } ?>
                        </div>
                    </div>
                    <ul class="blueprint__properties">
                        <li class="blueprint__property">Exposure: <span class="blueprint__property--emphasis"><?php echo Security::escHTML($data['blueprint']['exposure']); ?></span></li>
                        <?php if ($data['blueprint']['expiration'] !== null) { ?>
                        <li class="blueprint__property">Expiration: <span class="blueprint__property--emphasis"><?php echo Security::escHTML($data['blueprint']['expiration']); ?></span></li>
                        <?php } ?>
                        <li class="blueprint__property">UE Version: <span class="blueprint__property--emphasis"><?php echo Security::escHTML($data['blueprint']['ue_version']); ?></span></li>
                    </ul>
                </div>
                <hr class="block__hr"/>
                <div class="blueprint__author-infos">
                    <?php if ($data['blueprint']['author']['avatar_url'] !== null) { ?>
                    <img alt="avatar author" class="blueprint__avatar-container" src="<?php echo Security::escAttr($data['blueprint']['author']['avatar_url']); ?>"/>
                    <?php } else { ?>
                    <div class="blueprint__avatar-container blueprint__avatar-container--background">
                        <svg class="blueprint__avatar-svg">
                            <use href="/sprite/sprite.svg#avatar"></use>
                        </svg>
                    </div>
                    <?php } ?>
                    <div>
                        <h2 class="blueprint__author"><a class="blueprint__profile" href="<?php echo Security::escAttr($data['blueprint']['author']['profile_url']); ?>"><?php echo Security::escHTML($data['blueprint']['author']['username']); ?></a></h2>
                        <p class="blueprint__time"><?php echo Security::escHTML($data['blueprint']['published_at']); ?></p>
                    </div>
                </div>
                <?php if ($data['blueprint']['tags'] !== null && \count($data['blueprint']['tags']) > 0) { ?>
                <ul class="tag__items">
                    <?php foreach ($data['blueprint']['tags'] as $tag) { ?>
                    <li class="tag__item"><a class="block__link block__link--no-margin" href="<?php echo Security::escAttr($tag['url']); ?>"><?php echo Security::escHTML($tag['name']); ?></a></li>
                    <?php } ?>
                </ul>
                <?php } ?>
                <?php if (!empty($data['blueprint']['description'])) { ?>
                <div class="blueprint__description block__markdown">
                    <?php echo $data['markdown']->text($data['blueprint']['description']); ?>
                </div>
                <?php } ?>
                <?php if ($data['blueprint']['video'] !== null) { ?>
                <div class="blueprint__video"
                     data-video-iframe
                     data-video-iframe-button-id="blueprint-video-button-provider"
                     data-video-iframe-class="blueprint__iframe"
                     data-video-iframe-loading-class="blueprint__video--loading"
                     data-video-iframe-url="<?php echo Security::escAttr($data['blueprint']['video']); ?>">
                    <p>
                        This video is provided by <?php echo Security::escHTML($data['blueprint']['video_provider']); ?> using cookies.<br/>
                        <a class="blueprint__video--policy" href="<?php echo Security::escAttr($data['blueprint']['video_privacy_url']); ?>" rel="noopener noreferrer nofollow" target="_blank">See their privacy page.</a>
                    </p>
                    <button class="form__button form__button--primary" id="blueprint-video-button-provider">Accept <?php echo Security::escHTML($data['blueprint']['video_provider']); ?> cookies</button>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="block__container block__container--white-grey block__container--shadow-top<?php echo ($data['blueprint']['comments_hidden'] === true) ? ' block__container--last' : ''; ?>">
            <div class="block__element">
                <div class="form__element">
                    <label class="form__label" for="code_to_copy">Code to copy</label>
                    <div class="blueprint__code-copy-container">
                        <textarea class="form__input form__input--textarea blueprint__code-copy-textarea blueprint__code-copy-textarea--hidden" id="code_to_copy"><?php echo Security::escHTML($data['blueprint']['content']); ?></textarea>
                        <button class="blueprint__code-copy-button" id="fast-copy-clipboard">
                            <svg viewBox="0 0 14 16" width="14" height="16" aria-hidden="true">
                                <path fill-rule="evenodd" d="M2 13h4v1H2v-1zm5-6H2v1h5V7zm2 3V8l-3 3 3 3v-2h5v-2H9zM4.5 9H2v1h2.5V9zM2 12h2.5v-1H2v1zm9 1h1v2c-.02.28-.11.52-.3.7-.19.18-.42.28-.7.3H1c-.55 0-1-.45-1-1V4c0-.55.45-1 1-1h3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2h3c.55 0 1 .45 1 1v5h-1V6H1v9h10v-2zM2 5h8c0-.55-.45-1-1-1H8c-.55 0-1-.45-1-1s-.45-1-1-1-1 .45-1 1-.45 1-1 1H3c-.55 0-1 .45-1 1z"></path>
                            </svg>
                        </button>
                    </div>
                    <span class="blueprint__code-copy-manual">Click the button above, it will automatically copy blueprint in your clipboard. Then in Unreal Engine blueprint editor, paste it with <code class="blueprint__code-copy-manual--code">ctrl + v</code></span>
                </div>
                <div class="form__element">
                    <label class="form__label" for="code_to_embed">Code to Embed</label>
                    <input class="form__input" id="code_to_embed" type="text" value="<?php echo Security::escAttr($data['blueprint']['embed_url']); ?>"/>
                </div>

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
                        <?php foreach ($versions as $version) { ?>
                    <li class="blueprint__version<?php echo ($version['last'] === true) ? ' blueprint__version--last' : ''; ?><?php echo ($version['current'] === true) ? ' blueprint__version--current' : ''; ?>">
                        <div class="blueprint__version-left">
                            <p><?php echo \nl2br(Security::escHTML($version['reason'])); ?></p>
                        </div>
                            <?php if ($version['current'] !== true) { ?>
                        <div>
                                <?php if ($data['can_edit'] === true) { ?>
                            <form action="<?php echo Security::escAttr($data['page_url'] . '#delete_version_' . $version['version']); ?>" class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to delete this version?" data-form-confirm-yes="Yes" method="post">
                                <input name="form-delete_version_blueprint-hidden-version" type="hidden" value="<?php echo Security::escAttr($version['version']); ?>"/>
                                <input name="form-delete_version_blueprint-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_version_blueprint-hidden-csrf']); ?>"/>
                                <button class="form__button form__button--warning form__button--block_link" type="submit">Delete</button>
                            </form>
                                <?php } ?>
                            <a class="block__link block__link--no-margin" href="<?php echo Security::escAttr($version['url']); ?>">See</a>
                                <?php if ($version['diff_url'] !== '') { ?>
                            <a class="block__link block__link--no-margin" href="<?php echo Security::escAttr($version['diff_url']); ?>">Diff</a>
                                <?php } ?>
                        </div>
                            <?php } elseif ($version['diff_url'] !== '') { ?>
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
        <?php if ($data['blueprint']['comments_hidden'] === false) { ?>
        <div class="block__container block__container--shadow-bottom block__container--last">
            <div class="block__element">
                <h2 class="block__title" id="comments"><?php echo Security::escHTML($data['blueprint']['comments_count']); ?> <span class="block__title--emphasis">comment<?php echo ($data['blueprint']['comments_count'] > 1) ? 's' : ''; ?></span></h2>
                <?php if ($data['can_comment'] === true) { ?>
                    <?php
                    /** @var FormHelper $formDeleteComment */
                    $formDeleteComment = $data['form-delete_comment'];
                    ?>

                    <?php if ($formDeleteComment->hasSuccessMessage()) { ?>
                        <div class="block__info block__info--success" data-flash-success-for="form-delete_comment"><?php echo Security::escHTML($formDeleteComment->getSuccessMessage()); ?></div>
                    <?php } ?>

                    <?php if ($formDeleteComment->hasErrorMessage()) { ?>
                        <div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert"><?php echo Security::escHTML($formDeleteComment->getErrorMessage()); ?></div>
                    <?php } unset($formDeleteComment); ?>

                    <?php
                    if ($data['form-edit_comment-comment_id'] === null) {
                        /** @var FormHelper $formEditComment */
                        $formEditComment = $data['form-edit_comment']; ?>

                        <?php if ($formEditComment->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert"><?php echo Security::escHTML($formEditComment->getErrorMessage()); ?></div>
                        <?php }
                        unset($formEditComment);
                    } ?>

                    <?php
                    /** @var FormHelper $formAddComment */
                    $formAddComment = $data['form-add_comment'];
                    ?>

                    <?php if ($formAddComment->hasErrorMessage()) { ?>
                    <div class="block__info block__info--error" data-flash-error-for="form-add_comment" role="alert"><?php echo Security::escHTML($formAddComment->getErrorMessage()); ?></div>
                    <?php } ?>

                    <?php if ($data['blueprint']['comments_closed'] === false) { ?>
                <form action="<?php echo Security::escAttr($data['page_url'] . '#comments'); ?>" data-form-speak-error="Form is invalid:" id="form-add_comment" method="post">
                    <div class="form__element">
                        <label class="form__label" for="form-add_comment-textarea-comment" id="form-add_comment-label-comment">Add your comment <span class="form__label--info">(required)</span></label>
                        <div class="form__container form__container--textarea<?php echo $formAddComment->getClassError('comment', ' form__container--error'); ?>">
                            <textarea aria-invalid="false" aria-labelledby="form-add_comment-label-comment<?php echo $formAddComment->getClassError('comment', ' form-add_comment-label-comment-error'); ?>" aria-required="true" class="form__input form__input--textarea form__input--invisible<?php echo $formAddComment->getClassError('comment', ' form__input--error'); ?>" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-add_comment-textarea-comment" name="form-add_comment-textarea-comment" placeholder="Markdown supported"><?php echo Security::escHTML($formAddComment->getInputValue('comment')); ?></textarea>
                            <span class="form__feedback<?php echo $formAddComment->getClassError('comment', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formAddComment->getInputError('comment') !== '') { ?>
                        <label class="form__label form__label--error" for="form-add_comment-textarea-comment" id="form-add_comment-label-comment-error"><?php echo Security::escHTML($formAddComment->getInputError('comment')); ?></label>
                        <?php } ?>
                    </div>
                    <input name="form-add_comment-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-add_comment-hidden-csrf']); ?>"/>
                    <input class="form__button form__button--primary" id="form-add_comment-submit" name="form-add_comment-submit" type="submit" value="Add comment"/>
                </form>
                    <?php } ?>
                    <?php unset($formAddComment);
                } ?>
            </div>
            <?php if ($data['blueprint']['comments'] !== null) {
                    $i = 0;
                    $max = \count($data['blueprint']['comments']); ?>
            <div class="block__element">
                <ul class="comment__list">
                    <?php foreach ($data['blueprint']['comments'] as $comment) {
                        $liAttrs = 'class="comment__item' . (($i === $max) ? ' comment__item--last' : '') . '" id="comment-' . ((int) $comment['id']) . '"';
                        if ($comment['can_edit'] === true) {
                            $liAttrs = 'class="comment__item' . (($i === $max) ? ' comment__item--last' : '') . '" ' .
                                'data-edit_comment ' .
                                'data-edit_comment-btn_cancel_id="edit_comment-btn-cancel_comment-' . ((int) $comment['id']) . '" ' .
                                'data-edit_comment-btn_id="edit_comment-btn-edit-comment-' . ((int) $comment['id']) . '" ' .
                                'data-edit_comment-content_id="edit_comment-content-' . ((int) $comment['id']) . '" ' .
                                'data-edit_comment-edit_content_id="edit_comment-edit_content-' . ((int) $comment['id']) . '" ' .
                                'id="comment-' . ((int) $comment['id']) . '"';
                        }
                        $contentClass = '';
                        $editContentClass = ' comment__hide';
                        ++$i; ?>
                    <li <?php echo $liAttrs; ?>>
                        <?php if ($comment['id'] === $data['form-add_comment-comment_id'] && $data['form-add_comment']->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-add_comment"><?php echo Security::escHTML($data['form-add_comment']->getSuccessMessage()); ?></div>
                        <?php } ?>
                        <?php if ($comment['id'] === $data['form-edit_comment-comment_id'] && $data['form-edit_comment']->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-edit_comment"><?php echo Security::escHTML($data['form-edit_comment']->getSuccessMessage()); ?></div>
                        <?php } ?>
                        <?php if ($comment['id'] === $data['form-edit_comment-comment_id'] && $data['form-edit_comment']->hasErrorMessage()) {
                            $contentClass = ' comment__hide';
                            $editContentClass = ''; ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert"><?php echo Security::escHTML($data['form-edit_comment']->getErrorMessage()); ?></div>
                            <?php
                        } ?>
                        <div class="blueprint__author-infos">
                            <?php if ($comment['author']['avatar_url'] !== null) { // @codeCoverageIgnoreStart?>
                                <img alt="avatar author" class="blueprint__avatar-container" src="<?php echo Security::escAttr($comment['author']['avatar_url']); ?>" />
                                <?php // @codeCoverageIgnoreEnd
                            } else { ?>
                                <div class="blueprint__avatar-container blueprint__avatar-container--background">
                                    <svg class="blueprint__avatar-svg">
                                        <use href="/sprite/sprite.svg#avatar"></use>
                                    </svg>
                                </div>
                            <?php } ?>
                            <?php if ($comment['id_author'] === null) { ?>
                            <div class="comment__author">
                                <h2 class="blueprint__author"><?php echo Security::escHTML($comment['name_fallback']); ?></h2>
                                <p class="blueprint__time"><?php echo Security::escHTML($comment['created_at']); ?></p>
                            </div>
                            <?php } else { ?>
                            <div class="comment__author">
                                <h2 class="blueprint__author"><a class="blueprint__profile" href="<?php echo Security::escAttr($comment['author']['profile_url']); ?>"><?php echo Security::escHTML($comment['author']['username']); ?></a></h2>
                                <p class="blueprint__time"><?php echo Security::escHTML($comment['created_at']); ?></p>
                            </div>
                            <?php } ?>
                            <?php if ($comment['can_edit']) { ?>
                            <div class="comment__actions">
                                <form class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to delete this comment?" data-form-confirm-yes="Yes" method="post">
                                    <input name="form-delete_comment-hidden-id" type="hidden" value="<?php echo Security::escAttr($comment['id']); ?>"/>
                                    <input name="form-delete_comment-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_comment-hidden-csrf']); ?>"/>
                                    <button class="form__button form__button--warning form__button--block_link" type="submit">Delete</button>
                                </form>
                                <a class="block__link block__link--no-margin" id="edit_comment-btn-edit-comment-<?php echo Security::escAttr($comment['id']); ?>" href="#">Edit</a>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="comment__content<?php echo $contentClass; ?>"<?php echo ($comment['can_edit']) ? ' id="edit_comment-content-' . (int) $comment['id'] . '"' : ''; ?>><?php echo $data['markdown']->text($comment['content']); ?></div>
                        <?php if ($comment['can_edit']) {
                                $divAttrs = 'class="comment__content' . $editContentClass . '" id="edit_comment-edit_content-' . ((int) $comment['id']) . '"';
                                $formEditComment = new FormHelper();
                                $formEditComment->setInputValue('comment', $comment['content']);
                                if ($comment['id'] === $data['form-edit_comment-comment_id']) { // phpcs:ignore
                                    $formEditComment = $data['form-edit_comment']; // phpcs:ignore
                                } // phpcs:ignore?>
                        <div <?php echo $divAttrs; ?>>
                            <form data-form-speak-error="Form is invalid:" id="form-edit_comment-<?php echo Security::escAttr($comment['id']); ?>" method="post">
                                <div class="form__element">
                                    <label class="form__label" for="form-edit_comment-textarea-comment-<?php echo Security::escAttr($comment['id']); ?>" id="form-edit_comment-label-comment-<?php echo Security::escAttr($comment['id']); ?>">Edit comment</label>
                                    <div class="form__container form__container--textarea<?php echo $formEditComment->getClassError('comment', ' form__container--error'); ?>">
                                        <textarea aria-invalid="false" aria-labelledby="form-edit_comment-label-comment<?php echo $formEditComment->getClassError('comment', ' form-edit_comment-label-comment-error'); ?>" aria-required="true" class="form__input form__input--textarea form__input--invisible<?php echo $formEditComment->getClassError('comment', ' form__input--error'); ?>" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-edit_comment-textarea-comment-<?php echo Security::escAttr($comment['id']); ?>" name="form-edit_comment-textarea-comment"><?php echo Security::escHTML($formEditComment->getInputValue('comment')); ?></textarea>
                                        <span class="form__feedback<?php echo $formEditComment->getClassError('comment', ' form__feedback--error'); ?>"></span>
                                    </div>
                                    <?php if ($formEditComment->getInputError('comment') !== '') { ?>
                                        <label class="form__label form__label--error" for="form-edit_comment-textarea-comment" id="form-edit_comment-label-comment-error"><?php echo Security::escHTML($formEditComment->getInputError('comment')); ?></label>
                                    <?php } ?>
                                </div>
                                <input name="form-edit_comment-hidden-id" type="hidden" value="<?php echo Security::escAttr($comment['id']); ?>"/>
                                <input name="form-edit_comment-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-edit_comment-hidden-csrf']); ?>"/>
                                <input class="form__button form__button--small" id="form-edit_comment-submit-<?php echo Security::escAttr($comment['id']); ?>" name="form-edit_comment-submit" type="submit" value="Update comment"/>
                                <input class="form__button form__button--small form__button--secondary" id="edit_comment-btn-cancel_comment-<?php echo Security::escAttr($comment['id']); ?>" type="submit" value="Cancel"/>
                            </form>
                        </div>
                            <?php
                            } // phpcs:ignore?>
                    </li>
                        <?php
                    } // phpcs:ignore?>
                </ul>
            </div>
                <?php
                } // phpcs:ignore?>
        </div>
        <?php } ?>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php if ($data['can_claim'] === true || $data['can_delete'] === true || $data['can_edit'] === true || $data['has_own_comments'] === true) { ?>
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
    <?php } ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <link href="/bue-render/render.css" rel="stylesheet">
    <script src="/site.js"></script>
    <script src="/bue-render/render.js"></script>
    <script>
        new window.blueprintUE.render.Main(
            document.getElementById('code_to_copy').value,
            document.getElementById('blueprint-render-playground'),
            {height:"643px"}
        ).start();
    </script>
</body>