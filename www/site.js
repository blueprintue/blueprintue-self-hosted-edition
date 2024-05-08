window.blueprintUE = {};
window.blueprintUE.www = {};
if(window.navigator.userAgent.toUpperCase().indexOf("TRIDENT") !== -1 || window.navigator.userAgent.toUpperCase().indexOf("MSIE") !== -1) {window.location.assign('/ie.html');}
(function(){
    'use strict';

    function srSpeak(text, priority) {
        if (typeof text !== 'string') {
            return new TypeError('Invalid argument text, expect string, get ' + typeof text);
        }

        priority = priority || 'polite';
        if (typeof priority !== 'string') {
            return new TypeError('Invalid argument priority, expect string, get ' + typeof priority);
        }

        if (!document.body) {
            return new ReferenceError('document.body not exist');
        }

        var id = insertDivInDom(priority);
        addTextInDivAfter100ms(id, text);
        deleteDivAfter1000ms(id);
    }

    function insertDivInDom(priority) {
        var div = document.createElement('div');
        var id = 'speak-' + Date.now();
        div.setAttribute('id', id);
        div.setAttribute('aria-live', getPriority(priority));
        div.classList.add('sr-only');
        document.body.appendChild(div);

        return id;
    }

    function getPriority(priority) {
        var props = ['off', 'polite', 'assertive'];
        var i = 0;
        var max = props.length;
        priority = priority.toLowerCase();

        for (; i < max; i++) {
            if (props[i] === priority) {
                return props[i];
            }
        }

        return 'polite';
    }

    function addTextInDivAfter100ms(id, text) {
        setTimeout(function () {
            var elem = document.getElementById(id);
            if (elem) {
                elem.appendChild(document.createTextNode(text));
            }
        }, 100);
    }

    function deleteDivAfter1000ms(id) {
        setTimeout(function () {
            var elem = document.getElementById(id);
            if (document.body && elem) {
                document.body.removeChild(elem);
            }
        }, 1000);
    }

    window.blueprintUE.www.srSpeak = srSpeak;
})();
(function(){
    'use strict';

    function CodeCopyClipboard() {
        this.copyButton = document.getElementById('fast-copy-clipboard');
        this.textareaCode = document.getElementById('code_to_copy');
        if (!this.copyButton || !this.textareaCode) {
            return
        }

        this.eventCopy = this.copy.bind(this);

        this.addEvent(this);
    }

    CodeCopyClipboard.prototype.addEvent = function() {
        this.copyButton.addEventListener('click', this.eventCopy);
    };

    CodeCopyClipboard.prototype.copy = function() {
        this.showInputs();
        this.copyClipboard();
    };

    CodeCopyClipboard.prototype.showInputs = function() {
        if (!this.textareaCode.classList.contains('blueprint__code-copy-textarea--hidden')) {
            return;
        }

        this.textareaCode.classList.remove('blueprint__code-copy-textarea--hidden');
        this.copyButton.classList.add('blueprint__code-copy-button--minified');
    };

    CodeCopyClipboard.prototype.copyClipboard = function() {
        this.textareaCode.select();
        document.execCommand("copy");
        this.textareaCode.setSelectionRange(0,0);
    };

    new CodeCopyClipboard();
})();
(function(){
    'use strict';

    function EditComment(editCommentObj) {
        var btnID = editCommentObj.getAttribute('data-edit_comment-btn_id');
        var btnCancelID = editCommentObj.getAttribute('data-edit_comment-btn_cancel_id');
        var contentID = editCommentObj.getAttribute('data-edit_comment-content_id');
        var editContentID = editCommentObj.getAttribute('data-edit_comment-edit_content_id');

        this.btnObj = document.getElementById(btnID);
        if (this.btnObj === null) {
            return;
        }

        this.btnCancelObj = document.getElementById(btnCancelID);
        if (this.btnCancelObj === null) {
            return;
        }

        this.contentObj = document.getElementById(contentID);
        if (this.contentObj === null) {
            return;
        }

        this.editContentObj = document.getElementById(editContentID);
        if (this.editContentObj === null) {
            return;
        }

        this.btnObj.addEventListener('click', this.showForm.bind(this));
        this.btnCancelObj.addEventListener('click', this.hideForm.bind(this));
        this.originalContent = this.editContentObj.querySelector('textarea').value;
    }

    EditComment.prototype.showForm = function(e) {
        e.preventDefault();

        if (!this.editContentObj.classList.contains('comment__hide')) {
            return;
        }

        this.editContentObj.querySelector('textarea').value = this.originalContent;
        this.contentObj.classList.add('comment__hide');
        this.editContentObj.classList.remove('comment__hide');
    };

    EditComment.prototype.hideForm = function(e) {
        e.preventDefault();

        this.contentObj.classList.remove('comment__hide');
        this.editContentObj.classList.add('comment__hide');
    };


    var editComments = document.querySelectorAll('li[data-edit_comment]');
    var idxNodes = 0;
    var maxNodes = editComments.length;
    for (; idxNodes < maxNodes; idxNodes++) {
        new EditComment(editComments[idxNodes]);
    }
})();
(function(){
    'use strict';

    function Footer() {
        this.rootElem = document.getElementById('footer__links');
        if (!this.rootElem) {
            return
        }

        this.links = this.rootElem.querySelectorAll('a.footer__link');

        addEventLinks(this);
        addEventRootElemMouseLeave(this);
    }

    function addEventLinks(that) {
        for (var idx = 0; idx < that.links.length; idx++) {
            that.links[idx].addEventListener('mouseenter', function(e) {
                return function() {
                    mouseEnterLink.call(that, e);
                }
            }(that.links[idx]));

            that.links[idx].addEventListener('mouseleave', function(e) {
                return function() {
                    mouseLeaveLink.call(that, e);
                }
            }(that.links[idx]));
        }
    }

    function addEventRootElemMouseLeave(that) {
        that.rootElem.addEventListener('mouseleave', mouseLeaveRoot.bind(that));
    }

    function mouseEnterLink(e) {
        for (var idx = 0; idx < this.links.length; idx++) {
            this.links[idx].classList.add('footer__link--fade');
        }
        e.classList.remove('footer__link--fade');
    }

    function mouseLeaveLink(e) {
        e.classList.remove('footer__link--fade');
    }

    function mouseLeaveRoot() {
        for (var idx = 0; idx < this.links.length; idx++) {
            this.links[idx].classList.remove('footer__link--fade');
        }
    }

    new Footer();
})();
(function(){
    'use strict';

    function FormHelper() {}

    function getHTMLElement(elemID) {
        if (typeof elemID !== 'string') {
            return new TypeError('Invalid argument elemID, expect string, get ' + typeof elemID);
        }

        var elemObj = document.getElementById(elemID);
        if (!elemObj) {
            return new Error('DOM element ' + elemID + ' not found');
        }

        return elemObj;
    }

    function setInputCssClass(elemObj, cssClass) {
        elemObj.classList.remove('form__input--error', 'form__input--success');
        if (cssClass.length > 0) {
            elemObj.classList.add(cssClass);
        }
    }

    function setContainerCssClass(elemObj, cssClass) {
        if (!elemObj.hasAttribute('data-form-has-container')) {
            return;
        }

        elemObj.parentNode.classList.remove('form__container--error', 'form__container--success');
        if (cssClass.length > 0) {
            elemObj.parentNode.classList.add(cssClass);
        }
    }

    function setFeedbackCssClass(elemObj, cssClass) {
        var feedbacks = findFeedbacks(elemObj);
        var idxFeedbacks = 0;
        var maxFeedbacks = feedbacks.length;
        for (; idxFeedbacks < maxFeedbacks; idxFeedbacks++) {
            feedbacks[idxFeedbacks].classList.remove('form__feedback--error', 'form__feedback--loading', 'form__feedback--success');
            if (cssClass.length > 0) {
                feedbacks[idxFeedbacks].classList.add(cssClass);
            }
        }
    }

    function setFieldsetCssClass(elemObj, cssClass) {
        var fieldsetObj = findParentFieldset(elemObj);
        if (!fieldsetObj) {
            return;
        }

        fieldsetObj.classList.remove('form__fieldset--error', 'form__fieldset--success');
        if (cssClass.length > 0) {
            fieldsetObj.classList.add(cssClass);
        }
    }

    function findFeedbacks(elemObj) {
        var maxParentBouncing = 0;
        var currentObj = elemObj;
        var feedbacks = [];

        do {
            currentObj = currentObj.parentNode;
            if (currentObj.tagName === 'BODY' || currentObj.tagName === 'FORM') {
                break;
            }

            var idxChild = 0;
            var maxChild = currentObj.children.length;
            for(; idxChild < maxChild; idxChild++) {
                if (currentObj.children[idxChild].classList.contains('form__feedback')) {
                    feedbacks.push(currentObj.children[idxChild]);
                    break;
                }
            }

            if (currentObj.tagName === 'FIELDSET') {
                break;
            }

            maxParentBouncing++;
        } while(maxParentBouncing < 10);

        return feedbacks;
    }

    function findParentFieldset(elemObj) {
        var maxParentBouncing = 0;
        var fieldsetObj = elemObj;

        do {
            fieldsetObj = fieldsetObj.parentNode;
            if (fieldsetObj.tagName === 'BODY' || fieldsetObj.tagName === 'FORM') {
                return null;
            }

            if (fieldsetObj.classList.contains('form__fieldset')) {
                return fieldsetObj;
            } else {
                maxParentBouncing++;
            }
        } while(maxParentBouncing < 10);

        return null;
    }

    function getLabelErrorID(elemObj) {
        var inputID;
        var labelID;

        if (elemObj.getAttribute('type') !== 'radio') {
            inputID = elemObj.getAttribute('id');
            labelID = inputID.replace('-input-', '-label-');
        } else {
            inputID = elemObj.getAttribute('name');
            labelID = inputID.replace('-input-', '-label-');
        }

        return labelID + '-error';
    }

    function addLabelError(elemObj, errorMessage) {
        var labelErrorID = getLabelErrorID(elemObj);
        var labelObj = document.getElementById(labelErrorID);

        if (labelObj) {
            labelObj.textContent = errorMessage;
        } else {
            var label = document.createElement('label');
            var inputID = elemObj.getAttribute('id');
            var labelID = inputID.replace('-input-', '-label-');

            label.classList.add('form__label', 'form__label--error');
            label.setAttribute('for', elemObj.getAttribute('id'));
            label.setAttribute('id', labelErrorID);
            label.appendChild(document.createTextNode(errorMessage));

            if (elemObj.getAttribute('type') !== 'radio') {
                elemObj.setAttribute('aria-labelledby', labelID + ' ' + labelErrorID);
                if (elemObj.hasAttribute('data-form-has-container')) {
                    elemObj.parentNode.insertAdjacentElement('afterend', label);
                } else {
                    elemObj.insertAdjacentElement('afterend', label);
                }
            } else {
                label.removeAttribute('for');

                var fieldsetObj = findParentFieldset(elemObj);

                if (fieldsetObj) {
                    fieldsetObj.appendChild(label);
                }
            }
        }
    }

    function removeLabelError(elemObj) {
        var labelObj = document.getElementById(getLabelErrorID(elemObj));
        if (!labelObj) {
            return;
        }

        var labelID = labelObj.getAttribute('id').replace('-error', '');

        if (elemObj.getAttribute('type') !== 'radio') {
            elemObj.setAttribute('aria-labelledby', labelID);
        }

        labelObj.remove();
    }

    FormHelper.prototype.setFieldNeutral = function(elemID) {
        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return elemObj;
        }

        elemObj.setAttribute('aria-invalid', 'false');

        setInputCssClass(elemObj, '');
        setContainerCssClass(elemObj, '');
        setFeedbackCssClass(elemObj, '');
        setFieldsetCssClass(elemObj, '');

        removeLabelError(elemObj);
    };

    FormHelper.prototype.setFieldValid = function(elemID) {
        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return elemObj;
        }

        elemObj.setAttribute('aria-invalid', 'false');

        setInputCssClass(elemObj, 'form__input--success');
        setContainerCssClass(elemObj, 'form__container--success');
        setFeedbackCssClass(elemObj, 'form__feedback--success');
        setFieldsetCssClass(elemObj, 'form__fieldset--success');

        removeLabelError(elemObj);
    };

    FormHelper.prototype.setFieldInvalid = function(elemID, errorMessage) {
        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return elemObj;
        }

        if (typeof errorMessage !== 'string') {
            return new TypeError('Invalid argument errorMessage, expect string, get ' + typeof errorMessage);
        }

        if (errorMessage.length < 1) {
            return new Error('Argument errorMessage is empty');
        }

        elemObj.setAttribute('aria-invalid', 'true');

        setInputCssClass(elemObj, 'form__input--error');
        setContainerCssClass(elemObj, 'form__container--error');
        setFeedbackCssClass(elemObj, 'form__feedback--error');
        setFieldsetCssClass(elemObj, 'form__fieldset--error');

        addLabelError(elemObj, errorMessage);
    };

    FormHelper.prototype.setFieldLoading = function(elemID) {
        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return elemObj;
        }

        setInputCssClass(elemObj, '');
        setContainerCssClass(elemObj, '');
        setFeedbackCssClass(elemObj, 'form__feedback--loading');
        setFieldsetCssClass(elemObj, '');

        removeLabelError(elemObj);
    };

    FormHelper.prototype.removeGeneralError = function(elemID) {
        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return elemObj;
        }

        removeGeneralError(elemObj);
    };

    function removeGeneralError(elemObj) {
        var prevElem = elemObj.previousElementSibling;

        if (prevElem === null || prevElem.tagName !== 'DIV' || prevElem.getAttribute('role') !== 'alert') {
            return;
        }

        prevElem.remove();
    }

    FormHelper.prototype.setGeneralError = function(elemID, title, listErrors) {
        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return elemObj;
        }

        if (typeof title !== 'string') {
            return new TypeError('Invalid argument title, expect string, get ' + typeof title);
        }

        if (title.length < 1) {
            return new Error('Argument title is empty');
        }

        if (Object.prototype.toString.call(listErrors) !== '[object Array]') {
            return new TypeError('Invalid argument listErrors, expect Array, get ' + typeof listErrors);
        }

        var countErrors = listErrors.length;
        if (countErrors === 0) {
            return new Error('Argument listErrors is empty');
        }

        var err = checkErrorFormat(listErrors);
        if (err) {
            return err;
        }

        var generalInfo = createGeneralErrorDiv(elemID);
        var generalTitle = createGeneralErrorTitle(title);
        var listRoot = createGeneralErrorItems(listErrors);

        generalInfo.appendChild(generalTitle);
        generalInfo.appendChild(listRoot);

        removeGeneralError(elemObj);

        elemObj.insertAdjacentElement('beforebegin', generalInfo);
    };

    function checkErrorFormat(listErrors) {
        var idxError = 0;
        var maxError = listErrors.length;
        for(; idxError < maxError; idxError++) {
            if (!listErrors[idxError] || typeof listErrors[idxError] !== 'object') {
                return new Error('Invalid argument listErrors[' + idxError + '], expect Object');
            }

            if (!listErrors[idxError].hasOwnProperty('id') || typeof listErrors[idxError].id !== 'string') {
                return new Error('Invalid argument listErrors[' + idxError + '].id, expect string');
            }

            if (listErrors[idxError].id.length < 1) {
                return new Error('Invalid argument listErrors[' + idxError + '].id is empty');
            }

            if (!listErrors[idxError].hasOwnProperty('message') || typeof listErrors[idxError].message !== 'string') {
                return new Error('Invalid argument listErrors[' + idxError + '].message, expect string');
            }

            if (listErrors[idxError].message.length < 1) {
                return new Error('Invalid argument listErrors[' + idxError + '].message is empty');
            }

            if (listErrors[idxError].hasOwnProperty('more') && typeof listErrors[idxError].more !== 'string') {
                return new Error('Invalid argument listErrors[' + idxError + '].more, expect string');
            }
        }

        return null;
    }

    function createGeneralErrorDiv(elemID) {
        var generalInfo = document.createElement('div');
        generalInfo.setAttribute('role', 'alert');
        generalInfo.classList.add('block__info', 'block__info--error');
        generalInfo.setAttribute('id', elemID + '-error');

        return generalInfo;
    }

    function createGeneralErrorTitle(title) {
        var titleH4 = document.createElement('h4');
        titleH4.classList.add('block__title', 'block__title--small');
        titleH4.appendChild(document.createTextNode(title));

        return titleH4;
    }

    function createGeneralErrorItems(listErrors) {
        var listRoot = document.createElement('ul');
        listRoot.classList.add('block__list');

        var i = 0;
        var max = listErrors.length;
        for (;i < max; i++) {
            var listItem = document.createElement('li');
            listItem.classList.add('block__list-item');

            var listItemLink = document.createElement('a');
            listItemLink.classList.add('block__list-link');
            listItemLink.setAttribute('href', '#' + listErrors[i].id);
            listItemLink.appendChild(document.createTextNode(listErrors[i].message));

            listItem.appendChild(listItemLink);
            if (listErrors[i].more && listErrors[i].more.length > 0) {
                listItem.appendChild(document.createElement('br'));
                listItem.appendChild(document.createTextNode(listErrors[i].more));
            }

            listRoot.appendChild(listItem);
        }

        return listRoot;
    }

    FormHelper.prototype.tryFieldIsInvalid = function(elemID, rulesText, callback) {
        if (typeof callback !== 'function') {
            return new TypeError('Invalid argument callback, expect function, get ' + typeof callback);
        }

        var elemObj = getHTMLElement(elemID);
        if (!(elemObj instanceof HTMLElement)) {
            return callback(elemObj);
        }

        if (typeof rulesText !== 'string') {
            callback(new TypeError('Invalid argument rulesText, expect string, get ' + typeof rulesText));
            return;
        }

        var val = elemObj.value.trim();
        var rulesTextParts = rulesText.split('|');
        var fieldInspection = {
            'elemObj': elemObj,
            'val': val,
            'len': val.length,
            'idxOptionsTextParts': 0,
            'rulesTextParts': rulesTextParts,
            'maxOptionsTextParts': rulesTextParts.length
        };

        treatRulesLeft(fieldInspection, callback);
    };

    function treatRulesLeft(fieldInspection, callback) {
        if (fieldInspection.idxOptionsTextParts < fieldInspection.maxOptionsTextParts) {
            treatCurrentRule(fieldInspection, function(err) {
                if (err !== null) {
                    callback(err);
                } else {
                    fieldInspection.idxOptionsTextParts++;
                    treatRulesLeft(fieldInspection, callback);
                }
            });
        } else {
            callback(null);
        }
    }

    function treatCurrentRule(fieldInspection, callback) {
        var currentRule = fieldInspection.rulesTextParts[fieldInspection.idxOptionsTextParts].split(':');
        var rule = new Rule();
        if (!rule[currentRule[0]]) {
            callback(new Error('Invalid rule ' + currentRule[0]));
        } else {
            rule[currentRule[0]](currentRule, fieldInspection, callback);
        }
    }

    function Rule() {}

    Rule.prototype.required = function(rules, fieldInspection, callback) {
        if (fieldInspection.len === 0) {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.min = function(rules, fieldInspection, callback) {
        rules[1] = rules[1] >> 0;
        if (rules[1] === 0) {
            callback(new Error('Invalid parameter rule min, expect number above 0'));
            return;
        }

        if (fieldInspection.len < rules[1]) {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.max = function(rules, fieldInspection, callback) {
        rules[1] = rules[1] >> 0;
        if (rules[1] === 0) {
            callback(new Error('Invalid parameter rule max, expect number above 0'));
            return;
        }

        if (fieldInspection.len > rules[1]) {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.email = function(rules, fieldInspection, callback) {
        var posChar = fieldInspection.val.indexOf('@');
        if (posChar < 1 || posChar === (fieldInspection.len - 1)) {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.equal_field = function(rules, fieldInspection, callback) {
        var elem = document.getElementById(rules[1]);
        if (!elem) {
            callback(new Error('Invalid parameter rule equal_field, DOM element ' + rules[1] + ' not found'));
            return;
        }

        if (fieldInspection.val !== elem.value.trim()) {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.aria_invalid = function(rules, fieldInspection, callback) {
        if (fieldInspection.elemObj.getAttribute('aria-invalid') === 'true') {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.callback = function(rules, fieldInspection, callback) {
        if (!rules[1]) {
            callback(new Error('Invalid parameter rule callback, callback ' + rules[1] + ' not found'));
            return;
        }

        var fn = getFunction(rules[1]);
        if (!fn) {
            callback(new Error('Invalid parameter rule callback, callback ' + rules[1] + ' not found'));
            return;
        }

        var args = [fieldInspection.elemObj, function(success) {
            if (success === true) {
                callback(null);
            } else {
                callback('callback');
            }
        }];

        if (rules[2]) {
            var parts = rules[2].split(',');
            for (var i = 0; i < parts.length; i++) {
                args.push(parts[i]);
            }
        }

        !fn.apply(null, args);
    };

    Rule.prototype.regex = function(rules, fieldInspection, callback) {
        var pattern = rules.slice(1).join(':');
        if (!new RegExp(pattern).exec(fieldInspection.elemObj.value)) {
            callback(rules[0]);
        } else {
            callback(null);
        }
    };

    Rule.prototype.checked = function(rules, fieldInspection, callback) {
        if (fieldInspection.elemObj.getAttribute('type') === 'radio') {
            var name = fieldInspection.elemObj.getAttribute('name');
            if (!name || name.length < 1) {
                callback(new Error('Attribute name is missing for ' + fieldInspection.elemObj.getAttribute('id')));
                return;
            }

            var radios = document.getElementsByName(name);
            var isOneRadioChecked = false;
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    isOneRadioChecked = true;
                }
            }

            if (isOneRadioChecked) {
                callback(null);
            } else {
                callback(rules[0]);
            }
        } else {
            if (!fieldInspection.elemObj.checked) {
                callback(rules[0]);
            } else {
                callback(null);
            }
        }
    };

    function getFunction(fn) {
        var scope = window;
        var fnParts = fn.split('.');
        var idxScopes = 0;
        var maxFnParts = fnParts.length;
        for (; idxScopes < maxFnParts - 1; idxScopes++) {
            if (fnParts[idxScopes] === 'window') {
                continue
            }

            scope = scope[fnParts[idxScopes]];

            if (scope === undefined) {
                return;
            }
        }

        return scope[fnParts[fnParts.length - 1]];
    }

    function Form(formObj) {
        this.canSubmit = true;
        this.formHelper = new blueprintUE.www.FormHelper();
        this.listErrors = [];
        this.currentCallbacks = {};

        formObj.addEventListener('submit', this.onSubmit.bind(this), {passive: false});

        var idxNodes = 0;
        var maxNodes = formObj.length;
        for(; idxNodes < maxNodes; idxNodes++) {
            var type = formObj[idxNodes].getAttribute('type');
            if (type === 'checkbox' || type === 'radio' || type === 'file') {
                formObj[idxNodes].addEventListener('change', this.onChange.bind(this), {passive: false});
            } else {
                formObj[idxNodes].addEventListener('blur', this.onChange.bind(this), {passive: false});
            }
        }
    }

    function updateFakeFileLabel(elemObj) {
        if (elemObj.getAttribute('type') !== 'file') {
            return;
        }

        var text = elemObj.getAttribute('data-form-file-empty') || 'Choose file';
        if (elemObj.files.length > 0) {
            var filenameParts = [];
            for (var idxFiles = 0; idxFiles < elemObj.files.length; idxFiles++) {
                filenameParts.push(elemObj.files[idxFiles].name);
            }
            text = filenameParts.join(', ');
        }

        var idxChild = 0;
        var maxChild = elemObj.parentNode.children.length;
        for(; idxChild < maxChild; idxChild ++) {
            if (elemObj.parentNode.children[idxChild].classList.contains('form__fake-file-label')) {
                elemObj.parentNode.children[idxChild].textContent = text;
            }
        }
    }

    Form.prototype.onChange = function(e) {
        if (this.canSubmit === false) {
            e.preventDefault();
            return;
        }

        var now = Date.now();
        this.currentCallbacks[e.currentTarget.id] = now;

        updateFakeFileLabel(e.currentTarget);
        this.checkFieldState(e.currentTarget, false, now);
    };

    Form.prototype.onSubmit = function(e) {
        e.preventDefault();

        if (this.canSubmit === false) {
            return new Error('Form is already in submit processing');
        }

        for(var k in this.currentCallbacks) {
            this.currentCallbacks[k] = -1;
        }

        this.canSubmit = false;
        this.form = e.currentTarget;
        this.currentIdxNodes = 0;
        this.maxNodes = this.form.length;
        this.hasError = false;
        this.listErrors = [];

        this.checkFormFieldsState(this.canFinallySubmit.bind(this));
    };

    Form.prototype.checkFormFieldsState = function(callback) {
        if (this.currentIdxNodes < this.maxNodes) {
            this.checkFieldState(this.form[this.currentIdxNodes], true, null, (function() {
                this.currentIdxNodes++;
                this.checkFormFieldsState(callback);
            }).bind(this))
        } else {
            callback();
        }
    };

    Form.prototype.checkFieldState = function(elemObj, isSubmitEvent, now, callback) {
        var currentID = elemObj.getAttribute('id');
        var currentRules = elemObj.getAttribute('data-form-rules');

        if (currentID === null || currentRules === null) {
            if (callback) {
                callback(null);
            }

            return;
        }

        this.formHelper.setFieldLoading(currentID);
        this.formHelper.tryFieldIsInvalid(currentID, currentRules, (function(error) {
            if (now !== null && (this.currentCallbacks[currentID] && this.currentCallbacks[currentID] !== now)) {
                return;
            }

            var errorMessage = this.setFieldState(error, elemObj);

            if (isSubmitEvent && error !== null) {
                this.hasError = true;
                this.listErrors.push({
                    'id': currentID,
                    'message': errorMessage
                });
            }

            if (callback) {
                callback(error);
            }
        }).bind(this));
    };

    Form.prototype.setFieldState = function(error, elemObj) {
        var errorMessage;
        if (error !== null) {
            if (typeof error === 'string') {
                errorMessage = elemObj.getAttribute('data-form-error-' + error);
                if (!errorMessage || error.length === 0) {
                    errorMessage = 'Invalid field';
                }
            } else {
                errorMessage = 'Error: ' + error.message;
            }

            this.formHelper.setFieldInvalid(elemObj.getAttribute('id'), errorMessage);
        } else {
            this.formHelper.setFieldValid(elemObj.getAttribute('id'));
        }

        return errorMessage;
    };

    Form.prototype.canFinallySubmit = function() {
        if (!this.hasError) {
            if (this.callConfirmCallback()) {
                return;
            }

            if (this.showConfirm()) {
                return;
            }

            this.form.submit();
        } else {
            var generalErrorTitle = this.form.getAttribute('data-form-general-error');
            if (generalErrorTitle) {
                this.formHelper.setGeneralError(this.form.getAttribute('id'), generalErrorTitle, this.listErrors);
            } else {
                var speak = [];
                for (var i = 0; i < this.listErrors.length; i++) {
                    speak.push(this.listErrors[i].message);
                }
                var speakIntro = this.form.getAttribute('data-form-speak-error') || 'Form is invalid:';
                window.blueprintUE.www.srSpeak(speakIntro + ' ' + speak.join(', '));
            }
            this.canSubmit = true;
        }
    };

    Form.prototype.callConfirmCallback = function() {
        var confirmCallback = this.form.getAttribute('data-form-confirm-callback');
        if (!confirmCallback) {
            return false;
        }

        var fn = getFunction(confirmCallback);
        if (!fn) {
            return false;
        }

        fn.apply(null, [this.form, (function(hasConfirmed) {
            if(hasConfirmed) {
                this.form.submit();
            } else {
                this.canSubmit = true;
            }
        }).bind(this)]);

        return true;
    };

    Form.prototype.showConfirm = function() {
        if (!this.form.hasAttribute('data-form-confirm')) {
            return false;
        }

        var popinConfirmQuestion = document.getElementById('popin-confirm-h2');
        var popinConfirmContent = document.getElementById('popin-confirm-div-content');

        if (!popinConfirmQuestion || !popinConfirmContent) {
            return false;
        }

        var oldBtnYes = document.getElementById('popin-confirm-button-yes');
        if (oldBtnYes) {
            oldBtnYes.remove();
        }

        var oldBtnNo = document.getElementById('popin-confirm-button-no');
        if (oldBtnNo) {
            oldBtnNo.remove();
        }

        var btnYes = document.createElement('button');
        btnYes.setAttribute('class', 'form__button form__button--small');
        btnYes.setAttribute('id', 'popin-confirm-button-yes');
        var btnNo = document.createElement('button');
        btnNo.setAttribute('class', 'form__button form__button--secondary form__button--small');
        btnNo.setAttribute('id', 'popin-confirm-button-no');

        popinConfirmQuestion.textContent = this.form.getAttribute('data-form-confirm-question') || 'Do you confirm?';
        btnYes.appendChild(document.createTextNode(this.form.getAttribute('data-form-confirm-yes') || 'Yes'));
        btnNo.appendChild(document.createTextNode(this.form.getAttribute('data-form-confirm-no') || 'No'));

        popinConfirmContent.appendChild(btnYes);
        popinConfirmContent.appendChild(btnNo);

        btnYes.addEventListener('click', (this.confirmYes).bind(this));
        btnNo.addEventListener('click', (this.confirmNo).bind(this));

        this.previousHash = window.location.hash;
        window.location.hash = '#popin-confirm';

        return true;
    };

    Form.prototype.confirmYes = function() {
        this.form.submit();
    };

    Form.prototype.confirmNo = function() {
        this.canSubmit = true;
        window.location.hash = this.previousHash;
    };

    window.blueprintUE.www.FormHelper = FormHelper;

    var forms = document.querySelectorAll('form');
    var idxNodes = 0;
    var maxNodes = forms.length;
    for (; idxNodes < maxNodes; idxNodes++) {
        new Form(forms[idxNodes]);
    }
})();
(function(){
    'use strict';

    function Nav() {
        this.stateSearch = {
            timeout: undefined,
            inAction: false
        };

        this.stateToggleNavMenu = {
            timeout: undefined,
            expanded: false,
            inAction: false
        };

        this.delay = 500;

        var navToggle = document.getElementById('nav__toggle');
        if (navToggle) {
            navToggle.addEventListener('click', this.toggleNavMenu.bind(this));
        }

        var navSearchOpen = document.getElementById('nav__search-open');
        if (navSearchOpen) {
            navSearchOpen.addEventListener('click', this.showSearch.bind(this));
        }

        var navSearchClose = document.getElementById('nav__search-close');
        if (navSearchClose) {
            navSearchClose.addEventListener('click', this.hideSearch.bind(this));
        }
    }

    Nav.prototype.toggleNavMenu = function() {
        if (this.stateToggleNavMenu.inAction && this.stateToggleNavMenu.expanded) {
            clearTimeout(this.stateToggleNavMenu.timeout);
            this.stateToggleNavMenu.timeout = undefined;
        }

        this.stateToggleNavMenu.inAction = true;

        this.stateToggleNavMenu.expanded = document.querySelector('#nav__toggle .nav__toggle-inner').classList.contains('nav__toggle-inner--open');

        if (this.stateToggleNavMenu.expanded) {
            document.querySelector('#nav__container').classList.remove('nav__container--slide-down');
            document.querySelector('#nav__toggle .nav__toggle-inner').classList.remove('nav__toggle-inner--open');

            this.stateToggleNavMenu.timeout = setTimeout((function() {
                document.querySelector('#nav__center-side-container').classList.remove('nav__center-side-container--show');
                document.querySelector('#nav__right-side-container').classList.remove('nav__right-side-container--show');

                document.querySelector('#nav__toggle').setAttribute('aria-expanded', 'false');

                this.stateToggleNavMenu.expanded = false;
                this.stateToggleNavMenu.inAction = false;
            }).bind(this), this.delay);
        } else {
            document.querySelector('#nav__container').classList.add('nav__container--slide-down');
            document.querySelector('#nav__toggle .nav__toggle-inner').classList.add('nav__toggle-inner--open');
            document.querySelector('#nav__center-side-container').classList.add('nav__center-side-container--show');
            document.querySelector('#nav__right-side-container').classList.add('nav__right-side-container--show');

            document.querySelector('#nav__toggle').setAttribute('aria-expanded', 'true');

            this.stateToggleNavMenu.expanded = true;
            this.stateToggleNavMenu.inAction = false;
        }
    };

    Nav.prototype.showSearch = function()  {
        if (document.querySelector('#nav__toggle').getBoundingClientRect().width > 0) {
            return false;
        }

        if (this.stateSearch.inAction) {
            clearTimeout(this.stateSearch.timeout);
            this.stateSearch.timeout = undefined;
            this.stateSearch.inAction = false;
        }

        document.querySelector('#nav__center-side-container').classList.add('nav__center-side-container--hide');
        document.querySelector('#nav__right-side-container').classList.add('nav__right-side-container--search-open');
        document.querySelector('#nav__search-container').classList.add('nav__search-container--open');
        document.querySelector('#nav__search-close').classList.add('nav__search-close--show');
        document.querySelector('#nav__search-form').classList.add('nav__search-form--show');
    };

    Nav.prototype.hideSearch = function()  {
        if (this.stateSearch.inAction) {
            return false;
        }
        this.stateSearch.inAction = true;

        document.querySelector('#nav__search-container').classList.remove('nav__search-container--open');
        this.stateSearch.timeout = setTimeout((function() {
            document.querySelector('#nav__center-side-container').classList.remove('nav__center-side-container--hide');
            document.querySelector('#nav__right-side-container').classList.remove('nav__right-side-container--search-open');
            document.querySelector('#nav__search-form').classList.remove('nav__search-form--show');
            document.querySelector('#nav__search-close').classList.remove('nav__search-close--show');

            this.stateSearch.inAction = false;
            this.stateSearch.timeout = undefined;
        }).bind(this), this.delay);
    };

    new Nav();
})();
(function(){
    'use strict';

    function ProfileSocial(elemObj) {
        this.elemObj = elemObj;
        this.lastValue = elemObj.value;
        this.helpNodeText = null;
        this.currentFeedbackStatus = null;

        var elemID = elemObj.getAttribute('id');
        var helpID = elemID.replace('input', 'span') + '_help';
        var helpNode = document.getElementById(helpID);
        if (!helpNode) {
            return new Error('helpNode ' + helpID + ' is missing');
        }

        this.helpNodeText = helpNode.querySelector('.form__help--emphasis');
        if (!this.helpNodeText) {
            return new Error('.form__help--emphasis is missing');
        }

        try {
            this.pattern = new RegExp(elemObj.getAttribute('data-form-rules').substr(6));
        } catch(e) {
            return new Error('pattern ' + elemObj.getAttribute('data-form-rules').substr(6) + ' is incorrect');
        }

        this.fallback = elemObj.getAttribute('data-profile-social-fallback');
        if (!this.fallback) {
            return new Error('data-profile-social-fallback is missing');
        }
    }

    ProfileSocial.prototype.livecheck = function(e) {
        var value = this.elemObj.value;

        if (e !== null && this.lastValue === value) {
            return
        }
        this.lastValue = value;

        var event = new Event('blur');
        if (value.length === 0 || this.pattern.exec(value)) {
            this.helpNodeText.textContent = value || this.fallback;
            if (this.currentFeedbackStatus === false || this.currentFeedbackStatus === null) {
                this.elemObj.dispatchEvent(event);
                this.currentFeedbackStatus = true;
            }
        } else {
            this.helpNodeText.textContent = this.fallback;
            if (this.currentFeedbackStatus === true || this.currentFeedbackStatus === null) {
                this.elemObj.dispatchEvent(event);
                this.currentFeedbackStatus = false;
            }
        }
    };

    var inputsSocial = document.querySelectorAll('input[data-profile-social]');
    var idxNodes = 0;
    var maxNodes = inputsSocial.length;
    for(; idxNodes < maxNodes; idxNodes++) {
        var profileSocial = new ProfileSocial(inputsSocial[idxNodes]);
        if (profileSocial instanceof Error) {
            console.error('[ERROR][ProfileSocial] - ' + profileSocial);
            continue;
        }

        inputsSocial[idxNodes].addEventListener('keyup', profileSocial.livecheck.bind(profileSocial));
        inputsSocial[idxNodes].addEventListener('paste', profileSocial.livecheck.bind(profileSocial));
        inputsSocial[idxNodes].addEventListener('change', profileSocial.livecheck.bind(profileSocial));
        inputsSocial[idxNodes].addEventListener('blur', profileSocial.livecheck.bind(profileSocial));

        profileSocial.livecheck(null);
    }
})();
(function(){
    'use strict';

    function getHTMLElement(attributeName, elemID) {
        if (typeof elemID !== 'string') {
            return new TypeError('Invalid attribute ' + attributeName + ', expect string, get ' + typeof elemID);
        }

        var elemObj = document.getElementById(elemID);
        if (!elemObj) {
            return new Error('DOM element ' + elemID + ' not found');
        }

        return elemObj;
    }

    function Tag(tagDom) {
        var inputID = tagDom.getAttribute('data-tag-form-input-id') || null;
        this.inputObj = getHTMLElement('data-tag-form-input-id', inputID);
        if (!(this.inputObj instanceof HTMLElement)) {
            return this.inputObj;
        }

        var textareaID = tagDom.getAttribute('data-tag-form-textarea-id') || null;
        this.textareaObj = getHTMLElement('data-tag-form-textarea-id', textareaID);
        if (!(this.textareaObj instanceof HTMLElement)) {
            return this.textareaObj;
        }

        var listID = tagDom.getAttribute('data-tag-list-id') || null;
        this.listObj = getHTMLElement('data-tag-list-id', listID);
        if (!(this.listObj instanceof HTMLElement)) {
            return this.listObj;
        }

        var newID = tagDom.getAttribute('data-tag-new-id') || null;
        this.newObj = getHTMLElement('data-tag-new-id', newID);
        if (!(this.newObj instanceof HTMLElement)) {
            return this.newObj;
        }

        this.ariaLabel = tagDom.getAttribute('data-tag-aria-label') || 'Remove %s from the list';
        if (this.ariaLabel.indexOf('%s') === -1) {
            return new Error('Attribute data-tag-aria-label missing "%s"');
        }

        this.srSpeakAdd = tagDom.getAttribute('data-tag-srspeak-add') || '%s added';
        if (this.srSpeakAdd.indexOf('%s') === -1) {
            return new Error('Attribute data-tag-srspeak-add missing "%s"');
        }

        this.srSpeakDelete = tagDom.getAttribute('data-tag-srspeak-delete') || '%s deleted';
        if (this.srSpeakDelete.indexOf('%s') === -1) {
            return new Error('Attribute data-tag-srspeak-delete missing "%s"');
        }

        this.itemClass = tagDom.getAttribute('data-tag-item-class') || '';

        var keys = tagDom.getAttribute('data-tag-new-keys') || ',';
        this.keys = keys.split('|');

        this.regexKeys = null;
        var regexKeys = tagDom.getAttribute('data-tag-regex-keys') || '';
        if (regexKeys !== '') {
            this.regexKeys = new RegExp(regexKeys);
        }

        this.regexTag = null;
        var regexTag = tagDom.getAttribute('data-tag-regex-tag') || '';
        if (regexTag !== '') {
            this.regexTag = new RegExp(regexTag);
        }

        this.inputObj.addEventListener('keypress', this.tryToAddTagOnKeypress.bind(this));
        this.listObj.addEventListener('click', this.tryToDeleteTag.bind(this));
    }

    Tag.prototype.tryToAddTagOnKeypress = function(event) {
        var idxKeys = 0;
        var maxKeys = this.keys.length;
        for (; idxKeys < maxKeys; idxKeys++) {
            if (this.keys[idxKeys] === event.key) {
                var itemValue = event.target.value.trim();
                if (this.regexTag === null || this.regexTag.exec(itemValue)) {
                    this.add(itemValue);
                    event.target.value = '';
                    event.preventDefault();
                    break;
                }
            } else if (this.regexKeys !== null && !this.regexKeys.exec(event.key)) {
                event.preventDefault();
            }
        }
    };

    Tag.prototype.tryToDeleteTag = function(event) {
        event.preventDefault();

        var btn = event.target.closest('button');
        if (btn === null) {
            return;
        }

        this.remove(btn.parentElement, btn.textContent);
    };

    Tag.prototype.add = function(text) {
        var textNode = document.createTextNode(text);
        if (textNode.textContent === '') {
            return;
        }

        var li = document.createElement('li');
        li.classList.add('tag__item');

        var span = document.createElement('span');
        span.classList.add('sr-only');
        span.appendChild(document.createTextNode(text));

        var button = document.createElement('button');
        button.setAttribute('aria-label', this.ariaLabel.replace('%s', textNode.textContent));
        button.setAttribute('class', this.itemClass);
        button.appendChild(document.createTextNode(text));

        li.appendChild(span);
        li.appendChild(button);

        this.listObj.insertBefore(li, this.newObj);

        window.blueprintUE.www.srSpeak(this.srSpeakAdd.replace('%s', textNode.textContent));

        this.refreshTextarea();
    };

    Tag.prototype.remove = function(tagObj, text) {
        var textNode = document.createTextNode(text);

        tagObj.remove();

        window.blueprintUE.www.srSpeak(this.srSpeakDelete.replace('%s', textNode.textContent));

        this.refreshTextarea();
    };

    Tag.prototype.refreshTextarea = function() {
        var text = [];
        var tags = this.listObj.querySelectorAll('.sr-only');
        var idxTags = 0;
        var maxTags = tags.length;
        for(; idxTags < maxTags; idxTags++) {
            text.push(tags[idxTags].textContent);
        }
        this.textareaObj.value = text.join("\n");
    };


    var tagDom = document.querySelectorAll('div[data-tag]');
    var idxNodes = 0;
    var maxNodes = tagDom.length;
    for (; idxNodes < maxNodes; idxNodes++) {
        var tag = new Tag(tagDom[idxNodes]);
        if (tag instanceof Error) {
            console.error('[ERROR][Tag] - ' + tag);
        }
    }
})();
(function(){
    'use strict';

    function ThemeSwitcher() {
        this.buttons = document.querySelectorAll("[data-theme-switcher]");
        if (!this.buttons) {
            return
        }

        this.currentState = "light";
        this.eventClick = this.click.bind(this);

        this.addEventClick();
        this.currentState = this.readCurrentStateFromBrowser();
        this.setCurrentStateOnButtons();
        this.applyCurrentStateToPage();
    }

    ThemeSwitcher.prototype.click = function click() {
        var nextState = "light";
        if (this.currentState === "light") {
            nextState = "dark";
        }

        this.currentState = nextState;

        this.setCurrentStateOnButtons();
        this.applyCurrentStateToPage();
        this.saveCurrentStateInBrowser();
    };

    ThemeSwitcher.prototype.addEventClick = function addEventClick() {
        var idx = 0;
        var len = this.buttons.length;

        for (; idx < len; ++idx) {
            this.buttons[idx].addEventListener("click", this.eventClick);
        }
    };

    ThemeSwitcher.prototype.setCurrentStateOnButtons = function setCurrentStateOnButtons() {
        var idx = 0;
        var len = this.buttons.length;

        for (; idx < len; ++idx) {
            this.buttons[idx].querySelector("use").setAttribute("href", "/sprite/sprite.svg#icon-theme-" + this.currentState);
        }
    };

    ThemeSwitcher.prototype.applyCurrentStateToPage = function applyCurrentStateToPage() {
        document.querySelector("html").setAttribute("data-theme", this.currentState);
    };

    ThemeSwitcher.prototype.readCurrentStateFromBrowser = function readCurrentStateFromBrowser() {
        var value = localStorage.getItem('theme');
        if (value === "dark" || value === "light") {
            return value;
        }

        return "light";
    };

    ThemeSwitcher.prototype.saveCurrentStateInBrowser = function saveCurrentStateInBrowser() {
        localStorage.setItem('theme', this.currentState);
    };

    new ThemeSwitcher();
})();
(function(){
    'use strict';

    function callbackSave(that, name, xhr) {
        if (xhr && xhr.response !== '') {
            var response = JSON.parse(xhr.response);
            if (response['file_url'] !== undefined) {
                if (document.querySelector('#upload-current-avatar') !== null) {
                    updatePage(response.file_url, 'avatar');
                } else if (document.querySelector('#upload-current-thumbnail') !== null) {
                    updatePage(response.file_url, 'thumbnail');
                }
            }
        }

        that.cancel();
        window.location.hash = '#';
    }

    function updatePage(fileUrl, type) {
        var img = document.querySelector('#upload-current-' + type);
        var fallback = document.querySelector('#upload-fallback-' + type);
        var deleteButton = document.querySelector('#form-delete_' + type + '-submit');
        if (fallback) {
            fallback.classList.add('profile__avatar-container--hidden');
        }
        img.setAttribute('src', fileUrl);
        img.classList.remove('profile__avatar-container--hidden');
        deleteButton.classList.remove('form__button--hidden');

        var flashError = document.querySelector('[data-flash-error-for="form-delete_' + type + '"]');
        if (flashError) {
            flashError.remove();
        }

        var flashSuccess = document.querySelector('[data-flash-success-for="form-delete_' + type + '"]');
        if (flashSuccess) {
            flashSuccess.remove();
        }
    }

    function callbackInitZoom(that) {
        var styleTag = document.createElement('style');
        styleTag.setAttribute('id', 'style_gradient_' + that.inputZoomObj.getAttribute('id'));
        document.head.appendChild(styleTag);
    }

    function callbackUpdateZoom(that) {
        var gradValue = Math.round((that.inputZoomObj.value / that.inputZoomObj.getAttribute('max'))*100);
        var gradStyle = 'linear-gradient(90deg, #50e3c2 ' + gradValue + '%, #a4afc0 ' + (gradValue + 1) + '%)';
        var rangeSelector = 'input[id='+that.inputZoomObj.getAttribute('id')+']::';

        document.getElementById('style_gradient_' + that.inputZoomObj.getAttribute('id')).textContent = rangeSelector + '-webkit-slider-runnable-track' + '{background: ' + gradStyle + ';}';
    }

    window.blueprintUE.www.callbackSave = callbackSave;
    window.blueprintUE.www.callbackInitZoom = callbackInitZoom;
    window.blueprintUE.www.callbackUpdateZoom = callbackUpdateZoom;
})();
(function(){
    'use strict';

    function getHTMLElement(attributeName, elemID) {
        if (typeof elemID !== 'string') {
            return new TypeError('Invalid attribute ' + attributeName + ', expect string, get ' + typeof elemID);
        }

        var elemObj = document.getElementById(elemID);
        if (!elemObj) {
            return new Error('DOM element ' + elemID + ' not found');
        }

        return elemObj;
    }

    function getFunction(fn) {
        var scope = window;
        var fnParts = fn.split('.');
        var idxScopes = 0;
        var maxFnParts = fnParts.length;
        for (; idxScopes < maxFnParts - 1; idxScopes++) {
            if (fnParts[idxScopes] === 'window') {
                continue
            }

            scope = scope[fnParts[idxScopes]];

            if (scope === undefined) {
                return;
            }
        }

        return scope[fnParts[fnParts.length - 1]];
    }

    var ZoomModeCenter = 'center';
    var ZoomModePoint = 'point';

    function Uploader(masterDom) {
        this.initAttributes();

        var err = this.verifyMandatoryDataAttributes(masterDom);
        if (err !== null) {
            return err;
        }

        err = this.verifyOptionalDataAttributes(masterDom);
        if (err !== null) {
            return err;
        }

        this.initInputFile();
        this.initCanvas();

        this.initDivs();
        this.initMask();
        this.initZoom();
        this.initSave();
        this.initCancel();

        if (this.callbacks.init !== null) {
            this.callbacks.init(this, 'Uploader');
        }
    }

    Uploader.prototype.initAttributes = function() {
        this.img = null;
        this.imgSizeComputed = null;

        this.eventChangeInputFileListener = this.changeInputFile.bind(this);
        this.eventTreatImageListener = this.treatImage.bind(this);

        this.eventChangeInputZoomListener = this.changeInputZoomListener.bind(this);
        this.eventInputInputZoomListener = this.inputInputZoomListener.bind(this);
        this.zoomCurrent = 1;
        this._zoomEventHasNeverFired = null;
        this._zoomCurrentValue = null;
        this._zoomLastValue = null;

        this.eventSaveListener = this.save.bind(this);
        this.eventSaveOnLoad = this.saveOnLoad.bind(this);
        this.eventSaveOnError = this.saveOnError.bind(this);
        this.eventCancelListener = this.cancel.bind(this);

        this.ptTopLeftMask = {x: 0, y: 0};
        this.ptBottomRightMask = {x: 0, y: 0};
        this.mask = null;
        this.maskRaw = {color: null, size: null, radius: 0, constraint: true};

        this.inputFileObj = null;
        this.canvasObj = null;
        this.cssClassCanvasMoving = '';

        this.scaleFactor = 1.05;
        this.dragStart = null;

        this.divErrorObj = null;
        this.divUploadObj = null;
        this.divPreviewObj = null;
        this.inputZoomObj = null;
        this.btnSaveObj = null;
        this.btnCancelObj = null;

        this.callbacks = {
            init: null,
            zoom: {
                init: null,
                update: null,
            },
            image: {
                success: null,
                error: null,
            },
            save: {
                'update_form_data': null,
                success: null,
                error: null,
            },
            cancel: null,
            draw: null
        };

        this.errorLoadMessage = 'Could not load your image.\nUse png or jpg file.';
        this.errorUploadMessage = 'Could not upload your image.\nTry later.';

        this.canSave = true;

        this.reader = new FileReader();
        this.reader.addEventListener('load', this.eventTreatImageListener);

        this.eventTreatImageOnLoad = this.treatImageOnLoad.bind(this);
        this.eventTreatImageOnError = this.treatImageOnError.bind(this);

        this.inProgress = false;
    };

    Uploader.prototype.verifyMandatoryDataAttributes = function(masterDom) {
        var inputFileID = masterDom.getAttribute('data-uploader-input_file-id');
        this.inputFileObj = getHTMLElement('data-uploader-input_file-id', inputFileID);
        if (!(this.inputFileObj instanceof HTMLElement)) {
            return this.inputFileObj;
        }

        var canvasID = masterDom.getAttribute('data-uploader-canvas-id');
        this.canvasObj = getHTMLElement('data-uploader-canvas-id', canvasID);
        if (!(this.canvasObj instanceof HTMLElement)) {
            return this.canvasObj;
        }

        return null;
    };

    Uploader.prototype.verifyOptionalDataAttributes = function(masterDom) {
        var divErrorID = masterDom.getAttribute('data-uploader-div_error-id') || null;
        if (divErrorID !== null) {
            this.divErrorObj = getHTMLElement('data-uploader-div_error-id', divErrorID);
            if (!(this.divErrorObj instanceof HTMLElement)) {
                return this.divErrorObj;
            }
        }

        var divUploadID = masterDom.getAttribute('data-uploader-div_upload-id') || null;
        if (divUploadID !== null) {
            this.divUploadObj = getHTMLElement('data-uploader-div_upload-id', divUploadID);
            if (!(this.divUploadObj instanceof HTMLElement)) {
                return this.divUploadObj;
            }
        }

        var divPreviewID = masterDom.getAttribute('data-uploader-div_preview-id') || null;
        if (divPreviewID !== null) {
            this.divPreviewObj = getHTMLElement('data-uploader-div_preview-id', divPreviewID);
            if (!(this.divPreviewObj instanceof HTMLElement)) {
                return this.divPreviewObj;
            }
        }

        var maskSize = masterDom.getAttribute('data-uploader-mask-size');
        if (maskSize !== null) {
            var maskSizeWidth = 0;
            var maskSizeHeight = 0;
            if (maskSize.indexOf(',') === -1) {
                maskSizeWidth = maskSize >> 0;
                maskSizeHeight = maskSize >> 0;
            } else {
                var maskSizeParts = maskSize.split(',');
                maskSizeWidth = maskSizeParts[0] >> 0;
                maskSizeHeight = maskSizeParts[1] >> 0;
            }

            if (maskSizeWidth === 0 || maskSizeHeight === 0) {
                return new Error('Invalid attribute data-uploader-mask-size, expect size above 0, get width: ' + maskSizeWidth + ' height: ' + maskSizeHeight);
            }

            if (maskSizeWidth > this.canvasObj.width || maskSizeHeight > this.canvasObj.height) {
                return new Error('Invalid attribute data-uploader-mask-size, expect size below canvas size, get width: ' + maskSizeWidth + ' height: ' + maskSizeHeight);
            }

            this.maskRaw.size = {
                width: maskSizeWidth,
                height: maskSizeHeight
            };
        }

        var maskColor = masterDom.getAttribute('data-uploader-mask-color');
        if (maskColor === null) {
            this.maskRaw.color = 'rgba(255, 255, 255, 0.5)';
        } else if (this.maskRaw.size === null) {
            return new Error('Invalid attribute data-uploader-mask-color, you have to set data-uploader-mask-size first');
        } else {
            this.maskRaw.color = maskColor;
        }

        var maskRadius = masterDom.getAttribute('data-uploader-mask-radius');
        if (maskRadius !== null) {
            if (this.maskRaw.size === null) {
                return new Error('Invalid attribute data-uploader-mask-radius, you have to set data-uploader-mask-size first');
            }

            this.maskRaw.radius = maskRadius >> 0;
            if (this.maskRaw.radius > 0) {
                var minMaskSize = Math.min(this.maskRaw.size.width, this.maskRaw.size.height);
                if (this.maskRaw.radius > minMaskSize) {
                    this.maskRaw.radius = (minMaskSize / 2) >> 0;
                }
            }
        }

        var maskConstraint = masterDom.getAttribute('data-uploader-mask-constraint');
        if (maskConstraint !== null) {
            if (this.maskRaw.size === null) {
                return new Error('Invalid attribute data-uploader-mask-constraint, you have to set data-uploader-mask-size first');
            }

            if (maskConstraint !== 'true' && maskConstraint !== 'false') {
                return new Error('Invalid attribute data-uploader-mask-constraint, expect value "true" or "false", get ' + maskConstraint);
            }

            if (maskConstraint === 'false') {
                this.maskRaw.constraint = false;
            }
        }

        var inputZoomID = masterDom.getAttribute('data-uploader-input_zoom-id');
        if (inputZoomID !== null) {
            this.inputZoomObj = getHTMLElement('data-uploader-input_zoom-id', inputZoomID);
            if (!(this.inputZoomObj instanceof HTMLElement)) {
                return this.inputZoomObj;
            }
        }

        function parseCallbacks(instance, callbacks, parentKey) {
            var idxParentKey;
            var lenParentKey = parentKey.length;

            for (var key in callbacks) {
                if (callbacks.hasOwnProperty(key)) {
                    var localKey = [];
                    for(idxParentKey = 0; idxParentKey < lenParentKey; idxParentKey++){
                        localKey[idxParentKey] = parentKey;
                    }
                    localKey.push(key);

                    if (callbacks[key] !== null) {
                        var err = parseCallbacks(instance, callbacks[key], localKey);
                        if (err !== null) {
                            return err;
                        }
                    } else {
                        var callbackName = masterDom.getAttribute('data-uploader-callback-' + localKey.join('-')) || null;
                        if (callbackName !== null) {
                            var callbackFunction = getFunction(callbackName);
                            if (typeof callbackFunction === 'function') {
                                callbacks[key] = callbackFunction;
                            } else {
                                return new Error('Invalid function ' + callbackName + ' in data-uploader-callback-' + localKey.join('-'));
                            }
                        }
                    }
                }
            }

            return null;
        }

        var errorCallbacks = parseCallbacks(this, this.callbacks, []);
        if (errorCallbacks !== null) {
            return errorCallbacks;
        }

        var btnSaveID = masterDom.getAttribute('data-uploader-btn_save-id');
        if (btnSaveID !== null) {
            this.btnSaveObj = getHTMLElement('data-uploader-btn_save-id', btnSaveID);
            if (!(this.btnSaveObj instanceof HTMLElement)) {
                return this.btnSaveObj;
            }
        }

        this.uploadUrl = masterDom.getAttribute('data-uploader-upload-url') || window.location.toString();
        this.uploadName = masterDom.getAttribute('data-uploader-upload-name') || 'image';
        this.uploadPrefix = masterDom.getAttribute('data-uploader-upload-prefix') || '';

        this.extraUploadParams = [];
        var attrs = masterDom.getAttributeNames();
        var lenAttrs = attrs.length;
        for (var idxAttr = 0; idxAttr < lenAttrs; idxAttr++) {
            if (attrs[idxAttr].indexOf('data-uploader-upload-params-') === -1) {
                continue;
            }

            this.extraUploadParams.push({name: attrs[idxAttr].substring(28), value: masterDom.getAttribute(attrs[idxAttr])});
        }

        var btnCancelID = masterDom.getAttribute('data-uploader-btn_cancel-id');
        if (btnCancelID !== null) {
            this.btnCancelObj = getHTMLElement('data-uploader-btn_cancel-id', btnCancelID);
            if (!(this.btnCancelObj instanceof HTMLElement)) {
                return this.btnCancelObj;
            }
        }

        var errorLoadMessage = masterDom.getAttribute('data-uploader-error-load') || '';
        if (errorLoadMessage.length > 0) {
            this.errorLoadMessage = errorLoadMessage;
        }

        var errorUploadMessage = masterDom.getAttribute('data-uploader-error-upload') || '';
        if (errorUploadMessage.length > 0) {
            this.errorUploadMessage = errorUploadMessage;
        }

        var scaleFactor = masterDom.getAttribute('data-uploader-scale_factor') || 1.05;
        if (scaleFactor !== 1.05) {
            scaleFactor = parseFloat(scaleFactor);
            if (scaleFactor === 0 || Number.isNaN(scaleFactor)) {
                scaleFactor = 1.05;
            }
        }
        this.scaleFactor = scaleFactor;

        var cssClassCanvasMoving = masterDom.getAttribute('data-uploader-css-canvas_moving') || '';
        if (cssClassCanvasMoving !== '') {
            if (cssClassCanvasMoving.indexOf(' ') !== -1) {
                return new Error('Invalid css class "' + cssClassCanvasMoving + '" in data-uploader-css-canvas_moving, space is not allowed');
            }

            this.cssClassCanvasMoving = cssClassCanvasMoving;
        }

        return null;
    };

    Uploader.prototype.initInputFile = function() {
        this.inputFileObj.addEventListener('change', this.eventChangeInputFileListener);
    };

    Uploader.prototype.initCanvas = function() {
        this.lastX = this.canvasObj.width / 2;
        this.lastY = this.canvasObj.height / 2;

        this.canvasContext = this.canvasObj.getContext('2d');
        this.canvasContext.imageSmoothingEnabled = true;
        this.canvasContext.imageSmoothingQuality = 'high';

        trackTransforms(this.canvasContext);

        this.eventMouseDownListener = this.moveStart.bind(this);
        this.eventMouseMoveListener = this.moveMove.bind(this);
        this.eventMouseUpListener = this.moveEnd.bind(this);
        this.eventTouchStartListener = this.moveStart.bind(this);
        this.eventTouchMoveListener = this.moveMove.bind(this);
        this.eventTouchEndListener = this.moveEnd.bind(this);
        this.eventHandleScrollListener = this.handleScroll.bind(this);
    };

    Uploader.prototype.initDivs = function() {
        if (this.divPreviewObj !== null) {
            this.divPreviewObj.setAttribute('hidden', '');
        }

        if (this.divUploadObj !== null) {
            this.divUploadObj.removeAttribute('hidden');
        }

        this.hideError();
    };

    Uploader.prototype.initMask = function() {
        if (this.maskRaw.size === null) {
            return;
        }

        this.mask = {
            x: (this.canvasObj.width / 2) - (this.maskRaw.size.width / 2),
            y: (this.canvasObj.height / 2) - (this.maskRaw.size.height / 2),
            width: this.maskRaw.size.width,
            height: this.maskRaw.size.height,
            color: this.maskRaw.color,
            radius: this.maskRaw.radius,
            constraint: this.maskRaw.constraint
        };
    };

    Uploader.prototype.initZoom = function() {
        if (this.inputZoomObj !== null) {
            this.inputZoomObj.addEventListener('input', this.eventInputInputZoomListener);
            this.inputZoomObj.addEventListener('change', this.eventChangeInputZoomListener);
        }

        if (this.callbacks.zoom.init !== null) {
            this.callbacks.zoom.init(this, 'initZoom');
        }
    };

    Uploader.prototype.initSave = function() {
        if (this.btnSaveObj === null) {
            return;
        }

        this.btnSaveObj.addEventListener('click', this.eventSaveListener);
    };

    Uploader.prototype.initCancel = function() {
        if (this.btnCancelObj === null) {
            return;
        }

        this.btnCancelObj.addEventListener('click', this.eventCancelListener);
    };

    Uploader.prototype.changeInputFile = function() {
        if (this.inputFileObj.files.length < 1) {
            return;
        }

        this.reader.readAsDataURL(this.inputFileObj.files[0]);
    };

    Uploader.prototype.treatImage = function() {
        this.img = new Image();
        this.img.onload = this.eventTreatImageOnLoad;
        this.img.onerror = this.eventTreatImageOnError;
        this.img.src = this.reader.result;
    };

    Uploader.prototype.treatImageOnLoad = function() {
        if (this.img.width <= 0 || this.img.height <= 0) {
            this.treatImageOnError();
            return;
        }

        this.removeEventListeners();
        this.addEventListeners();

        this.zoomCurrent = 1;
        if (this.inputZoomObj) {
            this.inputZoomObj.value = 1;
        }

        this.canvasContext.setTransform(1,0,0,1,0,0);

        this.computeSize();

        this.draw();

        if (this.divUploadObj) {
            this.divUploadObj.setAttribute('hidden', '');
        }

        if (this.divPreviewObj) {
            this.divPreviewObj.removeAttribute('hidden');
        }

        this.hideError();

        if (this.callbacks.image.success !== null) {
            this.callbacks.image.success(this, 'treatImageOnLoad');
        }

        if (this.callbacks.zoom.update !== null) {
            this.callbacks.zoom.update(this, 'treatImageOnLoad');
        }
    };

    Uploader.prototype.treatImageOnError = function() {
        this.img = null;

        this.clearCanvas();

        this.showError(this.errorLoadMessage);

        if (this.callbacks.image.error !== null) {
            this.callbacks.image.error(this, 'treatImageOnError');
        }
    };

    Uploader.prototype.addEventListeners = function() {
        this.canvasObj.addEventListener('mousedown', this.eventMouseDownListener, {passive: false});
        window.addEventListener('mousemove', this.eventMouseMoveListener, {passive: false});
        window.addEventListener('mouseup', this.eventMouseUpListener, {passive: false});

        this.canvasObj.addEventListener('touchstart', this.eventTouchStartListener, {passive: false});
        this.canvasObj.addEventListener('touchmove', this.eventTouchMoveListener, {passive: false});
        this.canvasObj.addEventListener('touchend', this.eventTouchEndListener, {passive: false});

        this.canvasObj.addEventListener('DOMMouseScroll', this.eventHandleScrollListener, {passive: false});
        this.canvasObj.addEventListener('mousewheel', this.eventHandleScrollListener, {passive: false});
    };

    Uploader.prototype.removeEventListeners = function() {
        this.canvasObj.removeEventListener('mousedown', this.eventMouseDownListener);
        window.removeEventListener('mousemove', this.eventMouseMoveListener);
        window.removeEventListener('mouseup', this.eventMouseUpListener);

        this.canvasObj.removeEventListener('touchstart', this.eventTouchStartListener);
        this.canvasObj.removeEventListener('touchmove', this.eventTouchMoveListener);
        this.canvasObj.removeEventListener('touchend', this.eventTouchEndListener);

        this.canvasObj.removeEventListener('DOMMouseScroll', this.eventHandleScrollListener);
        this.canvasObj.removeEventListener('mousewheel', this.eventHandleScrollListener);
    };

    Uploader.prototype.cancel = function() {
        this.img = null;
        this.imgSizeComputed = null;
        this.zoomCurrent = 1;

        if (this.divPreviewObj !== null) {
            this.divPreviewObj.setAttribute('hidden', '');
        }

        if (this.divUploadObj !== null) {
            this.divUploadObj.removeAttribute('hidden');
        }

        this.hideError();

        this.clearCanvas();

        this.inputFileObj.value = null;

        this.removeEventListeners();

        if (this.callbacks.cancel !== null) {
            this.callbacks.cancel(this, 'cancel');
        }
    };

    Uploader.prototype.save = function() {
        if (this.img === null || this.canSave === false) {
            return;
        }

        this.canSave = false;

        var dataURL = this.getCanvasDataURL();
        var blob = dataURItoBlob(dataURL);
        var formData = new FormData();

        formData.append(this.uploadPrefix + this.uploadName, blob);
        formData.append(this.uploadPrefix + 'canvas_width', this.canvasObj.width);
        formData.append(this.uploadPrefix + 'canvas_height', this.canvasObj.height);

        if (this.mask !== null) {
            formData.append(this.uploadPrefix + 'mask_width', this.mask.width);
            formData.append(this.uploadPrefix + 'mask_height', this.mask.height);
            formData.append(this.uploadPrefix + 'mask_x', this.mask.x);
            formData.append(this.uploadPrefix + 'mask_y', this.mask.y);
        }

        var len = this.extraUploadParams.length;
        for(var i = 0; i < len; i++) {
            formData.append(this.extraUploadParams[i].name, this.extraUploadParams[i].value);
        }

        if (this.callbacks.save.update_form_data !== null) {
            formData = this.callbacks.save.update_form_data(this, 'save', formData);
        }

        var XHR = new XMLHttpRequest();
        XHR.addEventListener('load', this.eventSaveOnLoad);
        XHR.addEventListener('error', this.eventSaveOnError);
        XHR.open('POST', this.uploadUrl);
        XHR.send(formData);
    };

    Uploader.prototype.saveOnLoad = function(event) {
        if (event && event.currentTarget.status >= 400) {
            this.saveOnError(event.currentTarget.response);
            return;
        }

        this.canSave = true;

        this.hideError();

        if (this.callbacks.save.success !== null) {
            if (event) {
                this.callbacks.save.success(this, 'saveOnLoad', event.currentTarget);
            } else {
                this.callbacks.save.success(this, 'saveOnLoad');
            }
        }
    };

    Uploader.prototype.saveOnError = function(error) {
        this.canSave = true;

        this.showError(this.errorUploadMessage);

        if (this.callbacks.save.error !== null) {
            this.callbacks.save.error(this, 'saveOnError', error);
        }
    };

    function dataURItoBlob(dataURI) {
        var byteString;

        if (dataURI.split(',')[0].indexOf('base64') >= 0) {
            byteString = atob(dataURI.split(',')[1]);
        } else {
            byteString = decodeURI(dataURI.split(',')[1]);
        }

        var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

        var ia = new Uint8Array(byteString.length);
        var len = byteString.length;
        for (var i = 0; i < len; i++) {
            ia[i] = byteString.charCodeAt(i);
        }

        return new Blob([ia], {type: mimeString});
    }

    Uploader.prototype.computeSize = function() {
        var mask = {
            x: 0,
            y: 0,
            width: this.canvasObj.width,
            height: this.canvasObj.height
        };

        if (this.mask !== null) {
            mask.x = this.mask.x;
            mask.y = this.mask.y;
            mask.width = this.mask.width;
            mask.height = this.mask.height;
        }

        this.imgSizeComputed = {
            x: mask.x,
            y: mask.y,
            width: mask.width,
            height: mask.height
        };

        var ratio = Math.max(mask.width / this.img.width, mask.height / this.img.height);

        this.imgSizeComputed.height = this.img.height * ratio;
        this.imgSizeComputed.width = this.img.width * ratio;
        this.imgSizeComputed.x = mask.x - ((this.imgSizeComputed.width / 2) - (mask.width / 2));
        this.imgSizeComputed.y = mask.y - ((this.imgSizeComputed.height / 2) - (mask.height / 2));

        return this.imgSizeComputed;
    };

    Uploader.prototype.draw = function() {
        this.clearCanvas();

        this.drawImage();

        this.drawMask();

        if (this.mask !== null) {
            this.ptTopLeftMask = this.canvasContext.transformedPoint(this.mask.x, this.mask.y);
            this.ptBottomRightMask = this.canvasContext.transformedPoint(this.mask.x + this.mask.width, this.mask.y + this.mask.height);
        }

        if (this.callbacks.draw !== null) {
            this.callbacks.draw(this, 'draw');
        }
    };

    Uploader.prototype.clearCanvas = function() {
        var p1 = this.canvasContext.transformedPoint(0,0);
        var p2 = this.canvasContext.transformedPoint(this.canvasObj.width, this.canvasObj.height);
        this.canvasContext.clearRect(p1.x, p1.y, (p2.x-p1.x), (p2.y-p1.y));
    };

    Uploader.prototype.drawImage = function() {
        this.canvasContext.drawImage(this.img, this.imgSizeComputed.x, this.imgSizeComputed.y, this.imgSizeComputed.width, this.imgSizeComputed.height);
    };

    Uploader.prototype.drawMask = function() {
        if (this.mask === null) {
            return;
        }

        this.canvasContext.save();
        this.canvasContext.setTransform(1, 0, 0, 1, 0, 0);
        this.canvasContext.fillStyle = this.mask.color;
        this.canvasContext.beginPath();

        var x = this.mask.x;
        var y = this.mask.y;
        var width = this.mask.width;
        var height = this.mask.height;
        var radius = {
            topLeft: this.mask.radius,
            topRight: this.mask.radius,
            bottomRight: this.mask.radius,
            bottomLeft: this.mask.radius
        };

        this.canvasContext.moveTo(x + radius.topLeft, y);
        this.canvasContext.lineTo(x + width - radius.topRight, y);
        this.canvasContext.quadraticCurveTo(x + width, y, x + width, y + radius.topRight);
        this.canvasContext.lineTo(x + width, y + height - radius.bottomRight);
        this.canvasContext.quadraticCurveTo(x + width, y + height, x + width - radius.bottomRight, y + height);
        this.canvasContext.lineTo(x + radius.bottomLeft, y + height);
        this.canvasContext.quadraticCurveTo(x, y + height, x, y + height - radius.bottomLeft);
        this.canvasContext.lineTo(x, y + radius.topLeft);
        this.canvasContext.quadraticCurveTo(x, y, x + radius.topLeft, y);
        this.canvasContext.closePath();

        this.canvasContext.rect(this.canvasObj.width, 0, -this.canvasObj.width, this.canvasObj.height);
        this.canvasContext.fill();
        this.canvasContext.restore();
    };

    Uploader.prototype.getCanvasDataURL = function() {
        if (this.mask === null) {
            return this.canvasObj.toDataURL();
        }

        this.clearCanvas();

        this.drawImage();

        var dataURL = this.canvasObj.toDataURL();

        this.drawMask();

        return dataURL;
    };

    Uploader.prototype.moveStart = function(event) {
        pauseEvent(event);

        if (event.touches && event.touches.length > 0) {
            this.lastX = event.touches[0].pageX;
            this.lastY = event.touches[0].pageY;
        } else {
            this.lastX = event.pageX;
            this.lastY = event.pageY;
        }

        this.dragStart = {
            x: this.lastX,
            y: this.lastY
        };

        if (this.cssClassCanvasMoving !== '') {
            this.canvasObj.classList.add(this.cssClassCanvasMoving);
        }
    };

    Uploader.prototype.moveMove = function(event) {
        if (!this.dragStart) {
            return;
        }

        if (event.touches && event.touches.length > 0) {
            this.lastX = event.touches[0].pageX;
            this.lastY = event.touches[0].pageY;
        } else {
            this.lastX = event.pageX;
            this.lastY = event.pageY;
        }

        pauseEvent(event);

        if (this.lastX === this.dragStart.x && this.lastY === this.dragStart.y) {
            return;
        }

        var scale = this.canvasContext.getTransform().inverse().a;
        var translation = this.keepImgInsideMaskBoundings({
            x: (this.lastX - this.dragStart.x) * scale,
            y: (this.lastY - this.dragStart.y) * scale
        });

        if (translation.x !== 0 || translation.y !== 0) {
            this.canvasContext.translate(translation.x, translation.y);
            this.draw();
        }

        this.dragStart.x = this.lastX;
        this.dragStart.y = this.lastY;
    };

    Uploader.prototype.moveEnd = function() {
        this.dragStart = null;
        if (this.cssClassCanvasMoving !== '') {
            this.canvasObj.classList.remove(this.cssClassCanvasMoving);
        }

        var translation = this.keepImgInsideMaskBoundings({x: 0, y: 0});

        if (translation.x !== 0 || translation.y !== 0) {
            this.canvasContext.translate(translation.x, translation.y);
            this.draw();
        }
    };

    Uploader.prototype.keepImgInsideMaskBoundings = function(translation) {
        if (this.mask === null || this.mask.constraint === false) {
            return translation;
        }

        if (this.imgSizeComputed.x > this.ptTopLeftMask.x) {
            translation.x = this.ptTopLeftMask.x - this.imgSizeComputed.x;
        } else if (this.ptTopLeftMask.x === this.imgSizeComputed.x) {
            if (translation.x > 0) {
                translation.x = 0;
            }
        }

        if (this.imgSizeComputed.y > this.ptTopLeftMask.y) {
            translation.y = this.ptTopLeftMask.y - this.imgSizeComputed.y;
        } else if (this.ptTopLeftMask.y === this.imgSizeComputed.y) {
            if (translation.y > 0) {
                translation.y = 0;
            }
        }

        if (this.ptBottomRightMask.x > (this.imgSizeComputed.x + this.imgSizeComputed.width)) {
            translation.x = this.ptBottomRightMask.x - (this.imgSizeComputed.x + this.imgSizeComputed.width);
        } else if (this.ptBottomRightMask.x === (this.imgSizeComputed.x + this.imgSizeComputed.width)) {
            if (translation.x < 0) {
                translation.x = 0;
            }
        }

        if (this.ptBottomRightMask.y > (this.imgSizeComputed.y + this.imgSizeComputed.height)) {
            translation.y = this.ptBottomRightMask.y - (this.imgSizeComputed.y + this.imgSizeComputed.height);
        } else if (this.ptBottomRightMask.y === (this.imgSizeComputed.y + this.imgSizeComputed.height)) {
            if (translation.y < 0) {
                translation.y = 0;
            }
        }

        return translation;
    };

    function pauseEvent(event) {
        if (event.stopPropagation) {
            event.stopPropagation();
        }

        if (event.preventDefault) {
            event.preventDefault();
        }

        event.cancelBubble = true;
        event.returnValue = false;

        return false;
    }

    Uploader.prototype.updateZoomFromInput = function(event) {
        if (this.img === null) {
            return;
        }

        if (this.inProgress) {
            event.preventDefault();
            return false;
        }

        this.inProgress = true;

        var middleCanvasPoint = this.canvasContext.transformedPoint(this.canvasObj.width / 2, this.canvasObj.height / 2);
        var delta = this.zoomCurrent - event.target.value;
        var factor;
        if (delta > 0) {
            factor = Math.pow(this.scaleFactor, -1);
        } else {
            factor = Math.pow(this.scaleFactor, 1);
        }

        while(delta !== 0) {
            if (delta > 0) {
                if (this.zoomCurrent === 1) {
                    break;
                }

                this.zoomCurrent--;
                delta--;
            } else {
                this.zoomCurrent++;
                delta++;
            }

            this.canvasContext.translate(middleCanvasPoint.x, middleCanvasPoint.y);
            this.canvasContext.scale(factor, factor);
            this.canvasContext.translate(-middleCanvasPoint.x, -middleCanvasPoint.y);
        }

        var translation = this.keepImgInsideMaskBoundings({x: 0, y: 0});
        this.canvasContext.translate(translation.x, translation.y);
        this.draw();

        if (this.callbacks.zoom.update !== null) {
            this.callbacks.zoom.update(this, 'updateZoomFromInput');
        }

        this.inProgress = false;
    };

    Uploader.prototype.inputInputZoomListener = function(event) {
        this._zoomEventHasNeverFired = 1;
        this._zoomCurrentValue = event.target.value;
        if (this._zoomCurrentValue !== this._zoomLastValue) {
            this.updateZoomFromInput(event);
        }
        this._zoomLastValue = this._zoomCurrentValue;
    };

    Uploader.prototype.changeInputZoomListener = function(event) {
        if (!this._zoomEventHasNeverFired) {
            this.updateZoomFromInput(event);
        }
    };

    Uploader.prototype.zoomIn = function(zoomMode) {
        this.zoomCurrent++;
        this.zoom(1, zoomMode);
    };

    Uploader.prototype.zoomOut = function(zoomMode) {
        if (this.zoomCurrent === 1) {
            return;
        }

        this.zoomCurrent--;
        this.zoom(-1, zoomMode);
    };

    Uploader.prototype.zoom = function(exponent, zoomMode) {
        var pt;
        if (zoomMode === ZoomModeCenter) {
            pt = this.canvasContext.transformedPoint(this.canvasObj.width / 2, this.canvasObj.height / 2);
        } else {
            pt = this.canvasContext.transformedPoint(this.lastX, this.lastY);
        }

        this.canvasContext.translate(pt.x, pt.y);
        var factor = Math.pow(this.scaleFactor, exponent);
        this.canvasContext.scale(factor, factor);

        this.canvasContext.translate(-pt.x, -pt.y);

        var translation = this.keepImgInsideMaskBoundings({x: 0, y: 0});

        this.canvasContext.translate(translation.x, translation.y);
        this.draw();
    };

    Uploader.prototype.handleScroll = function(event) {
        var oldX = this.lastX;
        var oldY = this.lastY;

        this.lastX = event.offsetX || (event.pageX - this.canvasObj.offsetLeft);
        this.lastY = event.offsetY || (event.pageY - this.canvasObj.offsetTop);

        var wheelDirection = (event.detail < 0) ? 1 : (event.wheelDelta > 0) ? 1 : -1;

        if (wheelDirection === 1) {
            this.zoomIn(ZoomModePoint);
        } else if (wheelDirection === -1) {
            this.zoomOut(ZoomModePoint);
        }

        if (this.inputZoomObj !== null) {
            this.inputZoomObj.value = this.zoomCurrent;
        }

        if (this.callbacks.zoom.update !== null) {
            this.callbacks.zoom.update(this, 'handleScroll');
        }

        this.lastX = oldX;
        this.lastY = oldY;

        pauseEvent(event);

        return false;
    };

    Uploader.prototype.showError = function(message) {
        if (this.divErrorObj === null) {
            return;
        }

        while (this.divErrorObj.lastChild) {
            this.divErrorObj.lastChild.remove();
        }

        var parts = message.split("\n");
        var idxParts = 0;
        var maxParts = parts.length;
        for (; idxParts < maxParts; idxParts++) {
            this.divErrorObj.appendChild(document.createTextNode(parts[idxParts]));
            if (idxParts + 1 < maxParts) {
                this.divErrorObj.appendChild(document.createElement('br'));
            }
        }

        this.divErrorObj.removeAttribute('hidden');
    };

    Uploader.prototype.hideError = function() {
        if (this.divErrorObj === null) {
            return;
        }

        this.divErrorObj.setAttribute('hidden', '');

        while (this.divErrorObj.lastChild) {
            this.divErrorObj.lastChild.remove();
        }
    };

    function getMatrix() {
        if (typeof DOMMatrix === 'function') {
            return new DOMMatrix();
        }

        var svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
        return svg.createSVGMatrix();
    }

    function getPoint() {
        if (typeof DOMPoint === 'function') {
            return new DOMPoint();
        }

        var svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
        return svg.createSVGPoint();
    }

    function trackTransforms(ctx) {
        var xform = getMatrix();
        ctx.getTransform = function() {
            return xform;
        };

        var savedTransforms = [];
        var save = ctx.save;
        ctx.save = function() {
            savedTransforms.push(xform.translate(0,0));
            return save.call(ctx);
        };

        var restore = ctx.restore;
        ctx.restore = function() {
            xform = savedTransforms.pop();
            return restore.call(ctx);
        };

        var scale = ctx.scale;
        ctx.scale = function(sx, sy) {
            xform = xform.scale(sx, sy);
            return scale.call(ctx, sx, sy);
        };

        var rotate = ctx.rotate;
        ctx.rotate = function(radians) {
            xform = xform.rotate(radians*180/Math.PI);
            return rotate.call(ctx, radians);
        };

        var translate = ctx.translate;
        ctx.translate = function(dx, dy) {
            xform = xform.translate(dx, dy);
            return translate.call(ctx, dx, dy);
        };

        var transform = ctx.transform;
        ctx.transform = function(a, b, c, d, e, f) {
            var matrix2 = getMatrix();
            matrix2.a = a;
            matrix2.b = b;
            matrix2.c = c;
            matrix2.d = d;
            matrix2.e = e;
            matrix2.f = f;
            xform = xform.multiply(matrix2);
            return transform.call(ctx, a, b, c, d, e, f);
        };

        var setTransform = ctx.setTransform;
        ctx.setTransform = function(a, b, c, d, e, f) {
            xform.a = a;
            xform.b = b;
            xform.c = c;
            xform.d = d;
            xform.e = e;
            xform.f = f;
            return setTransform.call(ctx, a, b, c, d, e, f);
        };

        var pt = getPoint();
        ctx.transformedPoint = function(x,y) {
            pt.x = x;
            pt.y = y;
            return pt.matrixTransform(xform.inverse());
        };
    }

    var uploadersMasterDom = document.querySelectorAll('div[data-uploader]');
    var idxNodes = 0;
    var maxNodes = uploadersMasterDom.length;
    for (; idxNodes < maxNodes; idxNodes++) {
        var uploader = new Uploader(uploadersMasterDom[idxNodes]);
        if (uploader instanceof Error) {
            console.error('[ERROR][Uploader] - ' + uploader);
        }
    }
})();
(function(){
    'use strict';

    function VideoBlueprint(elemObj) {
        this.elemObj = elemObj;
        this.lastValue = elemObj.value;
        this.currentFeedbackStatus = null;
    }

    VideoBlueprint.prototype.livecheck = function(e) {
        var value = this.elemObj.value;

        if (e !== null && this.lastValue === value) {
            return
        }
        this.lastValue = value;
        var iframe = generateIframe(value);

        var event = new Event('blur');
        if (value.length === 0 || iframe) {
            if (this.currentFeedbackStatus === false || this.currentFeedbackStatus === null) {
                this.elemObj.setAttribute('aria-invalid', 'false');
                this.elemObj.dispatchEvent(event);
                removeIframe(this.elemObj);
                addIframe(this.elemObj, iframe);
            }
        } else {
            if (this.currentFeedbackStatus === true || this.currentFeedbackStatus === null) {
                this.elemObj.setAttribute('aria-invalid', 'true');
                this.elemObj.dispatchEvent(event);
                removeIframe(this.elemObj);
            }
        }
    };

    function generateIframe(url) {
        var matchers = [
            function matchYoutubeUrl(url) {
                var p = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\/?\?v=|watch\/?\?.+&v=))([A-Za-z0-9_-]{11})/i;
                return (url.match(p)) ? '//www.youtube.com/embed/' + RegExp.$1 : null;
            },
            function matchYoutubeNocookieUrl(url) {
                var p = /(?:https?:\/\/)?(?:www\.)?youtube-nocookie\.com\/embed\/([A-Za-z0-9_-]{11})/i;
                return (url.match(p)) ? '//www.youtube.com/embed/' + RegExp.$1 : null;
            },
            function matchVimeoUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|showcase\/(?:[^\/]+)\/video\/|video\/|)(\d+)/i;
                return url.match(regExp) ? '//player.vimeo.com/video/' + RegExp.$1 : null;
            },
            function matchDailymotionUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:api\.dailymotion\.com|www\.dailymotion\.com)\/(?:video|hub)\/([^#?&\/]+)?/i;
                return url.match(regExp) ? '//www.dailymotion.com/embed/video/' + RegExp.$1 : null;
            },
            function matchDailymotionShortUrl(url) {
                var regExp = /(?:https?:\/\/)?dai\.ly\/([^#?&\/]+)?/i;
                return url.match(regExp) ? '//www.dailymotion.com/embed/video/' + RegExp.$1 : null;
            },
            function matchDailymotionEmbedUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.dailymotion\.com)\/embed\/video\/([^#?&\/]+)?/i;
                return url.match(regExp) ? '//www.dailymotion.com/embed/video/' + RegExp.$1 : null;
            },
            function matchBilibiliUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.)?bilibili\.com\/video\/av(\d+)/i;
                return url.match(regExp) ? '//player.bilibili.com/player.html?aid=' + RegExp.$1 : null;
            },
            function matchBilibiliEmbedUrl(url) {
                var regExp = /(?:https?:\/\/)?player\.bilibili\.com\/player\.html\?aid=(\d+)/i;
                return url.match(regExp) ? '//player.bilibili.com/player.html?aid=' + RegExp.$1 : null;
            },
            function matchNiconicoUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.)?nicovideo\.jp\/watch\/sm(\d+)/i;
                return url.match(regExp) ? '//embed.nicovideo.jp/watch/sm' + RegExp.$1 : null;
            },
            function matchPeertubeUrl(url) {
                var regExp = /(?:https?:\/\/)?([^\/]+)\/videos\/(?:watch|embed)\/([a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12})(?:[^#?&\/]+)?/i;
                return url.match(regExp) ? '//' + RegExp.$1 + '/videos/embed/' + RegExp.$2 : null;
            },
        ];

        var idxMatchers = 0;
        var maxMatchers = matchers.length;
        var iframeUrl = null;
        for (; idxMatchers < maxMatchers; idxMatchers++) {
            iframeUrl = matchers[idxMatchers](url);
            if (iframeUrl !== null) {
                break;
            }
        }

        return iframeUrl;
    }

    function addIframe(elemObj, iframeURL) {
        if (iframeURL === null) {
            return;
        }

        var div = document.createElement('div');
        div.classList.add('blueprint__video');

        var iframe = document.createElement('iframe');
        iframe.setAttribute('src', iframeURL);
        iframe.setAttribute('allowfullscreen', "");
        iframe.classList.add('blueprint__iframe');

        div.appendChild(iframe);

        elemObj.parentNode.parentNode.appendChild(div);
    }

    function removeIframe(elemObj) {
        var iframe = elemObj.parentNode.parentNode.querySelector('div.blueprint__video');
        if (iframe !== null) {
            iframe.remove();
        }
    }

    var elemObj = document.getElementById('form-edit_informations-input-video');
    if (elemObj) {
        var videoBlueprint = new VideoBlueprint(elemObj);
        elemObj.addEventListener('keyup', videoBlueprint.livecheck.bind(videoBlueprint));
        elemObj.addEventListener('paste', videoBlueprint.livecheck.bind(videoBlueprint));
        elemObj.addEventListener('change', videoBlueprint.livecheck.bind(videoBlueprint));
        elemObj.addEventListener('blur', videoBlueprint.livecheck.bind(videoBlueprint));

        videoBlueprint.livecheck(null);
    }
})();
(function(){
    'use strict';

    function VideoIframe(elemObj) {
        var buttonID = elemObj.getAttribute('data-video-iframe-button-id') || null;
        var buttonObj = document.getElementById(buttonID);
        if (!buttonObj) {
            return new Error('DOM element ' + buttonID + ' not found given by data-video-iframe-button-id');
        }

        var videoURL = elemObj.getAttribute('data-video-iframe-url') || '';
        var url = generateIframe(videoURL);
        if (url === null) {
            return new Error('URL "'+videoURL+'" is invalid');
        }

        this.parentObj = elemObj;
        this.buttonObj = buttonObj;
        this.videoCss = elemObj.getAttribute('data-video-iframe-class') || '';
        this.loadingCss = elemObj.getAttribute('data-video-iframe-loading-class') || '';
        this.videoUrl = url;

        this.eventInsertIframe = this.insertIframe.bind(this);
        this.buttonObj.addEventListener('click', this.eventInsertIframe);
    }

    function generateIframe(url) {
        var matchers = [
            function matchYoutubeUrl(url) {
                var p = /(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\/?\?v=|watch\/?\?.+&v=))([A-Za-z0-9_-]{11})/i;
                return (url.match(p)) ? '//www.youtube.com/embed/' + RegExp.$1 : null;
            },
            function matchYoutubeNocookieUrl(url) {
                var p = /(?:https?:\/\/)?(?:www\.)?youtube-nocookie\.com\/embed\/([A-Za-z0-9_-]{11})/i;
                return (url.match(p)) ? '//www.youtube.com/embed/' + RegExp.$1 : null;
            },
            function matchVimeoUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|showcase\/(?:[^\/]+)\/video\/|video\/|)(\d+)/i;
                return url.match(regExp) ? '//player.vimeo.com/video/' + RegExp.$1 : null;
            },
            function matchDailymotionUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:api\.dailymotion\.com|www\.dailymotion\.com)\/(?:video|hub)\/([^#?&\/]+)?/i;
                return url.match(regExp) ? '//www.dailymotion.com/embed/video/' + RegExp.$1 : null;
            },
            function matchDailymotionShortUrl(url) {
                var regExp = /(?:https?:\/\/)?dai\.ly\/([^#?&\/]+)?/i;
                return url.match(regExp) ? '//www.dailymotion.com/embed/video/' + RegExp.$1 : null;
            },
            function matchDailymotionEmbedUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.dailymotion\.com)\/embed\/video\/([^#?&\/]+)?/i;
                return url.match(regExp) ? '//www.dailymotion.com/embed/video/' + RegExp.$1 : null;
            },
            function matchBilibiliUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.)?bilibili\.com\/video\/av(\d+)/i;
                return url.match(regExp) ? '//player.bilibili.com/player.html?aid=' + RegExp.$1 : null;
            },
            function matchBilibiliEmbedUrl(url) {
                var regExp = /(?:https?:\/\/)?player\.bilibili\.com\/player\.html\?aid=(\d+)/i;
                return url.match(regExp) ? '//player.bilibili.com/player.html?aid=' + RegExp.$1 : null;
            },
            function matchNiconicoUrl(url) {
                var regExp = /(?:https?:\/\/)?(?:www\.)?nicovideo\.jp\/watch\/sm(\d+)/i;
                return url.match(regExp) ? '//embed.nicovideo.jp/watch/sm' + RegExp.$1 : null;
            },
            function matchPeertubeUrl(url) {
                var regExp = /(?:https?:\/\/)?([^\/]+)\/videos\/(?:watch|embed)\/([a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12})(?:[^#?&\/]+)?/i;
                return url.match(regExp) ? '//' + RegExp.$1 + '/videos/embed/' + RegExp.$2 : null;
            },
        ];

        var idxMatchers = 0;
        var maxMatchers = matchers.length;
        var iframeUrl = null;
        for (; idxMatchers < maxMatchers; idxMatchers++) {
            iframeUrl = matchers[idxMatchers](url);
            if (iframeUrl !== null) {
                break;
            }
        }

        return iframeUrl;
    }

    VideoIframe.prototype.insertIframe = function() {
        if (this.loadingCss !== '') {
            this.parentObj.classList.add(this.loadingCss);
        }

        var iframe = document.createElement('iframe');
        if (this.videoCss !== '') {
            iframe.setAttribute('class', this.videoCss);
        }

        iframe.setAttribute('src', this.videoUrl);
        iframe.setAttribute('allowfullscreen', '');
        iframe.onload = (function() {
            if (this.loadingCss !== '') {
                this.parentObj.classList.remove(this.loadingCss);
            }
        }).bind(this);

        this.buttonObj.removeEventListener('click', this.eventInsertIframe);
        this.buttonObj = null;

        while (this.parentObj.lastChild) {
            this.parentObj.lastChild.remove();
        }

        this.parentObj.appendChild(iframe);
    };


    var videoIframes = document.querySelectorAll('div[data-video-iframe]');
    var idxNodes = 0;
    var maxNodes = videoIframes.length;
    for (; idxNodes < maxNodes; idxNodes++) {
        var videoIframe = new VideoIframe(videoIframes[idxNodes]);
        if (videoIframe instanceof Error) {
            console.error('[ERROR][VideoIframe] - ' + videoIframe);
        }
    }
})();
(function(){
    'use strict';

    function Expander(rootHTMLElement, handlerHTMLElement, minHeight) {
        if (!(rootHTMLElement instanceof HTMLElement)) {
            return new TypeError("Argument 'rootHTMLElement', expect HTMLElement, get " + typeof htmlElement);
        }

        if (!(handlerHTMLElement instanceof HTMLElement)) {
            return new TypeError("Argument 'handlerHTMLElement', expect HTMLElement, get " + typeof htmlElement);
        }

        minHeight = minHeight >> 0;
        if (minHeight === 0) {
            minHeight = 643;
        }

        this.dom = {
            root: rootHTMLElement,
            handler: handlerHTMLElement,
            render: null,
        };

        this.isHandlerDragging = false;

        this.bpHeight = null;
        this.minHeight = minHeight;

        this.eventsBinding = {
            mouseDown: this.eventMouseDown.bind(this),
            mouseMove: this.eventMouseMove.bind(this),
            mouseUp: this.eventMouseUp.bind(this)
        };
    }

    Expander.prototype.start = function start() {
        document.addEventListener("mousedown", this.eventsBinding.mouseDown);
        document.addEventListener("mousemove", this.eventsBinding.mouseMove);
        document.addEventListener("mouseup", this.eventsBinding.mouseUp);

        this.dom.render = this.dom.root.querySelector(".bue-render .frame");

        this.bpHeight = localStorage.getItem('bp-height') >> 0;
        if (this.bpHeight >= this.minHeight) {
            this.dom.root.style.height = this.bpHeight + "px";
            this.dom.render.style.height = this.bpHeight + "px";
        }

        this.startObserver();
    };

    Expander.prototype.eventMouseDown = function eventMouseDown(event) {
        if (event.target === this.dom.handler) {
            this.isHandlerDragging = true;
        }
    };

    Expander.prototype.eventMouseMove = function eventMouseMove(event) {
        if (!this.isHandlerDragging) {
            return false;
        }

        var pointerRelativeYpos = event.pageY - this.dom.root.offsetTop;

        if (pointerRelativeYpos < this.minHeight) {
            return;
        }

        this.dom.root.style.height = pointerRelativeYpos + "px";
        this.dom.render.style.height = pointerRelativeYpos + "px";

        this.bpHeight = pointerRelativeYpos;
    };

    Expander.prototype.eventMouseUp = function eventMouseUp() {
        if (this.isHandlerDragging && this.bpHeight !== null) {
            localStorage.setItem("bp-height", this.bpHeight);
        }

        this.isHandlerDragging = false;
    };

    Expander.prototype.startObserver = function startObserver() {
        var observer = new MutationObserver(function(mutationList){
            var idx = 0;
            var len = mutationList.length;
            var bpHeight = 0;

            for (;idx < len; ++idx) {
                if (mutationList[idx].type !== "attributes") {
                    continue;
                }

                if (mutationList[idx].target.classList.contains("frame-header__buttons-fullscreen") &&
                    !mutationList[idx].target.classList.contains("frame-header__buttons-fullscreen--exit")) {
                    bpHeight = localStorage.getItem('bp-height') >> 0;
                    if (bpHeight === 0) {
                        bpHeight = this.minHeight;
                    }

                    this.dom.root.style.height = bpHeight + "px";
                    this.dom.render.style.height = bpHeight + "px";

                    break;
                }
            }
        }.bind(this));

        observer.observe(this.dom.root.querySelector(".frame-header__buttons-fullscreen"), {
            attributes: true,
            childList: false,
            subtree: false
        });
    };

    // Freeze prototype for security issue about prototype pollution.
    Object.freeze(Expander.prototype);
    Object.freeze(Expander);

    window.blueprintUE.www.Expander = Expander;
})();
