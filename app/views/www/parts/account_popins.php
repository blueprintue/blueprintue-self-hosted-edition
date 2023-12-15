<?php

/* @noinspection PhpUnhandledExceptionInspection */
/* phpcs:disable Generic.Files.LineLength */

declare(strict_types=1);

use app\helpers\FormHelper;
use Rancoud\Application\Application;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

/* @var $data array */
?>
<?php if (!Session::has('userID')) { ?>
    <div aria-labelledby="popin-login-h2" class="popin" id="popin-login" role="dialog">
        <div class="popin__mask">
            <a class="popin__back" href="#" tabindex="-1"></a>
            <div class="popin__container">
                <div class="popin__body">
                    <div class="popin__header">
                        <h2 class="popin__title" id="popin-login-h2">Log in</h2>
                    </div>
                    <div class="popin__content">
                        <?php
                        /** @var FormHelper $formLogin */
                        $formLogin = $data['form-login'];
                        ?>
                        <?php if ($formLogin->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert"><?php echo Security::escHTML($formLogin->getErrorMessage()); ?></div>
                        <?php } ?>
                        <form action="#popin-login" data-form-speak-error="Form is invalid:" id="form-login" method="post">
                            <div class="form__element">
                                <label class="form__label" for="form-login-input-username" id="form-login-label-username">Username</label>
                                <div class="form__container">
                                    <input aria-invalid="false" aria-labelledby="form-login-label-username" aria-required="true" autocomplete="username" class="form__input form__input--invisible" data-form-error-required="Username is required" data-form-has-container data-form-rules="required" id="form-login-input-username" name="form-login-input-username" type="text"/>
                                    <span class="form__feedback"></span>
                                </div>
                            </div>
                            <div class="form__element">
                                <label class="form__label" for="form-login-input-password" id="form-login-label-password">Password</label>
                                <a class="popin__link popin__link--right" href="#popin-forgot_password">Forgot your password?</a>
                                <div class="form__container">
                                    <input aria-invalid="false" aria-labelledby="form-login-label-password" aria-required="true" autocomplete="current-password" class="form__input form__input--invisible" data-form-error-required="Password is required" data-form-has-container data-form-rules="required" id="form-login-input-password" name="form-login-input-password" type="password"/>
                                    <span class="form__feedback"></span>
                                </div>
                            </div>

                            <div class="form__element">
                                <input class="form__input form__input--checkbox" id="form-login-checkbox-remember" name="form-login-checkbox-remember" type="checkbox" value="remember">
                                <label class="form__label form__label--checkbox" for="form-login-checkbox-remember">
                                <span aria-hidden="true" class="form__fake-checkbox">
                                    <svg class="form__fake-checkbox-svg" version="1.1" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                        <path class="form__fake-checkbox-path" d="M461.6,109.6l-54.9-43.3c-1.7-1.4-3.8-2.4-6.2-2.4c-2.4,0-4.6,1-6.3,2.5L194.5,323c0,0-78.5-75.5-80.7-77.7  c-2.2-2.2-5.1-5.9-9.5-5.9c-4.4,0-6.4,3.1-8.7,5.4c-1.7,1.8-29.7,31.2-43.5,45.8c-0.8,0.9-1.3,1.4-2,2.1c-1.2,1.7-2,3.6-2,5.7  c0,2.2,0.8,4,2,5.7l2.8,2.6c0,0,139.3,133.8,141.6,136.1c2.3,2.3,5.1,5.2,9.2,5.2c4,0,7.3-4.3,9.2-6.2L462,121.8  c1.2-1.7,2-3.6,2-5.8C464,113.5,463,111.4,461.6,109.6z"></path>
                                    </svg>
                                </span>
                                    Remember Me
                                </label>
                            </div>
                            <input name="form-login-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-login-hidden-csrf']); ?>"/>
                            <input class="form__button form__button--large form__button--primary" id="form-login-submit" name="form-login-submit" type="submit" value="Log in"/>
                        </form>
                    </div>
                </div>

                <div class="popin__body popin__body--bottom popin__body--bg-grey">
                    <p><a class="popin__link" href="#popin-register">Create your account</a></p>
                </div>

                <a aria-label="Close" class="popin__close" href="#">
                    <svg aria-hidden="true" class="popin__close-svg">
                        <use href="/sprite/sprite.svg#icon-close"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div aria-labelledby="popin-register-h2" class="popin" id="popin-register" role="dialog">
        <div class="popin__mask">
            <a class="popin__back" href="#" tabindex="-1"></a>
            <div class="popin__container">
                <div class="popin__body">
                    <div class="popin__header">
                        <h2 class="popin__title" id="popin-register-h2">Register</h2>
                    </div>
                    <div class="popin__content">
                        <?php if ($data['has_invalid_configuration_mail_from_address']) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-register" role="alert">Error, could not use this form, "MAIL_FROM_ADDRESS" env variable is invalid.</div>
                        <?php } ?>
                        <?php
                        /** @var FormHelper $formRegister */
                        $formRegister = $data['form-register'];
                        ?>
                        <?php if ($formRegister->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert"><?php echo Security::escHTML($formRegister->getErrorMessage()); ?></div>
                        <?php } ?>
                        <form action="#popin-register" data-form-speak-error="Form is invalid:" id="form-register" method="post">
                            <div class="form__element">
                                <label class="form__label" for="form-register-input-username" id="form-register-label-username">Username</label>
                                <div class="form__container<?php echo $formRegister->getClassError('username', ' form__container--error'); ?>">
                                    <input aria-invalid="false" aria-labelledby="form-register-label-username<?php echo $formRegister->getClassError('username', ' form-register-label-username-error'); ?>" aria-required="true" autocomplete="username" class="form__input form__input--invisible<?php echo $formRegister->getClassError('username', ' form__input--error'); ?>" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-error-required="Username is required" data-form-has-container data-form-rules="required|regex:^[a-zA-Z0-9._ -]*$" id="form-register-input-username" name="form-register-input-username" type="text" value="<?php echo Security::escAttr($formRegister->getInputValue('username')); ?>"/>
                                    <span class="form__feedback<?php echo $formRegister->getClassError('username', ' form__feedback--error'); ?>"></span>
                                </div>
                                <?php if ($formRegister->getInputError('username') !== '') { ?>
                                    <label class="form__label form__label--error" for="form-register-input-username" id="form-register-label-username-error"><?php echo Security::escHTML($formRegister->getInputError('username')); ?></label>
                                <?php } ?>
                            </div>
                            <div class="form__element">
                                <label class="form__label" for="form-register-input-email" id="form-register-label-email">Email</label>
                                <div class="form__container<?php echo $formRegister->getClassError('email', ' form__container--error'); ?>">
                                    <input aria-invalid="false" aria-labelledby="form-register-label-email<?php echo $formRegister->getClassError('email', ' form-register-label-email-error'); ?>" aria-required="true" autocomplete="email" class="form__input form__input--invisible<?php echo $formRegister->getClassError('email', ' form__input--error'); ?>" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-register-input-email" name="form-register-input-email" type="text" value="<?php echo Security::escAttr($formRegister->getInputValue('email')); ?>"/>
                                    <span class="form__feedback<?php echo $formRegister->getClassError('email', ' form__feedback--error'); ?>"></span>
                                </div>
                                <?php if ($formRegister->getInputError('email') !== '') { ?>
                                    <label class="form__label form__label--error" for="form-register-input-email" id="form-register-label-email-error"><?php echo Security::escHTML($formRegister->getInputError('email')); ?></label>
                                <?php } ?>
                            </div>
                            <div class="form__element">
                                <label class="form__label" for="form-register-input-password" id="form-register-label-password">Password</label>
                                <div class="form__container<?php echo $formRegister->getClassError('password', ' form__container--error'); ?>">
                                    <input aria-describedby="form-register-span-password" aria-invalid="false" aria-labelledby="form-register-label-password<?php echo $formRegister->getClassError('password', ' form-register-label-password-error'); ?>" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible<?php echo $formRegister->getClassError('password', ' form__input--error'); ?>" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-register-input-password" name="form-register-input-password" type="password"/>
                                    <span class="form__feedback<?php echo $formRegister->getClassError('password', ' form__feedback--error'); ?>"></span>
                                </div>
                                <?php if ($formRegister->getInputError('password') !== '') { ?>
                                    <label class="form__label form__label--error" for="form-register-input-password" id="form-register-label-password-error"><?php echo Security::escHTML($formRegister->getInputError('password')); ?></label>
                                <?php } ?>
                                <span class="form__help" id="form-register-span-password">Minimum of 10 characters with 1 digit and 1 uppercase and 1 lowercase and 1 special characters</span>
                            </div>
                            <div class="form__element">
                                <label class="form__label" for="form-register-input-password_confirm" id="form-register-label-password_confirm">Confirm Password</label>
                                <div class="form__container<?php echo $formRegister->getClassError('password_confirm', ' form__container--error'); ?>">
                                    <input aria-invalid="false" aria-labelledby="form-register-label-password_confirm<?php echo $formRegister->getClassError('password_confirm', ' form-register-label-password_confirm-error'); ?>" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible<?php echo $formRegister->getClassError('password_confirm', ' form__input--error'); ?>" data-form-error-equal_field="Confirm Password must be the same as Password" data-form-error-required="Confirm Password is required" data-form-has-container data-form-rules="required|equal_field:form-register-input-password" id="form-register-input-password_confirm" name="form-register-input-password_confirm" type="password"/>
                                    <span class="form__feedback<?php echo $formRegister->getClassError('password_confirm', ' form__feedback--error'); ?>"></span>
                                </div>
                                <?php if ($formRegister->getInputError('password_confirm') !== '') { ?>
                                    <label class="form__label form__label--error" for="form-register-input-password_confirm" id="form-register-label-password_confirm-error"><?php echo Security::escHTML($formRegister->getInputError('password_confirm')); ?></label>
                                <?php } ?>
                            </div>
                            <div class="form__element">
                                <p>By clicking on "Create an account" below, you are agreeing to the <a class="popin__link" href="/terms-of-service/" target="_blank">Terms of Service</a> and the <a class="popin__link" href="/privacy-policy/" target="_blank">Privacy Policy</a>.</p>
                            </div>
                            <input name="form-register-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-register-hidden-csrf']); ?>"/>
                            <input class="form__button form__button--large form__button--primary" id="form-register-submit" name="form-register-submit" type="submit" value="Create an account"/>
                        </form>
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

    <div aria-labelledby="popin-forgot_password-h2" class="popin" id="popin-forgot_password" role="dialog">
        <div class="popin__mask">
            <a class="popin__back" href="#" tabindex="-1"></a>
            <div class="popin__container">
                <div class="popin__body">
                    <div class="popin__header">
                        <h2 class="popin__title" id="popin-forgot_password-h2">Reset your <span class="popin__title--span">password</span></h2>
                    </div>
                    <div class="popin__content">
                        <?php if ($data['has_invalid_configuration_mail_from_address']) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-forgot_password" role="alert">Error, could not use this form, "MAIL_FROM_ADDRESS" env variable is invalid.</div>
                        <?php } ?>
                        <?php
                        /** @var FormHelper $formForgotPassword */
                        $formForgotPassword = $data['form-forgot_password'];
                        ?>
                        <?php if ($formForgotPassword->hasErrorMessage()) { ?>
                            <div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert"><?php echo Security::escHTML($formForgotPassword->getErrorMessage()); ?></div>
                        <?php } ?>
                        <form action="#popin-forgot_password" data-form-speak-error="Form is invalid:" id="form-forgot_password" method="post">
                            <div class="form__element">
                                <label class="form__label" for="form-forgot_password-input-email" id="form-forgot_password-label-email">Enter your email address and we will send you a link to reset your password.</label>
                                <div class="form__container<?php echo $formForgotPassword->getClassError('email', ' form__container--error'); ?>">
                                    <input aria-invalid="false" aria-labelledby="form-forgot_password-label-email<?php echo $formForgotPassword->getClassError('email', ' form-forgot_password-label-email-error'); ?>" aria-required="true" autocomplete="email" class="form__input form__input--invisible<?php echo $formForgotPassword->getClassError('email', ' form__input--error'); ?>" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-forgot_password-input-email" name="form-forgot_password-input-email" placeholder="your@email.com" type="text" value="<?php echo Security::escAttr($formForgotPassword->getInputValue('email')); ?>"/>
                                    <span class="form__feedback<?php echo $formForgotPassword->getClassError('email', ' form__feedback--error'); ?>"></span>
                                </div>
                                <?php if ($formForgotPassword->getInputError('email') !== '') { ?>
                                    <label class="form__label form__label--error" for="form-forgot_password-input-email" id="form-forgot_password-label-email-error"><?php echo Security::escHTML($formForgotPassword->getInputError('email')); ?></label>
                                <?php } ?>
                            </div>
                            <input name="form-forgot_password-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-forgot_password-hidden-csrf']); ?>"/>
                            <input class="form__button form__button--large form__button--primary" id="form-forgot_password-submit" name="form-forgot_password-submit" type="submit" value="Send link"/>
                        </form>
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
<?php } ?>
