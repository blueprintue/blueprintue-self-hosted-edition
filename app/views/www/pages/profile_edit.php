<?php

/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use app\helpers\FormHelper;
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
        <div class="block__container block__container--first block__container--last">
            <div class="block__element edit-profile">
                <div class="edit-profile__avatar-area">
                    <div class="profile__avatar-container" id="current-avatar">
                        <?php if ($data['avatar'] !== null) { ?>
                        <img alt="avatar author" class="profile__avatar-container" id="upload-current-avatar" src="<?php echo Security::escAttr($data['avatar']); ?>"/>
                        <?php } else { ?>
                        <img alt="avatar author" class="profile__avatar-container profile__avatar-container--hidden" id="upload-current-avatar"/>
                        <div class="profile__avatar-container profile__avatar-container--background" id="upload-fallback-avatar">
                            <svg class="profile__avatar-svg">
                                <use href="/sprite/sprite.svg#avatar"></use>
                            </svg>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="edit-profile__cancel">
                        <a class="form__button form__button--small form__button--no_underline edit-profile__cancel-link" href="#popin-upload-avatar">Change avatar</a>
                        <form id="form-delete_avatar" method="post">
                            <?php
                            /** @var FormHelper $formDeleteAvatar */
                            $formDeleteAvatar = $data['form-delete_avatar'];
                            ?>

                            <?php
                            // @codeCoverageIgnoreStart
                            if ($formDeleteAvatar->hasErrorMessage()) { ?>
                                <div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert"><?php echo Security::escHTML($formDeleteAvatar->getErrorMessage()); ?></div>
                            <?php }
                            // @codeCoverageIgnoreEnd?>

                            <?php if ($formDeleteAvatar->hasSuccessMessage()) { ?>
                                <div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar"><?php echo Security::escHTML($formDeleteAvatar->getSuccessMessage()); ?></div>
                            <?php } ?>

                            <input name="form-delete_avatar-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_avatar-hidden-csrf']); ?>"/>
                            <input class="form__button form__button--small form__button--warning form__button--edit_side<?php echo ($data['avatar'] === null) ? ' form__button--hidden' : ''; ?>" id="form-delete_avatar-submit" name="form-delete_avatar-submit" type="submit" value="Delete avatar"/>
                            <?php unset($formDeleteAvatar); ?>
                        </form>

                        <a class="block__link block__link--no-margin" href="/profile/<?php echo Security::escAttr(Session::get('slug')); ?>/">Back</a>
                    </div>
                </div>
                <div class="edit-profile__forms-area">
                    <form action="#form-edit_basic_infos" data-form-speak-error="Form is invalid:" id="form-edit_basic_infos" method="post">
                        <h2 class="block__title block__title--form-first"><?php echo Security::escHTML($data['slug']); ?> <span class="block__title--emphasis">basic information</span></h2>
                        <hr class="block__hr block__hr--form"/>

                        <?php
                        /** @var FormHelper $formEditBasicInfos */
                        $formEditBasicInfos = $data['form-edit_basic_infos'];
                        ?>

                        <?php if ($formEditBasicInfos->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert"><?php echo Security::escHTML($formEditBasicInfos->getErrorMessage()); ?></div>
                        <?php } ?>

                        <?php if ($formEditBasicInfos->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos"><?php echo Security::escHTML($formEditBasicInfos->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_basic_infos-textarea-bio" id="form-edit_basic_infos-label-bio">Bio</label>
                            <textarea aria-invalid="false" aria-labelledby="form-edit_basic_infos-label-bio" class="form__input form__input--textarea" id="form-edit_basic_infos-textarea-bio" name="form-edit_basic_infos-textarea-bio"><?php echo Security::escHTML($formEditBasicInfos->getInputValue('bio')); ?></textarea>
                        </div>
                        <div class="form__element">
                            <label class="form__label" for="form-edit_basic_infos-input-website" id="form-edit_basic_infos-label-website">Website</label>
                            <input aria-invalid="false" aria-labelledby="form-edit_basic_infos-label-website" class="form__input" id="form-edit_basic_infos-input-website" name="form-edit_basic_infos-input-website" type="text" value="<?php echo Security::escAttr($formEditBasicInfos->getInputValue('website')); ?>"/>
                        </div>

                        <input name="form-edit_basic_infos-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-edit_basic_infos-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-edit_basic_infos-submit" name="form-edit_basic_infos-submit" type="submit" value="Update basic informations"/>
                        <?php unset($formEditBasicInfos); ?>
                    </form>

                    <form action="#form-edit_socials" data-form-speak-error="Form is invalid:" id="form-edit_socials" method="post">
                        <h2 class="block__title block__title--form">Your <span class="block__title--emphasis">social profiles</span></h2>
                        <hr class="block__hr block__hr--form"/>

                        <?php
                        /** @var FormHelper $formEditSocials */
                        $formEditSocials = $data['form-edit_socials'];
                        ?>

                        <?php if ($formEditSocials->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-edit_socials" role="alert"><?php echo Security::escHTML($formEditSocials->getErrorMessage()); ?></div>
                        <?php } ?>

                        <?php if ($formEditSocials->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-edit_socials"><?php echo Security::escHTML($formEditSocials->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_socials-input-facebook" id="form-edit_socials-label-facebook">Facebook</label>
                            <div class="form__container<?php echo $formEditSocials->getClassError('facebook', ' form__container--error'); ?>">
                                <input aria-describedby="form-edit_socials-span-facebook_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-facebook<?php echo $formEditSocials->getClassError('facebook', ' form-edit_socials-label-facebook-error'); ?>" class="form__input form__input--invisible<?php echo $formEditSocials->getClassError('facebook', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-facebook" name="form-edit_socials-input-facebook" type="text" value="<?php echo Security::escAttr($formEditSocials->getInputValue('facebook')); ?>"/>
                                <span class="form__feedback<?php echo $formEditSocials->getClassError('facebook', ' form__feedback--error'); ?>"></span>
                                <svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--facebook">
                                    <use href="/sprite/sprite.svg#icon-facebook"></use>
                                </svg>
                            </div>
                            <?php if ($formEditSocials->getInputError('facebook') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_socials-input-facebook" id="form-edit_socials-label-facebook-error"><?php echo Security::escHTML($formEditSocials->getInputError('facebook')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_socials-span-facebook_help">https://www.facebook.com/<span class="form__help--emphasis"><?php echo Security::escHTML(($formEditSocials->getInputValue('facebook') !== '' && $formEditSocials->getInputError('facebook') === '') ? $formEditSocials->getInputValue('facebook') : 'username'); ?></span></span>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_socials-input-twitter" id="form-edit_socials-label-twitter">Twitter</label>
                            <div class="form__container<?php echo $formEditSocials->getClassError('twitter', ' form__container--error'); ?>">
                                <input aria-describedby="form-edit_socials-span-twitter_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-twitter<?php echo $formEditSocials->getClassError('twitter', ' form-edit_socials-label-twitter-error'); ?>" class="form__input form__input--invisible<?php echo $formEditSocials->getClassError('twitter', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-twitter" name="form-edit_socials-input-twitter" type="text" value="<?php echo Security::escAttr($formEditSocials->getInputValue('twitter')); ?>"/>
                                <span class="form__feedback<?php echo $formEditSocials->getClassError('twitter', ' form__feedback--error'); ?>"></span>
                                <svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--twitter">
                                    <use href="/sprite/sprite.svg#icon-twitter"></use>
                                </svg>
                            </div>
                            <?php if ($formEditSocials->getInputError('twitter') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_socials-input-twitter" id="form-edit_socials-label-twitter-error"><?php echo Security::escHTML($formEditSocials->getInputError('twitter')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_socials-span-twitter_help">https://twitter.com/<span class="form__help--emphasis"><?php echo Security::escHTML(($formEditSocials->getInputValue('twitter') !== '' && $formEditSocials->getInputError('twitter') === '') ? $formEditSocials->getInputValue('twitter') : 'username'); ?></span></span>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_socials-input-github" id="form-edit_socials-label-github">GitHub</label>
                            <div class="form__container<?php echo $formEditSocials->getClassError('github', ' form__container--error'); ?>">
                                <input aria-describedby="form-edit_socials-span-github_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-github<?php echo $formEditSocials->getClassError('github', ' form-edit_socials-label-github-error'); ?>" class="form__input form__input--invisible<?php echo $formEditSocials->getClassError('github', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-github" name="form-edit_socials-input-github" type="text" value="<?php echo Security::escAttr($formEditSocials->getInputValue('github')); ?>"/>
                                <span class="form__feedback<?php echo $formEditSocials->getClassError('github', ' form__feedback--error'); ?>"></span>
                                <svg aria-hidden="true" class="edit-profile__social-icon">
                                    <use href="/sprite/sprite.svg#icon-github"></use>
                                </svg>
                            </div>
                            <?php if ($formEditSocials->getInputError('github') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_socials-input-github" id="form-edit_socials-label-github-error"><?php echo Security::escHTML($formEditSocials->getInputError('github')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_socials-span-github_help">https://github.com/<span class="form__help--emphasis"><?php echo Security::escHTML(($formEditSocials->getInputValue('github') !== '' && $formEditSocials->getInputError('github') === '') ? $formEditSocials->getInputValue('github') : 'username'); ?></span></span>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_socials-input-youtube" id="form-edit_socials-label-youtube">Youtube</label>
                            <div class="form__container<?php echo $formEditSocials->getClassError('youtube', ' form__container--error'); ?>">
                                <input aria-describedby="form-edit_socials-span-youtube_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-youtube<?php echo $formEditSocials->getClassError('youtube', ' form-edit_socials-label-youtube-error'); ?>" class="form__input form__input--invisible<?php echo $formEditSocials->getClassError('youtube', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="channel_id" id="form-edit_socials-input-youtube" name="form-edit_socials-input-youtube" type="text" value="<?php echo Security::escAttr($formEditSocials->getInputValue('youtube')); ?>"/>
                                <span class="form__feedback<?php echo $formEditSocials->getClassError('youtube', ' form__feedback--error'); ?>"></span>
                                <svg aria-hidden="true" class="edit-profile__social-icon">
                                    <use href="/sprite/sprite.svg#icon-youtube"></use>
                                </svg>
                            </div>
                            <?php if ($formEditSocials->getInputError('youtube') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_socials-input-youtube" id="form-edit_socials-label-youtube-error"><?php echo Security::escHTML($formEditSocials->getInputError('youtube')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_socials-span-youtube_help">https://www.youtube.com/channel/<span class="form__help--emphasis"><?php echo Security::escHTML(($formEditSocials->getInputValue('youtube') !== '' && $formEditSocials->getInputError('youtube') === '') ? $formEditSocials->getInputValue('youtube') : 'channel_id'); ?></span><br />
                                Find your channel id: <a href="https://www.youtube.com/account_advanced" rel="noopener noreferrer nofollow" target="_blank">https://www.youtube.com/account_advanced</a></span>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_socials-input-twitch" id="form-edit_socials-label-twitch">Twitch</label>
                            <div class="form__container<?php echo $formEditSocials->getClassError('twitch', ' form__container--error'); ?>">
                                <input aria-describedby="form-edit_socials-span-twitch_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-twitch<?php echo $formEditSocials->getClassError('twitch', ' form-edit_socials-label-twitch-error'); ?>" class="form__input form__input--invisible<?php echo $formEditSocials->getClassError('twitch', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-twitch" name="form-edit_socials-input-twitch" type="text" value="<?php echo Security::escAttr($formEditSocials->getInputValue('twitch')); ?>"/>
                                <span class="form__feedback<?php echo $formEditSocials->getClassError('twitch', ' form__feedback--error'); ?>"></span>
                                <svg aria-hidden="true" class="edit-profile__social-icon profile__network-svg--twitch">
                                    <use href="/sprite/sprite.svg#icon-twitch"></use>
                                </svg>
                            </div>
                            <?php if ($formEditSocials->getInputError('twitch') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_socials-input-twitch" id="form-edit_socials-label-twitch-error"><?php echo Security::escHTML($formEditSocials->getInputError('twitch')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_socials-span-twitch_help">https://www.twitch.tv/<span class="form__help--emphasis"><?php echo Security::escHTML(($formEditSocials->getInputValue('twitch') !== '' && $formEditSocials->getInputError('twitch') === '') ? $formEditSocials->getInputValue('twitch') : 'username'); ?></span></span>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-edit_socials-input-unreal" id="form-edit_socials-label-unreal">Unreal Engine Forum</label>
                            <div class="form__container<?php echo $formEditSocials->getClassError('unreal', ' form__container--error'); ?>">
                                <input aria-describedby="form-edit_socials-span-unreal_help" aria-invalid="false" aria-labelledby="form-edit_socials-label-unreal<?php echo $formEditSocials->getClassError('unreal', ' form-edit_socials-label-unreal-error'); ?>" class="form__input form__input--invisible<?php echo $formEditSocials->getClassError('unreal', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-has-container data-form-rules="regex:^[a-zA-Z0-9._-]*$" data-profile-social data-profile-social-fallback="username" id="form-edit_socials-input-unreal" name="form-edit_socials-input-unreal" type="text" value="<?php echo Security::escAttr($formEditSocials->getInputValue('unreal')); ?>"/>
                                <span class="form__feedback<?php echo $formEditSocials->getClassError('unreal', ' form__feedback--error'); ?>"></span>
                                <svg aria-hidden="true" class="edit-profile__social-icon">
                                    <use href="/sprite/sprite.svg#icon-unreal"></use>
                                </svg>
                            </div>
                            <?php if ($formEditSocials->getInputError('unreal') !== '') { ?>
                                <label class="form__label form__label--error" for="form-edit_socials-input-unreal" id="form-edit_socials-label-unreal-error"><?php echo Security::escHTML($formEditSocials->getInputError('unreal')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-edit_socials-span-unreal_help">https://forums.unrealengine.com/u/<span class="form__help--emphasis"><?php echo Security::escHTML(($formEditSocials->getInputValue('unreal') !== '' && $formEditSocials->getInputError('unreal') === '') ? $formEditSocials->getInputValue('unreal') : 'username'); ?></span></span>
                        </div>

                        <input name="form-edit_socials-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-edit_socials-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-edit_socials-submit" name="form-edit_socials-submit" type="submit" value="Update social profiles"/>
                        <?php unset($formEditSocials); ?>
                    </form>

                    <form action="#form-change_email" data-form-speak-error="Form is invalid:" id="form-change_email" method="post">
                        <h2 class="block__title block__title--form"><?php echo (empty($data['email'])) ? 'Add' : 'Change'; ?> <span class="block__title--emphasis">email</span></h2>
                        <hr class="block__hr block__hr--form"/>
                        <p>This is used for forgotten password requests.</p>

                        <?php
                        /** @var FormHelper $formChangeEmail */
                        $formChangeEmail = $data['form-change_email'];
                        ?>

                        <?php if ($formChangeEmail->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-change_email" role="alert"><?php echo Security::escHTML($formChangeEmail->getErrorMessage()); ?></div>
                        <?php } ?>

                        <?php if ($formChangeEmail->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-change_email"><?php echo Security::escHTML($formChangeEmail->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <?php if (!empty($data['email'])) { ?>
                        <div class="form__element">
                            <label class="form__label" for="form-change_email-input-current_email" id="form-change_email-label-current_email">Current Email</label>
                            <input aria-labelledby="form-change_email-label-current_email" class="form__input form__input--disabled" disabled id="form-change_email-input-current_email" name="form-change_email-input-current_email" type="text" value="<?php echo Security::escAttr($data['email']); ?>"/>
                        </div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-change_email-input-new_email" id="form-change_email-label-new_email">New Email</label>
                            <div class="form__container<?php echo $formChangeEmail->getClassError('new_email', ' form__container--error'); ?>">
                                <input aria-invalid="false" aria-labelledby="form-change_email-label-new_email<?php echo $formChangeEmail->getClassError('new_email', ' form-change_email-label-new_email-error'); ?>" aria-required="true" class="form__input form__input--invisible<?php echo $formChangeEmail->getClassError('new_email', ' form__input--error'); ?>" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-change_email-input-new_email" name="form-change_email-input-new_email" type="text" value="<?php echo Security::escAttr($formChangeEmail->getInputValue('new_email')); ?>"/>
                                <span class="form__feedback<?php echo $formChangeEmail->getClassError('new_email', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formChangeEmail->getInputError('new_email') !== '') { ?>
                                <label class="form__label form__label--error" for="form-change_email-input-new_email" id="form-change_email-label-new_email-error"><?php echo Security::escHTML($formChangeEmail->getInputError('new_email')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-change_email-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-change_email-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-change_email-submit" name="form-change_email-submit" type="submit" value="Change email"/>
                        <?php unset($formChangeEmail); ?>
                    </form>

                    <form action="#form-change_username" data-form-speak-error="Form is invalid:" id="form-change_username" method="post">
                        <h2 class="block__title block__title--form">Change <span class="block__title--emphasis">username</span></h2>
                        <hr class="block__hr block__hr--form"/>

                        <?php
                        /** @var FormHelper $formChangeUsername */
                        $formChangeUsername = $data['form-change_username'];
                        ?>

                        <?php if ($formChangeUsername->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert"><?php echo Security::escHTML($formChangeUsername->getErrorMessage()); ?></div>
                        <?php } ?>

                        <?php if ($formChangeUsername->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-change_username"><?php echo Security::escHTML($formChangeUsername->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-change_username-input-current_username" id="form-change_username-label-current_username">Current Username</label>
                            <input aria-labelledby="form-change_username-label-current_username" class="form__input form__input--disabled" disabled id="form-change_username-input-current_username" name="form-change_username-input-current_username" type="text" value="<?php echo Security::escAttr($data['username']); ?>"/>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-change_username-input-new_username" id="form-change_username-label-new_username">New Username</label>
                            <div class="form__container<?php echo $formChangeUsername->getClassError('new_username', ' form__container--error'); ?>">
                                <input aria-invalid="false" aria-labelledby="form-change_username-label-new_username<?php echo $formChangeUsername->getClassError('new_username', ' form-change_username-label-new_username-error'); ?>" aria-required="true" class="form__input form__input--invisible<?php echo $formChangeUsername->getClassError('new_username', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-error-required="Username is required" data-form-has-container data-form-rules="required|regex:^[a-zA-Z0-9._ -]*$" id="form-change_username-input-new_username" name="form-change_username-input-new_username" type="text" value="<?php echo Security::escAttr($formChangeUsername->getInputValue('new_username')); ?>"/>
                                <span class="form__feedback<?php echo $formChangeUsername->getClassError('new_username', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formChangeUsername->getInputError('new_username') !== '') { ?>
                                <label class="form__label form__label--error" for="form-change_username-input-new_username" id="form-change_username-label-new_username-error"><?php echo Security::escHTML($formChangeUsername->getInputError('new_username')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-change_username-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-change_username-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-change_username-submit" name="form-change_username-submit" type="submit" value="Change username"/>
                        <?php unset($formChangeUsername); ?>
                    </form>

                    <form action="#form-change_password" data-form-speak-error="Form is invalid:" id="form-change_password" method="post">
                        <h2 class="block__title block__title--form"><?php echo ($data['has_password'] === false) ? 'Add' : 'Change'; ?> <span class="block__title--emphasis">password</span></h2>
                        <hr class="block__hr block__hr--form"/>
                        <p>This is used to log in with your username.</p>

                        <?php
                        /** @var FormHelper $formChangePassword */
                        $formChangePassword = $data['form-change_password'];
                        ?>

                        <?php if ($formChangePassword->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert"><?php echo Security::escHTML($formChangePassword->getErrorMessage()); ?></div>
                        <?php } ?>

                        <?php if ($formChangePassword->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-change_password"><?php echo Security::escHTML($formChangePassword->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-change_password-input-new_password" id="form-change_password-label-new_password">New Password</label>
                            <div class="form__container<?php echo $formChangePassword->getClassError('new_password', ' form__container--error'); ?>">
                                <input aria-describedby="form-change_password-span-new_password" aria-invalid="false" aria-labelledby="form-change_password-label-new_password<?php echo $formChangePassword->getClassError('new_password', ' form-change_password-label-new_password-error'); ?>" aria-required="true" class="form__input form__input--invisible<?php echo $formChangePassword->getClassError('new_password', ' form__input--error'); ?>" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-change_password-input-new_password" name="form-change_password-input-new_password" type="password"/>
                                <span class="form__feedback<?php echo $formChangePassword->getClassError('new_password', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formChangePassword->getInputError('new_password') !== '') { ?>
                                <label class="form__label form__label--error" for="form-change_password-input-new_password" id="form-change_password-label-new_password-error"><?php echo Security::escHTML($formChangePassword->getInputError('new_password')); ?></label>
                            <?php } ?>
                            <span class="form__help" id="form-change_password-span-new_password">Minimum of 10 characters with 1 digit and 1 uppercase and 1 lowercase and 1 special characters</span>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-change_password-input-new_password_confirm" id="form-change_password-label-new_password_confirm">Confirm New Password</label>
                            <div class="form__container<?php echo $formChangePassword->getClassError('new_password_confirm', ' form__container--error'); ?>">
                                <input aria-describedby="form-change_password-span-new_password_confirm" aria-invalid="false" aria-labelledby="form-change_password-label-new_password_confirm<?php echo $formChangePassword->getClassError('new_password_confirm', ' form-change_password-label-new_password_confirm-error'); ?>" aria-required="true" class="form__input form__input--invisible<?php echo $formChangePassword->getClassError('new_password_confirm', ' form__input--error'); ?>" data-form-error-equal_field="Confirm Password must be the same as Password" data-form-error-required="Confirm Password is required" data-form-has-container data-form-rules="required|equal_field:form-change_password-input-new_password" id="form-change_password-input-new_password_confirm" name="form-change_password-input-new_password_confirm" type="password"/>
                                <span class="form__feedback<?php echo $formChangePassword->getClassError('new_password_confirm', ' form__feedback--error'); ?>"></span>
                            </div>
                            <?php if ($formChangePassword->getInputError('new_password_confirm') !== '') { ?>
                                <label class="form__label form__label--error" for="form-change_password-input-new_password_confirm" id="form-change_password-label-new_password_confirm-error"><?php echo Security::escHTML($formChangePassword->getInputError('new_password_confirm')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-change_password-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-change_password-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-change_password-submit" name="form-change_password-submit" type="submit" value="Update password"/>
                        <?php unset($formChangePassword); ?>
                    </form>

                    <form action="#form-generate_api_key" data-form-speak-error="Form is invalid:" id="form-generate_api_key" method="post">
                        <h2 class="block__title block__title--form">Generate <span class="block__title--emphasis">api key</span></h2>
                        <hr class="block__hr block__hr--form"/>
                        <p>You can use blueprintUE with an API for uploading blueprints. Documentation is available on Postman: <a href="https://www.postman.com/blueprintue/workspace/blueprintue/api/bc237829-3bc2-4476-977b-f5765e528e59" target="_blank">https://www.postman.com/blueprintue/workspace/blueprintue/api/bc237829-3bc2-4476-977b-f5765e528e59</a></p>

                        <?php
                        /** @var FormHelper $formGenerateApiKey */
                        $formGenerateApiKey = $data['form-generate_api_key'];
                        ?>

                        <?php if ($formGenerateApiKey->hasSuccessMessage()) { ?>
                            <div class="block__info block__info--success" data-flash-success-for="form-generate_api_key"><?php echo Security::escHTML($formGenerateApiKey->getSuccessMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-generate_api_key-input-current_api_key" id="form-generate_api_key-label-current_api_key">Current API Key</label>
                            <input aria-labelledby="form-generate_api_key-label-current_api_key" class="form__input form__input--disabled" disabled id="form-generate_api_key-input-current_api_key" name="form-generate_api_key-input-current_api_key" type="text" value="<?php echo Security::escAttr($data['api_key']); ?>"/>
                        </div>

                        <input name="form-generate_api_key-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-generate_api_key-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--primary" id="form-generate_api_key-submit" name="form-generate_api_key-submit" type="submit" value="Generate API Key"/>
                        <?php unset($formGenerateApiKey); ?>
                    </form>

                    <form action="#form-delete_profile" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure?" data-form-confirm-yes="Yes" data-form-speak-error="Form is invalid:" id="form-delete_profile" method="post">
                        <h2 class="block__title block__title--form">Delete your <span class="block__title--emphasis">profile</span></h2>
                        <hr class="block__hr block__hr--form"/>
                        <p>If you delete your profile, give blueprints to anonymous user will keep content, properties and expose. Blueprints with private exposure will be deleted.</p>

                        <?php
                        /** @var FormHelper $formDeleteProfile */
                        $formDeleteProfile = $data['form-delete_profile'];
                        ?>

                        <?php if ($formDeleteProfile->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert"><?php echo Security::escHTML($formDeleteProfile->getErrorMessage()); ?></div>
                        <?php } ?>

                        <div class="form__element">
                            <label class="form__label" for="form-delete_profile-select-blueprints_ownership" id="form-delete_profile-label-blueprints_ownership">Blueprints ownership</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-delete_profile-label-blueprints_ownership<?php echo $formDeleteProfile->getClassError('blueprints_ownership', ' form-delete_profile-label-blueprints_ownership-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formDeleteProfile->getClassError('blueprints_ownership', ' form__input--error'); ?>" id="form-delete_profile-select-blueprints_ownership" name="form-delete_profile-select-blueprints_ownership">
                                    <option value="give"<?php echo ($data['has_not_anonymous_user']) ? ' disabled="disabled"' : $formDeleteProfile->getSelectedValue('blueprints_ownership', 'give'); ?><?php echo (!$data['has_not_anonymous_user'] && $formDeleteProfile->getInputValue('blueprints_ownership') === '') ? ' selected="selected"' : ''; ?>>Give my blueprints to anonymous user</option>
                                    <option value="delete"<?php echo $formDeleteProfile->getSelectedValue('blueprints_ownership', 'delete'); ?>>Delete my blueprints</option>
                                </select>
                            </div>
                            <?php if ($formDeleteProfile->getInputError('blueprints_ownership') !== '') { ?>
                                <label class="form__label form__label--error" for="form-delete_profile-select-blueprints_ownership" id="form-delete_profile-label-blueprints_ownership-error"><?php echo Security::escHTML($formDeleteProfile->getInputError('blueprints_ownership')); ?></label>
                            <?php } ?>
                        </div>

                        <div class="form__element">
                            <label class="form__label" for="form-delete_profile-select-comments_ownership" id="form-delete_profile-label-comments_ownership">Comments ownership</label>
                            <div class="form__container form__container--select">
                                <select aria-invalid="false" aria-labelledby="form-delete_profile-label-comments_ownership<?php echo $formDeleteProfile->getClassError('comments_ownership', ' form-delete_profile-label-comments_ownership-error'); ?>" aria-required="true" class="form__input form__input--select<?php echo $formDeleteProfile->getClassError('comments_ownership', ' form__input--error'); ?>" id="form-delete_profile-select-comments_ownership" name="form-delete_profile-select-comments_ownership">
                                    <option value="keep"<?php echo $formDeleteProfile->getSelectedValue('comments_ownership', 'keep'); ?><?php echo ($formDeleteProfile->getInputValue('comments_ownership') === '') ? ' selected="selected"' : ''; ?>>Keep my name and comments</option>
                                    <option value="anonymize"<?php echo $formDeleteProfile->getSelectedValue('comments_ownership', 'anonymize'); ?>>Use guest name and keep comments</option>
                                    <option value="delete"<?php echo $formDeleteProfile->getSelectedValue('comments_ownership', 'delete'); ?>>Delete comments</option>
                                </select>
                            </div>
                            <?php if ($formDeleteProfile->getInputError('comments_ownership') !== '') { ?>
                                <label class="form__label form__label--error" for="form-delete_profile-select-comments_ownership" id="form-delete_profile-label-comments_ownership-error"><?php echo Security::escHTML($formDeleteProfile->getInputError('comments_ownership')); ?></label>
                            <?php } ?>
                        </div>

                        <input name="form-delete_profile-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-delete_profile-hidden-csrf']); ?>"/>
                        <input class="form__button form__button--warning" id="form-delete_profile-submit" name="form-delete_profile-submit" type="submit" value="Delete profile"/>
                        <?php unset($formDeleteProfile); ?>
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

    <div aria-labelledby="popin-upload-avatar-h2" class="popin" id="popin-upload-avatar" role="dialog">
        <div class="popin__mask">
            <a class="popin__back" href="#" tabindex="-1"></a>
            <div class="popin__container">
                <div class="popin__body">
                    <div class="popin__header">
                        <h2 class="popin__title" id="popin-upload-avatar-h2">Change <span class="popin__title--span">your avatar</span></h2>
                    </div>
                    <div class="popin__content"
                         data-uploader
                         data-uploader-btn_cancel-id="popin-upload-avatar-cancel"
                         data-uploader-btn_save-id="popin-upload-avatar-save"
                         data-uploader-callback-save-success="window.blueprintUE.www.callbackSave"
                         data-uploader-callback-zoom-init="window.blueprintUE.www.callbackInitZoom"
                         data-uploader-callback-zoom-update="window.blueprintUE.www.callbackUpdateZoom"
                         data-uploader-canvas-id="popin-upload-avatar-canvas"
                         data-uploader-css-canvas_moving="uploader__canvas--moving"
                         data-uploader-div_error-id="popin-upload-avatar-error"
                         data-uploader-div_preview-id="popin-upload-avatar-preview"
                         data-uploader-div_upload-id="popin-upload-avatar-upload"
                         data-uploader-input_file-id="popin-upload-avatar-input_file"
                         data-uploader-input_zoom-id="popin-upload-avatar-zoom"
                         data-uploader-mask-color="rgba(255,255,255,0.7)"
                         data-uploader-mask-radius="20"
                         data-uploader-mask-size="200"
                         data-uploader-upload-url="/upload/user/<?php echo (int) $data['profile_id']; ?>/avatar/"
                         data-uploader-upload-name="avatar"
                         data-uploader-upload-params-csrf="<?php echo Security::escAttr($data['data-uploader-upload-params-csrf']); ?>"
                    >
                        <div id="popin-upload-avatar-upload">
                            <div class="form__element form__element--center">
                                <label class="form__button form__button--small" for="popin-upload-avatar-input_file">Upload photo</label>
                                <input accept="image/*" class="form__input form__input--hidden" id="popin-upload-avatar-input_file" type="file">
                            </div>
                        </div>
                        <div id="popin-upload-avatar-preview">
                            <canvas class="uploader__canvas" height="310" id="popin-upload-avatar-canvas" width="310"></canvas>
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
                                    <input aria-label="Range input for zooming photo" class="uploader__zoom-input" id="popin-upload-avatar-zoom" max="50" min="1" step="1" type="range" value="1">
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
                                <button class="form__button form__button--small" id="popin-upload-avatar-save">Save</button>
                                <button class="form__button form__button--secondary form__button--small" id="popin-upload-avatar-cancel">Cancel</button>
                            </div>
                        </div>
                        <div class="uploader__error" id="popin-upload-avatar-error"></div>
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
