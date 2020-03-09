$(function() {
    // Init variables 
    var step = 0;
    var maxStep = 8;

    // Click on icon upload 
    $('#upload-icon').click(function(e){
        $('#ice-file').trigger('click');
    });
    
    $('#previous-step').hide();
    
    // FUNCTIONS
    function emailIsValid(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    }

    function getTermsOfService($divStepElmt) {
        if(!$divStepElmt) return false;

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="./assets/img/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : '/auth/service-terms',
            type : 'GET',
            success : function(data) {
                content = data;
                if(content) $('div#nextAndPreviousStepButtons').fadeOut();
            },
            error: function() {
            },
            complete: function() {
                $divStepElmt.find('div.loader').remove();
                $divStepElmt.find('div.desc').html(content);
            }
        });
    }

    getPaymentSummary = function($divStepElmt) {
        if(!$divStepElmt) return false;

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="./assets/img/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : '/auth/payment-summary',
            type : 'GET',
            success : function(data) {
                content = data;
            },
            error: function() {
            },
            complete: function() {
                $divStepElmt.find('div.loader').remove();
                $divStepElmt.find('div.desc').html(content);
            }
        });
    }

    getPayment = function($divStepElmt) {
        if(!$divStepElmt) return false;

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="./assets/img/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : '/auth/go-to-payment',
            type : 'GET',
            success : function(data) {
                content = data;
                $('div#nextAndPreviousStepButtons').fadeOut();
            },
            error: function() {
            },
            complete: function() {
                $divStepElmt.find('div.loader').remove();
                $divStepElmt.find('div.desc').html(content);
            }
        });
    }

    // GENERAL STEP
    sendStepForm = function(form) {
        $('button#next-step').prop("disabled", true);

        var response;

        // Remove all error message 
        form.find('span.msg-info').text('');

        // Display loader
        form.closest('div.desc').append('<div class="loader text-center"><img src="./assets/img/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');
        
        // Hide the current form
        if( (typeof form.attr('data-stay-display') === typeof undefined) || (form.attr('data-stay-display') === false) ) {
            form.hide();
        }

        //Send Ajax request to save information 
        var formData = new FormData(form[0]);
        $.ajax({
            url : '/auth/send-step',
            type : 'POST',
            data : formData,
            cache: false,
            processData: false,
            contentType: false,
            success : function(data) {
                // Treat response
                response = data;
                if(response.status) {
                    $table = $(`<table id="step-${step}-rapport" class="table"></table>`);
                    $tbody = $(`<tbody></tbody>`);
                    // $dl = $(`<dl id="step-info-${step}"></dl>`);

                    $.each(response.items, function (title, content) {
                        if(title == 'files') {
                            $.each(response.items.files, function (i, file) {
                                $tbody.append( $(`<tr><th>${file.name}</th><td>${(file.ext == 'pdf') ? (`<object width="400" height="240" data="${file.completePath}"></object>`) : file.imageBlock }</td></tr>`) );
                            });
                        } else {
                            $tbody.append( $(`<tr><th>${title}</th><td>${content}</td></tr>`) );
                        }
                    });

                    $table.append($tbody);
                    form.closest('div.desc').append($table);

                    form.closest('div.desc').find('div.loader').remove();
                } else {
                    form.closest('div.desc').find('div.loader').remove();
                    form.fadeIn('slow');
                    // Display error message
                    $.each(response.items, function (spanID, errorContent) {
                        form.find(`span#${spanID}`).text(errorContent);
                    });
                }
            },
            complete: function() {
                // Show finish modal when it was last step 
                if(response.lastStep && response.status) {
                    // console.log('Now do something');
                    // window.location.href = '/auth/payment';
                } else {
                    if(response.status) {
                        $(`div#step-${step}`).find('div.step-pause').hide();
                        // var passedStep = `div#step-${step}`;

                        step ++;
                        var passedStep = `div#step-${step}`;
                        $(`div#step-${step}`).fadeIn('slow'); 
                        var body = $("html, body");
                        var paddingScroll = $(passedStep).height() * (30/100);
                        // console.log( paddingScroll );
                        body.stop().animate({scrollTop: ($(document).scrollTop() + $(passedStep).height() + 50) }, 1200, 'linear', function() { 
                        });

                        if( (typeof $(`div#step-${step}`).attr('data-summary') !== typeof undefined) && ($(`div#step-${step}`).attr('data-summary') !== false) ) {
                            getAndDisplaySummaryInformations( $(`div#step-${step}`) );
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-hidden-step') !== typeof undefined) && ($(`div#step-${step}`).attr('data-hidden-step') !== false) ) {
                            $('div#nextAndPreviousStepButtons').fadeOut();
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-terms-service') !== typeof undefined) && ($(`div#step-${step}`).attr('data-terms-service') !== false) ) {
                            getTermsOfService( $(`div#step-${step}`) );
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-payment-summary') !== typeof undefined) && ($(`div#step-${step}`).attr('data-payment-summary') !== false) ) {
                            getPaymentSummary( $(`div#step-${step}`) );
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-payment') !== typeof undefined) && ($(`div#step-${step}`).attr('data-payment') !== false) ) {
                            getPayment( $(`div#step-${step}`) );
                        }
                    }
                    showPreviousStepButton();
                }

                if(response.hideStep){ 
                    $('div#nextAndPreviousStepButtons').fadeOut();
                }

                $('button#next-step').prop("disabled", false);

            }
        });
    }

    nextStepAction = function() {
        if(step <= maxStep) {
            sendStepForm(  $(`form#step-form-${step}`) );
        }
    }

    previousStepAction = function() {
        if(step >= 1) {
            // Remove previous error message
            $('section#step-block-section').find(`form#step-form-${step}`).find('span.msg-info').text('');

            $(`div#step-${step}`).slideUp({ duration: 1600, easing: 'linear' });
            var body = $("html, body");
            var passedStep = `div#step-${step}`;
            var paddingScroll = $(passedStep).height() * (20/100);
            body.stop().animate({scrollTop: ($(document).scrollTop() - $(passedStep).height() - paddingScroll) }, 1200, 'linear', function() { 
            });

            $(`div#step-${step}`).find('div.loader').remove();
            $(`div#step-${step}`).find('form').show();

            step --;
            $('section#step-block-section').find(`form#step-form-${step}`).show();
            $('section#step-block-section').find(`form#step-form-${step}`).closest('div.desc').find(`table`).remove();

            if( (typeof $(`div#step-${step}`).attr('data-hidden-step') !== typeof undefined) && ($(`div#step-${step}`).attr('data-hidden-step') !== false) ) {
                $('div#nextAndPreviousStepButtons').fadeOut();
            }
        }
        showPreviousStepButton();
    }

    showPreviousStepButton = function () {
        if(step <= 0) {
            $('button#previous-step').hide();
        } else {
            $('button#previous-step').show();
            $(`div#step-${step}`).find('div.step-pause').show();
        }
    }

    getAndDisplaySummaryInformations = function($divStepElmt){
        if(!$divStepElmt) return false;

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="./assets/img/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : '/auth/summary-infos',
            type : 'GET',
            success : function(data) {
                content = data;
                if(content) $('div#nextAndPreviousStepButtons').fadeOut();
            },
            error: function() {

            },
            complete: function() {
                $divStepElmt.find('div.loader').remove();
                $divStepElmt.find('div.desc').html(content);
            }
        });
    }

    //HYDRATATION SELECTED FUNCTIONS
    /**
     * Get City Regions list 
     */
    getRegions = function(inputSelectID, inputSelectCities) {

        $.ajax({
            url : '/auth/regions',
            type : 'GET',
            success : function(data) {
                // Treat response
                response = data;
                if(inputSelectID) {
                    $(inputSelectID).html('');
                    $.each(response.data, function (i, region) {
                        $(inputSelectID).append(`<option value="${region.id}">${region.name}</option>`);
                    });
                }
            },
            complete: function() {
                $(inputSelectID).on('change', function(e){
                    getCities( $(inputSelectID).val(), inputSelectCities );
                });
                $(inputSelectID).trigger('change');
            }
        });
    }

    /**
     * Get cities list 
     */
    getCities = function(regionID, inputSelectID) {
        regionID = parseInt(regionID);

        $.ajax({
            url : '/auth/cities-region',
            type : 'GET',
            data: {
                regionID: regionID
            },
            success : function(data) {
                // Treat response
                response = data;
                if(inputSelectID) {
                    $(inputSelectID).html('');
                    $.each(response.data, function (i, city) {
                        $(inputSelectID).append(`<option value="${city.id}">${city.name}</option>`);
                    });
                }
            },
        });
    }

    /**
     * Get Activities area list 
     */
    getActivityArea = function(inputSelectID, inputSelectBranchID, inputSelectSubBranchID) {
        $.ajax({
            url : '/auth/sectors',
            type : 'GET',
            success : function(data) {
                // Treat response
                response = data;
                if(inputSelectID) {
                    $(inputSelectID).html('');
                    $.each(response.data, function (i, sector) {
                        $(inputSelectID).append(`<option value="${sector.id}">${sector.name}</option>`);
                    });
                }
            },
            complete: function() {
                $(inputSelectID).on('change', function(e){
                    getBranch( $(inputSelectID).val(), inputSelectBranchID, inputSelectSubBranchID );
                });
                $(inputSelectID).trigger('change');
            }
        });
    }

    /**
     * Get and set branch
     */
    getBranch = function(activityAreaID, inputSelectBranchID, inputSelectSubBranchID) {
        activityAreaID = parseInt(activityAreaID);

        $.ajax({
            url : '/auth/branches',
            type : 'GET',
            data: {
                activityAreaID: activityAreaID
            },
            success : function(data) {
                // Treat response
                response = data;
                if(inputSelectBranchID) {
                    $(inputSelectBranchID).html('');
                    $.each(response.data, function (i, branch) {
                        $(inputSelectBranchID).append(`<option value="${branch.id}">${branch.name}</option>`);
                    });
                }
            },
            complete: function() {
                $(inputSelectBranchID).on('change', function(e){
                    getSubBranch( $(inputSelectBranchID).val(), inputSelectSubBranchID );
                });
                $(inputSelectBranchID).trigger('change');
            }
        });
    }

    /**
     * Get City Regions list 
     */
    getSubBranch = function(branchID, inputSelectSubBranchID) {
        branchID = parseInt(branchID);

        $.ajax({
            url : '/auth/sub-branches',
            type : 'GET',
            data: {
                branchID: branchID
            },
            success : function(data) {
                // Treat response
                response = data;
                if(inputSelectSubBranchID) {
                    $(inputSelectSubBranchID).html('');
                    $.each(response.data, function (i, subBranch) {
                        $(inputSelectSubBranchID).append(`<option value="${subBranch.id}">${subBranch.name}</option>`);
                    });
                }
            },
        });
    }

    // EVENTS LIST
    // Next step button
    $('button#next-step').click(function(e){
        e.preventDefault();
        nextStepAction();
    });

    // Previous step button
    $('button#previous-step').click(function(e){
        e.preventDefault();
        previousStepAction();
    });

    // To forced numeric input
    $('input#phoneSubscribe, input#ice, div#step-1 input#verificationCode').on("input", function() {
        var dInput = this.value;
        $(this).val( this.value.replace(/\D/g,'') );
    });

    // ICE
    /**
     *
     */
    $('input#ice').on("input", function() {
        var iceInput = this.value;
        var $input = $(this);

        $('div.ice-rapport').remove();

        if( iceInput.length == 15 ) {
            $input.removeClass('checked').addClass('loading');
           $.ajax({
            url : '/get-ice',
            type : 'POST',
            data: {ice: iceInput},
            success : function(data) {
                $input.removeClass('loading');
                if(data.success) {
                    $input.addClass('checked');
                    
                    $div = $(`<div class="ice-rapport"><h5 class="text-info">Informations de l’entreprise</h5></div>`);
                    $table = $(`<table class="table"></table>`);
                    $tbody = $(`<tbody></tbody>`);

                    $.each(data.iceResponse, function (title, content) {
                        $tbody.append( $(`<tr><th>${content.name}</th><td>${content.value}</td></tr>`) );
                    });

                    $table.append($tbody);
                    $div.append($table);
                    $input.closest('div.desc').append($div);
                } else {
                    $input.addClass('not-checked');
                }
            },
            complete: function() {

            }
        });

           // Send request to get ICE information
           // use complete of the ajax request to remove spinner
           // Put check icon if information ICE is good
        } else {
           $(this).removeClass('loading checked not-checked');
        }
    });

    // PHONE NUMBER
    /**
     * Event - Detected the entry of a keyboard key - for input phone number
     * Create to display or remove a button to send the verification code by SMS for activate phone number
     */
    $('input#phoneSubscribe').on("input", function() {
        var phoneInput = this.value;

        // Force format number phone
        if( phoneInput.length == 1 && phoneInput != '0') {
            $('input#phoneSubscribe').val(`0${phoneInput}`);
        }

        if( phoneInput.length >= 10 ) {
           $(this).parents('div.form-row').append( $('<div class="col-1" data-toggle="tooltip" data-placement="bottom" title="Envoyer le code de vérification"><a id="checkPhoneNumber" class="btn bg-primary-red text-light" href="#" role="button"><i class="icofont icofont-ui-messaging fa-lg"></i></a></div>') );
        } else if (phoneInput.length < 10) {
            $(this).parents('div.form-row').find('div.col-1').remove();
        }
    });

    /** 
     * Detecte if input phone verification code has a good length
     * to create and display a button that will verify the code entered is valid
     */
    $('body').on('input', 'div#step-1 input#verificationCode', function(e) {
        var input = this.value;
        if( input.length >= 4 ) {
            $('input#phoneSubscribe').parents('div.form-row').append( $('<div class="col-1 send-phone-code" data-toggle="tooltip" data-placement="bottom" title="Vérifier le numéro"><a id="sendPhoneNumberCode" class="btn bg-primary-red text-light" href="#" role="button"><i class="icofont icofont-check-circled fa-lg"></i></a></div>') );

            // $('body').find('div#edit-phone-block').hide();
        } else if (input.length < 4) {
            $(this).parents('div.form-row').find('div.col-1.send-phone-code').remove();
            // $('body').find('div#edit-phone-block').show();
        }
    });

    /** 
     * Event when user clic to send code by SMS
     * Generate code and send SMS
     */
    $('body').on('click', 'div#step-1 a#checkPhoneNumber', function(e) {
        e.preventDefault();
        $('input#phoneSubscribe').attr("disabled", true);
        var $button = $(this);
        // Display loader here
        // $button.hide();
        // $button.closest('div.column.one-fourth').append('<div class="loader" style="text-align: center; display: flex;"><img src="./assets/img/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 2em;" /></div>');

        // Remove old status message
        $('span#phoneSubscribeError').text('');
        $('span#verificationCodeError').text('');

        // var phoneNumberReg = /([0-9]{10})|(\([0-9]{3}\)\s+[0-9]{3}\-[0-9]{4})/;
        var phoneNumberReg = /(\+212|0)([ \-_/]*)(\d[ \-_/]*){9}/g;
        var phoneNumber = $('input#phoneSubscribe').val();
        phoneNumber = phoneNumber.replace(/\D/g,'');

        // Detect if phone number is valid and execute this script
        if (phoneNumberReg.test( phoneNumber )) {
            $checkButton = $('input#phoneSubscribe').parents('div.form-row').find('div.col-1');
            $checkButton.find('a').removeClass('bg-primary-red');

            // $checkButton.find('a i.icofont-ui-messaging').removeClass('icofont-ui-messaging').addClass('fa fa-spinner fa-spin');
            $checkButton.find('a#checkPhoneNumber i.icofont-ui-messaging').remove();
            $checkButton.find('a#checkPhoneNumber').append( $('<img class="img-fluid w-100" src="./assets/img/loader/symbol-check-info.svg" />') );

            $.ajax({
                url : '/verification/phone-number',
                type : 'POST',
                data: {
                    phone: phoneNumber
                },
                success : function(data) {
                    //  If code has been send with success
                    if(data.success) {
                        // remove loader at the end
                        $checkButton.remove();
                        // Block to the edit phone number button
                        $('input#phoneSubscribe').parents('div.form-row').append( $('<div id="edit-phone-block" class="col-1" data-toggle="tooltip" data-placement="bottom" title="Modifier le numéro de téléphone"><a id="editPhoneNumber" class="btn bg-primary-red text-light" href="#" role="button"><i class="icofont icofont-edit-alt fa-lg"></i></a></div>') );
                        // Block to the input code phone number
                        $('input#phoneSubscribe').parents('div.form-row').append( $('<div class="col-2"><input class="form-control" id="verificationCode" maxlength="4" name="verificationCode" type="text" placeholder="Code" autocomplete="off"></div>') );
                        $.notify(data.message, "success");
                    } else {
                        $('span#phoneSubscribeError').text('Une erreur s\'est produite, rééssayez plus tard !');
                        $button.show();
                        $.notify(data.message, "error");
                    }
                }
            });
        } else {
            // phone number is incorrect stop the script and display error
            $('span#phoneSubscribeError').text('Veuillez renseigner un numéro de téléphone valide');
            // remove loader at the end
            $button.show();
            $('input#phoneSubscribe').val('');
            $('input#phoneSubscribe').parents('div.form-row').find('div.col-1').remove();
        }
    });

    /** 
     * Event - Click on button to edit phone number
     * Reset state of the input phone number
     */
    $('body').on('click', 'div#step-1 a#editPhoneNumber', function(e) {
        e.preventDefault();
        // Remove error message phone number
        $('body').find('span#phoneSubscribeError').text('');
        // Remove input phone number code
        $('body').find('input#verificationCode').closest('div.col-2').remove();
        // Remove button to send SMS
        $('body').find('a#checkPhoneNumber').closest('div.col-1').remove();
        // Remove button to check if code match
        $('body').find('a#sendPhoneNumberCode').closest('div.col-1.send-phone-code').remove();
        // Remove button to edit phone number
        $('body').find('div#edit-phone-block').remove();
        // Remove disabled attribute for phone number input
        $('input#phoneSubscribe').attr("disabled", false).trigger('input').focus();
        // Focus on phone number input
    });

    /** 
     * Event - Click on button to edit phone number
     * Reset state of the input phone number
     */
    $('body').on('click', 'div#step-1 a#sendPhoneNumberCode', function(e) {
        e.preventDefault();
        console.log('Send code phone number input to check if code match');

        $('span#phoneSubscribeError').text('');
        $('span#verificationCodeError').text('');
        var $button = $(this);

        var $phone = $('input#phoneSubscribe');
        var $verificationCode = $('input#verificationCode');

        if($phone.val() != '' && $verificationCode.val() != '') {
            $.ajax({
                url : '/verification/check-phone-number',
                type : 'POST',
                data: {
                    action: 'phoneNumberVerification',
                    phoneNumber: $phone.val(),
                    code: $verificationCode.val()
                },
                success : function(data) {
                    data = data;
                    console.log(data);
                    if(data.success) {
                        console.log(data.message);
                        $button.closest('div.form-row').children('div').not('.col.form-group').remove();
                        // Add indicator to the number and code status
                        $.notify(data.message, 'success');
                    } else {
                        console.log(data.message);
                        $('span#phoneSubscribeError').text('Le code de vérification est erroné');
                        // $('a#editPhoneNumber').closest('div.column.one-fourth').fadeIn();
                        $.notify(data.message, 'error');
                    }
                }
            });
        } else {
            $('span#verificationCodeError').text('Veuillez saisir le bon code de vérification');
        }
    });

    // ADDRESS EMAIL
    /**
     * Event - Detected the entry of a keyboard key - for input email
     * Create to display or remove a button to send the verification code by email for activate email address
     */
    $('input#emailSubscribe').on("input", function() {
        var email = this.value;

        if (emailIsValid(email)) {
            if( $('body').find('div#step-1 a#checkEmail').length <= 0){
                $(this).parents('div.form-row').append( $('<div class="col-1" data-toggle="tooltip" data-placement="bottom" title="Envoyer le code de vérification"><a id="checkEmail" class="btn bg-primary-red text-light" href="#" role="button"><i class="icofont icofont-send-mail fa-lg"></i></a></div>') );
            }
        } else {
            $(this).parents('div.form-row').find('div.col-1').remove();
        }
    });

    /** 
     * Detecte if input email verification code has a good length
     * to create and display a button that will verify the code entered is valid
     */
    $('body').on('input', 'div#step-1 input#verificationEmailCode', function(e) {
        var input = this.value;
        if( input.length >= 4 ) {
            $('input#emailSubscribe').parents('div.form-row').append( $('<div class="col-1 send-email-code" data-toggle="tooltip" data-placement="bottom" title="Vérifier le numéro"><a id="sendEmailCode" class="btn bg-primary-red text-light" href="#" role="button"><i class="icofont icofont-check-circled fa-lg"></i></a></div>') );

        } else if (input.length < 4) {
            $(this).parents('div.form-row').find('div.col-1.send-email-code').remove();
        }
    });

    /** 
     * Event when user clic to send code by SMS
     * Generate code and send SMS
     */
    $('body').on('click', 'div#step-1 a#checkEmail', function(e) {
        e.preventDefault();
        $('input#emailSubscribe').attr("disabled", true);
        var $button = $(this);

        // Remove old status message
        $('span#emailError').text('');
        // $('span#verificationCodeError').text('');

        var email = $('input#emailSubscribe').val();

        // Detect if email is valid and execute this script
        if (emailIsValid(email)) {
            $checkButton = $('input#emailSubscribe').parents('div.form-row').find('div.col-1');
            $checkButton.find('a').removeClass('bg-primary-red');

            // $checkButton.find('a i.icofont-ui-messaging').removeClass('icofont-ui-messaging').addClass('fa fa-spinner fa-spin');
            $checkButton.find('a#checkEmail i.icofont-send-mail').remove();
            $checkButton.find('a#checkEmail').append( $('<img class="img-fluid w-100" src="./assets/img/loader/symbol-check-info.svg" />') );

            $.ajax({
                url : '/verification/email',
                type : 'POST',
                data: {
                    email: email
                },
                success : function(data) {
                    //  If code has been send with success
                    if(data.success) {
                        // remove loader at the end
                        $checkButton.remove();
                        // Block to the edit email button
                        $('input#emailSubscribe').parents('div.form-row').append( $('<div id="edit-email-block" class="col-1" data-toggle="tooltip" data-placement="bottom" title="Modifier l\'adresse email"><a id="editEmail" class="btn bg-primary-red text-light" href="#" role="button"><i class="icofont icofont-edit-alt fa-lg"></i></a></div>') );
                        // Block to the input code email
                        $('input#emailSubscribe').parents('div.form-row').append( $('<div class="col-2"><input class="form-control" id="verificationEmailCode" maxlength="4" name="verificationEmailCode" type="text" placeholder="Code" autocomplete="off"></div>') );
                        $.notify(data.message, "success");
                    } else {
                        $('span#emailError').text('Une erreur s\'est produite, rééssayez plus tard !');
                        $button.show();
                        $.notify(data.message, "error");
                    }
                }
            });
        } else {
            // email is incorrect stop the script and display error
            $('span#emailError').text('Veuillez renseigner une adresse email valide');
            // remove loader at the end
            $button.show();
            $('input#emailSubscribe').val('');
            $('input#emailSubscribe').parents('div.form-row').find('div.col-1').remove();
        }
    });

    /** 
     * Event - Click on button to edit email address
     * Reset state of the input email address
     */
    $('body').on('click', 'div#step-1 a#editEmail', function(e) {
        e.preventDefault();
        // Remove error message email address
        $('body').find('span#emailError').text('');
        // Remove input email address code
        $('body').find('input#verificationEmailCode').closest('div.col-2').remove();
        // Remove button to send SMS
        $('body').find('a#checkEmail').closest('div.col-1').remove();
        // Remove button to check if code match
        $('body').find('a#sendEmailCode').closest('div.col-1.send-email-code').remove();
        // Remove button to edit email address
        $('body').find('div#edit-email-block').remove();
        // Remove disabled attribute for email address input
        $('input#emailSubscribe').attr("disabled", false).trigger('input').focus();
    });

    /** 
     * Event - Click on button to edit email address
     * Reset state of the input email address
     */
    $('body').on('click', 'div#step-1 a#sendEmailCode', function(e) {
        e.preventDefault();
        console.log('Send code email input to check if code match');

        $('span#emailError').text('');
        $('span#verificationCodeError').text('');
        var $button = $(this);

        var $email = $('input#emailSubscribe');
        var $verificationCode = $('input#verificationEmailCode');

        if($email.val() != '' && $verificationCode.val() != '') {
            $.ajax({
                url : '/verification/check-email',
                type : 'POST',
                data: {
                    email: $email.val(),
                    code: $verificationCode.val()
                },
                success : function(data) {
                    data = data;
                    if(data.success) {
                        console.log(data.message);
                        $button.closest('div.form-row').children('div').not('.col.form-group').remove();
                        // Add indicator to the number and code status
                        $.notify(data.message, 'success');
                    } else {
                        console.log(data.message);
                        $('span#emailError').text('Le code de vérification est erroné');
                        // $('a#editEmail').closest('div.column.one-fourth').fadeIn();
                        $.notify(data.message, 'error');
                    }
                }
            });
        } else {
            // Check error and display alert message
            // $('span#emailError').text('Veuillez saisir le bon code de vérification');
        }
    });


    // STEP SETTING
    // Step 0
    // Input file ice event change
    $('input#ice-file').on('change', function(e) { 
        var fileName = e.target.files[0].name;
        $('#upload-icon span.filename').remove();
        $('#upload-icon').append( $(`<span class="text-muted filename" style="font-size: 0.8em; display:block">${fileName}</span>`) );
        $('span#iceFileError').text('');
    });

    // Step 3 Status company event
    $('div.company-status-item').click(function(e){
        $('div.company-status-item').removeClass('active');
        $(this).addClass('active');
        $(this).closest('form').find('input[name="companyStatus"]').val( $(this).attr('data-company') );
    });

    // Step 4 
    // show summary informations
    // button validate and back event
    $('body').on('click', 'div.desc a.trigger-previous-step', function(e){
        e.preventDefault();
        $('a#previous-step').trigger('click');
        $('div#nextAndPreviousStepButtons').fadeIn();
    });

    $('body').on('click', 'div.desc a.trigger-next-step, div.desc div.option-payment', function(e){
        e.preventDefault();
        $('a#next-step').trigger('click');
        $('div#nextAndPreviousStepButtons').fadeIn();
    });

    // Step 6
    // Pricing
    $(document).ready(function () { 
        $(".check-moukawil").click(function () { 
            $('input[name="subscriptionFormulaRadios"]').prop("checked", true); 
            $("#moukawil").prop("checked", true); 
            $('.check-button').removeClass('btn-danger').removeClass('btn-light');
            $('.check-button:not(.check-moukawil)').addClass('btn-light');
            $(this).addClass('btn-danger');
        }); 
        $(".check-pro").click(function () { 
            $('input[name="subscriptionFormulaRadios"]').prop("checked", true); 
            $("#pro").prop("checked", true); 
            $('.check-button').removeClass('btn-danger').removeClass('btn-light');
            $('.check-button:not(.check-pro)').addClass('btn-light');
            $(this).addClass('btn-danger');
        }); 
        $(".check-entreprise").click(function () { 
            $('input[name="subscriptionFormulaRadios"]').prop("checked", true); 
            $("#entreprise").prop("checked", true); 
            $('.check-button').removeClass('btn-danger').removeClass('btn-light');
            $('.check-button:not(.check-entreprise)').addClass('btn-light');
            $(this).addClass('btn-danger');
        });
        $(".check-premium").click(function () { 
            $('input[name="subscriptionFormulaRadios"]').prop("checked", true); 
            $("#premium").prop("checked", true); 
            $('.check-button').removeClass('btn-danger').removeClass('btn-light');
            $('.check-button:not(.check-premium)').addClass('btn-light');
            $(this).addClass('btn-danger');
        });
    });

    // Trigger next step
    $('body div.desc').on('click', 'form a.trigger-next-step, form div.option-payment', function(e){
        e.preventDefault();
        $('button#next-step').trigger('click');
        $('div#nextAndPreviousStepButtons').fadeIn();
    });

    // Activate Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // RUN
    showPreviousStepButton();
    getRegions('select#region', 'select#city');
    getActivityArea('select#activityArea', 'select#branch', 'select#sub-branch');

}); 