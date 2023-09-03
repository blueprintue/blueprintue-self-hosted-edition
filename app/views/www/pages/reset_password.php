<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use app\helpers\FormHelper;
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
        <?php if ($data['has_reset_token']) { ?>
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Reset password</h2>

                <?php
                /** @var FormHelper $formResetPassword */
                $formResetPassword = $data['form-reset_password'];
                ?>
                <?php if ($formResetPassword->hasErrorMessage()) { ?>
                    <div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert"><?php echo Security::escHTML($formResetPassword->getErrorMessage()); ?></div>
                <?php } ?>

                <?php if ($formResetPassword->hasSuccessMessage()) { ?>
                    <div class="block__info block__info--success" data-flash-success-for="form-reset_password"><?php echo Security::escHTML($formResetPassword->getSuccessMessage()); ?></div>
                    <p><a href="/#popin-login">Now you can go back to the home page to login with your new password (or click on this link).</a></p>
                <?php } else { ?>
                <form action="/reset-password/?reset_token=<?php echo Security::escAttr($data['reset_token']); ?>" data-form-speak-error="Form is invalid:" id="form-reset_password" method="post">
                    <div class="form__element">
                        <label class="form__label" for="form-reset_password-input-email" id="form-reset_password-label-email">Email <span class="form__label--info">(required)</span></label>
                        <div class="form__container<?php echo $formResetPassword->getClassError('email', ' form__container--error'); ?>">
                            <input aria-invalid="false" aria-labelledby="form-reset_password-label-email<?php echo $formResetPassword->getClassError('email', ' form-reset_password-label-email-error'); ?>" aria-required="true" autocomplete="email" class="form__input form__input--invisible<?php echo $formResetPassword->getClassError('email', ' form__input--error'); ?>" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-reset_password-input-email" name="form-reset_password-input-email" type="text" value="<?php echo Security::escAttr($formResetPassword->getInputValue('email')); ?>"/>
                            <span class="form__feedback<?php echo $formResetPassword->getClassError('email', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formResetPassword->getInputError('email') !== '') { ?>
                        <label class="form__label form__label--error" for="form-reset_password-input-email" id="form-reset_password-label-email-error"><?php echo Security::escHTML($formResetPassword->getInputError('email')); ?></label>
                        <?php } ?>
                    </div>
                    <div class="form__element">
                        <label class="form__label" for="form-reset_password-input-password" id="form-reset_password-label-password">New Password <span class="form__label--info">(required)</span></label>
                        <div class="form__container<?php echo $formResetPassword->getClassError('password', ' form__container--error'); ?>">
                            <input aria-describedby="form-reset_password-span-password" aria-invalid="false" aria-labelledby="form-reset_password-label-password<?php echo $formResetPassword->getClassError('password', ' form-reset_password-label-password-error'); ?>" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible<?php echo $formResetPassword->getClassError('password', ' form__input--error'); ?>" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-reset_password-input-password" name="form-reset_password-input-password" type="password"/>
                            <span class="form__feedback<?php echo $formResetPassword->getClassError('password', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formResetPassword->getInputError('password') !== '') { ?>
                        <label class="form__label form__label--error" for="form-reset_password-input-password" id="form-reset_password-label-password-error"><?php echo Security::escHTML($formResetPassword->getInputError('password')); ?></label>
                        <?php } ?>
                        <span class="form__help" id="form-reset_password-span-password">Minimum of 10 characters with 1 digit and 1 uppercase and 1 lowercase and 1 special characters</span>
                    </div>
                    <div class="form__element">
                        <label class="form__label" for="form-reset_password-input-password_confirm" id="form-reset_password-label-password_confirm">Confirm new password <span class="form__label--info">(required)</span></label>
                        <div class="form__container<?php echo $formResetPassword->getClassError('password_confirm', ' form__container--error'); ?>">
                            <input aria-invalid="false" aria-labelledby="form-reset_password-label-password_confirm<?php echo $formResetPassword->getClassError('password_confirm', ' form-reset_password-label-password_confirm-error'); ?>" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible<?php echo $formResetPassword->getClassError('password_confirm', ' form__input--error'); ?>" data-form-error-equal_field="Confirm New Password must be the same as New Password" data-form-error-required="Confirm New Password is required" data-form-has-container data-form-rules="required|equal_field:form-reset_password-input-password" id="form-reset_password-input-password_confirm" name="form-reset_password-input-password_confirm" type="password"/>
                            <span class="form__feedback<?php echo $formResetPassword->getClassError('password_confirm', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formResetPassword->getInputError('password_confirm') !== '') { ?>
                        <label class="form__label form__label--error" for="form-reset_password-input-password_confirm" id="form-reset_password-label-password_confirm-error"><?php echo Security::escHTML($formResetPassword->getInputError('password_confirm')); ?></label>
                        <?php } ?>
                    </div>
                    <input name="form-reset_password-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-reset_password-hidden-csrf']); ?>"/>
                    <input class="form__button form__button--primary" id="form-reset_password-submit" name="form-reset_password-submit" type="submit" value="Reset my password"/>
                </form>
                <?php } ?>
            </div>
        </div>
        <?php } else { ?>
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Reset password</h2>
                <h3 class="block__subtitle">If you have requested a password reset then you have to check your emails.</h3>
            </div>
        </div>
        <?php } ?>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <script src="/site.js"></script>
</body>