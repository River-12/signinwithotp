define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'mage/storage',
    'mage/translate',
    'mage/mage',
    'jquery/ui'
], function ($, modal, customerData, storage, $t) {
    'use strict';

    $.widget('riverstone.customerAuthenticationPopup', {
        options: {
            login: '#riverstone-popup-login',
            nextRegister: '#riverstone-popup-registration',
            register: '#riverstone-popup-register',
            prevLogin: '#riverstone-popup-sign-in',
            otp: '#riverstone-popup-otp',
            loginOtp : '#mobile_opt'

        },
        _create: function () {
            var self = this,
                authentication_options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: this.options.popupTitle,
                    buttons: false,
                    modalClass : 'riverstone-popup'
                };

            modal(authentication_options, this.element);

            $('body').on('click', '.river-login-link, '+self.options.prevLogin, function() {
                $('.modal-title').css('display','none');
                $(self.options.register).modal('closeModal');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('openModal');
                return false;
            });

            $('body').on('click', self.options.loginOtp, function() {
                $('.modal-title').css('display','none');
                $(self.options.register).modal('closeModal');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('openModal');
                return false;
            });

            $('body').on('click', '.river-register-link, '+self.options.nextRegister, function() {
                $('.modal-title').css('display','block');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('closeModal');
                $(self.options.register).modal('openModal');
                return false;
            });

            this._ajaxSubmit();
        },

        _ajaxSubmit: function() {
            var self = this,
                form = this.element.find('form'),
                inputElement = form.find('input');

            inputElement.keyup(function (e) {
                self.element.find('.messages').html('');
            });

            form.submit(function (e) {
                if (form.validation('isValid')) {
                    if (form.hasClass('form-create-account')) {
                    } else {
                        var submitData = {},
                            formDataArray = $(e.target).serializeArray();
                        formDataArray.forEach(function (entry) {
                            submitData[entry.name] = entry.value;
                        });
                        $('body').loader().loader('show');
                            $.ajax({
                                url: $(e.target).attr('action'),
                                data: JSON.stringify(submitData),
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $('body').loader().loader('hide');
                                    if (response.errors) {
                                        $('.modal-header').css('display', 'block');
                                        $(".message").remove();
                                        $('<div class="message message-error error"><div>' + response.message +
                                            ' please try again later.</div></div>').appendTo('.otp_messages');
                                    } else {
                                        $(".message").remove();
                                        $('#riverstone-popup-login').modal('closeModal');
                                        $('<div class="message message-success success"><div>' + response.message +
                                            '</div></div>').appendTo('.otp_messages');
                                        var url = $('#riverstone-popup-login-form').
                                                        find('input[name="redirect_url"]').val();
                                        window.location.href = url;
                                    }
                                },
                                error: function () {
                                    $('body').loader().loader('hide');
                                }
                            });
                    }
                }
                return false;
            });
        },
        _displayMessages: function(className, message) {
            $('<div class="message '+className+'"><div>'+message+'</div></div>').appendTo(this.element.find('.messages'));
        },
        _showResponse: function(response) {
            var self = this,
                timeout = 800;
            this.element.find('.messages').html('');
            if (response.errors) {
                this._displayMessages('message-error error', response.message);
            } else {
                this._displayMessages('message-success success', response.message);
            }
            this.element.find('.messages .message').show();
            setTimeout(function() {
                if (!response.errors) {
                    self.element.modal('closeModal');
                }
            }, timeout);
        },

        _showFailingMessage: function() {
            this.element.find('.messages').html('');
            this._displayMessages('message-error error', $t('An error occurred, please try again later.'));
            this.element.find('.messages .message').show();
        }
    });

    return $.riverstone.customerAuthenticationPopup;
});
