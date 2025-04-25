<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Contact;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class ContactTest extends TestCase
{
    use Common;

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testContactGET(): void
    {
        $response = $this->getResponseFromApplication('GET', '/contact/');
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => 'Contact us | This is a base title',
            'description' => 'Contact&#x20;us'
        ]);
        $this->doTestHtmlBody($response, '<h2 class="block__title">Contact</h2>');
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasNoLinkActive($response);
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testContactGETInvalidConfigurationEmail(): void
    {
        $response = $this->getResponseFromApplication('GET', '/contact/', [], [], [], [], [], [], [], 'tests-invalid-mail-contact-to.env');
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => 'Contact us | This is a base title',
            'description' => 'Contact&#x20;us'
        ]);
        $this->doTestHtmlBody($response, '<h2 class="block__title">Contact</h2>');
        $this->doTestHtmlBody($response, '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, could not use this form, &quot;MAIL_CONTACT_TO&quot; env variable is invalid.</div>');
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasNoLinkActive($response);
    }

    public static function provideDataCases(): iterable
    {
        return [
            'xss email - OK' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => '0<script>alert("name");</script>',
                    'form-contact-input-email'      => '0<script>alert("email");</script>@<script>alert("email");</script>',
                    'form-contact-textarea-message' => '0<script>alert("message");</script>'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailText'           => "Name: 0<script>alert(\"name\");</script>\nEmail: 0<script>alert(\"email\");</script>@<script>alert(\"email\");</script>\nMessage: 0<script>alert(\"message\");</script>",
                'mailSent'           => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">Message sent successfully</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'xss form - KO' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => '1<script>alert("name");</script>',
                    'form-contact-input-email'      => '1<script>alert("email");</script><script>alert("email");</script>',
                    'form-contact-textarea-message' => '1<script>alert("message");</script>'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['name', 'email', 'message'],
                'fieldsLabelError' => [
                    'email' => 'Email is invalid'
                ],
            ],
            'send mail OK' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => '20',
                    'form-contact-input-email'      => '20@0',
                    'form-contact-textarea-message' => '20'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailText'           => "Name: 20\nEmail: 20@0\nMessage: 20",
                'mailSent'           => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">Message sent successfully</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'send mail KO' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => '30',
                    'form-contact-input-email'      => '30@0',
                    'form-contact-textarea-message' => '30'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailText'           => "Name: 30\nEmail: 30@0\nMessage: 30",
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, could not sent message, try later</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'csrf incorrect' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'incorrect_csrf',
                    'form-contact-input-name'       => '40',
                    'form-contact-input-email'      => '40',
                    'form-contact-textarea-message' => '40'
                ],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no fields' => [
                'params'             => [],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'params' => [
                    'form-contact-input-name'       => '50',
                    'form-contact-input-email'      => '50',
                    'form-contact-textarea-message' => '50'
                ],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no name' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'incorrect_csrf',
                    'form-contact-input-email'      => '60',
                    'form-contact-textarea-message' => '60'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no email' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'incorrect_csrf',
                    'form-contact-input-name'       => '70',
                    'form-contact-textarea-message' => '70'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no message' => [
                'params' => [
                    'form-contact-hidden-csrf'   => 'incorrect_csrf',
                    'form-contact-input-name'    => '80',
                    'form-contact-input-email'   => '80',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - name empty' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => ' ',
                    'form-contact-input-email'      => 'em@ail',
                    'form-contact-textarea-message' => 'message'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => ['name'],
                'fieldsHasValue'   => ['name', 'email', 'message'],
                'fieldsLabelError' => [
                    'name' => 'Name is required'
                ],
            ],
            'empty fields - email empty' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => 'name',
                    'form-contact-input-email'      => ' ',
                    'form-contact-textarea-message' => '0'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['name', 'email', 'message'],
                'fieldsLabelError' => [
                    'email' => 'Email is required'
                ],
            ],
            'empty fields - message empty' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => 'name',
                    'form-contact-input-email'      => 'em@ail',
                    'form-contact-textarea-message' => ' '
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => ['message'],
                'fieldsHasValue'   => ['name', 'email', 'message'],
                'fieldsLabelError' => [
                    'message' => 'Message is required'
                ],
            ],
            'invalid fields - email' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => 'name',
                    'form-contact-input-email'      => 'a',
                    'form-contact-textarea-message' => 'message'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['name', 'email', 'message'],
                'fieldsLabelError' => [
                    'email' => 'Email is invalid'
                ],
            ],
            'invalid encoding fields - name' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => \chr(99999999),
                    'form-contact-input-email'      => '40',
                    'form-contact-textarea-message' => '40'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - email' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => '40',
                    'form-contact-input-email'      => \chr(99999999),
                    'form-contact-textarea-message' => '40'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - message' => [
                'params' => [
                    'form-contact-hidden-csrf'      => 'csrf_is_replaced',
                    'form-contact-input-name'       => '40',
                    'form-contact-input-email'      => '40',
                    'form-contact-textarea-message' => \chr(99999999)
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-contact">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-contact" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
        ];
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('provideDataCases')]
    public function testContactPOST(array $params, bool $useCsrfFromSession, int $mailCalled, string $mailText, bool $mailSent, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        // set how mail must return in $_SESSION
        $session = [
            'remove' => [],
            'set'    => [
                'phpunit_mail_called' => 0,
                'phpunit_mail_text'   => $mailText,
                'phpunit_mail_sent'   => $mailSent,
            ],
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/contact/', [], $session);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-contact-hidden-csrf'] = $_SESSION['csrf'];
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/contact/', $params);
        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', '/contact/');
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlMain($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlMainNot($response, $flashMessages['success']['message']);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlMain($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlMainNot($response, $flashMessages['error']['message']);
        }

        static::assertSame($mailCalled, $_SESSION['phpunit_mail_called']);

        if ($hasRedirection && $isFormSuccess) {
            return;
        }

        // test fields HTML
        $fields = ['name', 'email', 'message'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'name') {
                $value = $hasValue ? Helper::trim($params['form-contact-input-name']) : '';
                $this->doTestHtmlForm($response, '/contact/', $this->getHTMLFieldName($value, $hasError, $labelError));
            }

            if ($field === 'email') {
                $value = $hasValue ? Helper::trim($params['form-contact-input-email']) : '';
                $this->doTestHtmlForm($response, '/contact/', $this->getHTMLFieldEmail($value, $hasError, $labelError));
            }

            if ($field === 'message') {
                $value = $hasValue ? Helper::trim($params['form-contact-textarea-message']) : '';
                $this->doTestHtmlForm($response, '/contact/', $this->getHTMLFieldMessage($value, $hasError, $labelError));
            }
        }
    }

    /** @throws SecurityException */
    protected function getHTMLFieldName(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-contact-label-name form-contact-label-name-error" aria-required="true" autocomplete="name" class="form__input form__input--invisible form__input--error" data-form-error-required="Name is required" data-form-has-container data-form-rules="required" id="form-contact-input-name" name="form-contact-input-name" type="text" value="{$v}"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-contact-input-name" id="form-contact-label-name-error">{$labelError}</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-contact-label-name" aria-required="true" autocomplete="name" class="form__input form__input--invisible" data-form-error-required="Name is required" data-form-has-container data-form-rules="required" id="form-contact-input-name" name="form-contact-input-name" type="text" value="{$v}"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldEmail(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-contact-label-email form-contact-label-email-error" aria-required="true" autocomplete="email" class="form__input form__input--invisible form__input--error" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-contact-input-email" name="form-contact-input-email" type="text" value="{$v}"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-contact-input-email" id="form-contact-label-email-error">{$labelError}</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-contact-label-email" aria-required="true" autocomplete="email" class="form__input form__input--invisible" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-contact-input-email" name="form-contact-input-email" type="text" value="{$v}"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldMessage(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--textarea form__container--error">
<textarea aria-invalid="false" aria-labelledby="form-contact-label-message form-contact-label-message-error" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--message form__input--error" data-form-error-required="Message is required" data-form-has-container data-form-rules="required" id="form-contact-textarea-message" name="form-contact-textarea-message">{$v}</textarea>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-contact-textarea-message" id="form-contact-label-message-error">{$labelError}</label>
HTML;
        }

        return <<<HTML
<div class="form__container form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-contact-label-message" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--message" data-form-error-required="Message is required" data-form-has-container data-form-rules="required" id="form-contact-textarea-message" name="form-contact-textarea-message">{$v}</textarea>
<span class="form__feedback"></span>
</div>
HTML;
    }

    public static function mailForPHPUnit($to, $subject, $message): bool
    {
        ++$_SESSION['phpunit_mail_called'];

        static::assertSame('contact@blueprintue.test', $to);
        static::assertSame('Contact From this_site_name', $subject);
        static::assertSame($_SESSION['phpunit_mail_text'], $message);

        return $_SESSION['phpunit_mail_sent'];
    }
}
