<?php

/* @noinspection PhpUnhandledExceptionInspection */

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
        include Application::getFolder('VIEWS') . 'www/parts/nav.php';
        ?>
    </header>
    <main class="main">
        <div class="block__container block__container--first block__container--last">
            <div class="block__element">
                <h2 class="block__title">Contact</h2>
                <?php
                    /** @var FormHelper $formContact */
                    $formContact = $data['form-contact'];
                ?>
                <?php if ($formContact->hasErrorMessage()) { ?>
                <div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert"><?php echo Security::escHTML($formContact->getErrorMessage()); ?></div>
                <?php } ?>

                <?php if ($formContact->hasSuccessMessage()) { ?>
                <div class="block__info block__info--success" data-flash-success-for="form-contact"><?php echo Security::escHTML($formContact->getSuccessMessage()); ?></div>
                <?php } else { ?>
                <form action="/contact/" data-form-speak-error="Form is invalid:" id="form-contact" method="post">
                    <div class="form__element">
                        <label class="form__label" for="form-contact-input-name" id="form-contact-label-name">Name <span class="form__label--info">(required)</span></label>
                        <div class="form__container<?php echo $formContact->getClassError('name', ' form__container--error'); ?>">
                            <input aria-invalid="false" aria-labelledby="form-contact-label-name<?php echo $formContact->getClassError('name', ' form-contact-label-name-error'); ?>" aria-required="true" autocomplete="name" class="form__input form__input--invisible<?php echo $formContact->getClassError('name', ' form__input--error'); ?>" data-form-error-required="Name is required" data-form-has-container data-form-rules="required" id="form-contact-input-name" name="form-contact-input-name" type="text" value="<?php echo Security::escAttr($formContact->getInputValue('name')); ?>"/>
                            <span class="form__feedback<?php echo $formContact->getClassError('name', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formContact->getInputError('name') !== '') { ?>
                        <label class="form__label form__label--error" for="form-contact-input-name" id="form-contact-label-name-error"><?php echo Security::escHTML($formContact->getInputError('name')); ?></label>
                        <?php } ?>
                    </div>
                    <div class="form__element">
                        <label class="form__label" for="form-contact-input-email" id="form-contact-label-email">Email for response <span class="form__label--info">(required)</span></label>
                        <div class="form__container<?php echo $formContact->getClassError('email', ' form__container--error'); ?>">
                            <input aria-invalid="false" aria-labelledby="form-contact-label-email<?php echo $formContact->getClassError('email', ' form-contact-label-email-error'); ?>" aria-required="true" autocomplete="email" class="form__input form__input--invisible<?php echo $formContact->getClassError('email', ' form__input--error'); ?>" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-contact-input-email" name="form-contact-input-email" type="text" value="<?php echo Security::escAttr($formContact->getInputValue('email')); ?>"/>
                            <span class="form__feedback<?php echo $formContact->getClassError('email', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formContact->getInputError('email') !== '') { ?>
                        <label class="form__label form__label--error" for="form-contact-input-email" id="form-contact-label-email-error"><?php echo Security::escHTML($formContact->getInputError('email')); ?></label>
                        <?php } ?>
                    </div>
                    <div class="form__element">
                        <label class="form__label" for="form-contact-textarea-message" id="form-contact-label-message">Message <span class="form__label--info">(required)</span></label>
                        <div class="form__container form__container--textarea<?php echo $formContact->getClassError('message', ' form__container--error'); ?>">
                            <textarea aria-invalid="false" aria-labelledby="form-contact-label-message<?php echo $formContact->getClassError('message', ' form-contact-label-message-error'); ?>" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--message<?php echo $formContact->getClassError('message', ' form__input--error'); ?>" data-form-error-required="Message is required" data-form-has-container data-form-rules="required" id="form-contact-textarea-message" name="form-contact-textarea-message"><?php echo Security::escHTML($formContact->getInputValue('message')); ?></textarea>
                            <span class="form__feedback<?php echo $formContact->getClassError('message', ' form__feedback--error'); ?>"></span>
                        </div>
                        <?php if ($formContact->getInputError('message') !== '') { ?>
                        <label class="form__label form__label--error" for="form-contact-textarea-message" id="form-contact-label-message-error"><?php echo Security::escHTML($formContact->getInputError('message')); ?></label>
                        <?php } ?>
                    </div>
                    <input name="form-contact-hidden-csrf" type="hidden" value="<?php echo Security::escAttr($data['form-contact-hidden-csrf']); ?>"/>
                    <input class="form__button form__button--primary" id="form-contact-submit" name="form-contact-submit" type="submit" value="Send Message"/>
                </form>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include Application::getFolder('VIEWS') . 'www/parts/footer.php'; ?>

    <?php include Application::getFolder('VIEWS') . 'www/parts/account_popins.php'; ?>

    <script src="/site.js"></script>
</body>
