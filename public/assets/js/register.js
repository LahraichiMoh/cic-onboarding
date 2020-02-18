jQuery(function($) {

    function emailIsValid(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    }

    // example notify usage
    // $.notify("Access granted", "success");

    $(document).on('click', 'a.next-step[href^="#"]', function (event) {
        event.preventDefault();

        $('html, body').animate({
            scrollTop: $($.attr(this, 'href')).offset().top
        }, 1000);
    });

    var step = 0;
    var maxStep = 8;

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

    showPreviousStepButton = function () {
        if(step <= 0) {
            $('a#previous-step').hide();
        } else {
            $('a#previous-step').show();
            $(`div#step-${step}`).find('div.step-pause').show();
        }
    }

    nextStepAction = function() {
        if(step <= maxStep) {
            sendStepForm(  $(`form#step-form-${step}`) );
        }
    }

    $.fn.scrollBottom = function() { 
        return $(document).height() - this.scrollTop() - this.height(); 
    };

    previousStepAction = function() {
        if(step >= 1) {
            $(`div#step-${step}`).slideUp({ duration: 1600, easing: 'swing' });
            var body = $("html, body");
            var passedStep = `div#step-${step}`;
            var paddingScroll = $(passedStep).height() * (20/100);
            body.stop().animate({scrollTop: ($(document).scrollTop() - $(passedStep).height() - paddingScroll) }, 1600, 'swing', function() { 
            });

            $(`div#step-${step}`).find('div.loader').remove();
            $(`div#step-${step}`).find('form').show();

            step --;
            $('div#step-block-section').find(`form#step-form-${step}`).show();
            $('div#step-block-section').find(`form#step-form-${step}`).closest('div.desc').find(`dl`).remove();
            // $('div#step-block-section').find(`dl#step-info-${step}`).remove();

            if( (typeof $(`div#step-${step}`).attr('data-hidden-step') !== typeof undefined) && ($(`div#step-${step}`).attr('data-hidden-step') !== false) ) {
                $('div#nextAndPreviousStepButtons').fadeOut();
            }
        }
        showPreviousStepButton();
    }
    
    sendStepForm = function(form) {
        var response;

        // Remove all error message 
        form.find('span.msg-info').text('');

        // Display loader
        form.closest('div.desc').append('<div class="loader" style="text-align: center; padding: "><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');
        
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
                    $dl = $(`<dl id="step-info-${step}"></dl>`);

                    $.each(response.items, function (title, content) {
                        if(title == 'files') {
                            $.each(response.items.files, function (i, file) {
                                $dl.append( $(`<dt>${file.name}</dt>`) );
                                if(file.ext == 'pdf') {
                                    $dl.append(`<object width="400" height="240" data="${file.completePath}"></object>`);
                                } else {
                                    $dl.append( $(file.imageBlock) );
                                }
                            });
                        } else {
                            $dl.append( $(`<dt>${title}</dt>`) );
                            $dl.append( $(`<dd>${content}</dd>`) );
                        }
                    });

                    form.closest('div.desc').find('div.loader').remove();
                    form.closest('div.desc').append($dl);
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
                    window.location.replace("./Onboarding/onboarding.payment.php");
                    // window.location.replace("?page=payment");
                } else {
                    if(response.status) {
                        $(`div#step-${step}`).find('div.step-pause').hide();
                        var passedStep = `div#step-${step}`;

                        step ++;
                        $(`div#step-${step}`).fadeIn('slow'); 
                        var body = $("html, body");
                        var paddingScroll = $(passedStep).height() * (20/100);
                        body.stop().animate({scrollTop: ($(document).scrollTop() + $(passedStep).height() + paddingScroll) }, 2000, 'swing', function() { 
                        });
                        $(`a.next-step[href="#step-${step}"]`).trigger('click');

                        if( (typeof $(`div#step-${step}`).attr('data-summary') !== typeof undefined) && ($(`div#step-${step}`).attr('data-summary') !== false) ) {
                            getAndDisplaySummaryInformations( $(`div#step-${step}`) );
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-hidden-step') !== typeof undefined) && ($(`div#step-${step}`).attr('data-hidden-step') !== false) ) {
                            $('div#nextAndPreviousStepButtons').fadeOut();
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-terms-service') !== typeof undefined) && ($(`div#step-${step}`).attr('data-terms-service') !== false) ) {
                            getTermsOfService( $(`div#step-${step}`) );
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-payment-summary') !== typeof undefined) && ($(`div#step-${step}`).attr('data-tpayment-summary') !== false) ) {
                            getPaymentSummary( $(`div#step-${step}`) );
                        }

                        if( (typeof $(`div#step-${step}`).attr('data-payment') !== typeof undefined) && ($(`div#step-${step}`).attr('data-tpayment') !== false) ) {
                            getPayment( $(`div#step-${step}`) );
                        }
                    }
                    showPreviousStepButton();
                }

                if(response.hideStep){ 
                    $('div#nextAndPreviousStepButtons').fadeOut();
                }
            }
        });
    }

    getAndDisplaySummaryInformations = function($divStepElmt){
        if(!$divStepElmt) return false;

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : './OnBoarding/onboarding.treat.php',
            type : 'POST',
            data: {
                action: 'getSummaryInfos',
            },
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

    getTermsOfService = function($divStepElmt) {
        if(!$divStepElmt) return false;

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : './OnBoarding/onboarding.treat.php',
            type : 'POST',
            data: {
                action: 'getTermsOfService',
            },
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

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : './OnBoarding/onboarding.treat.php',
            type : 'POST',
            data: {
                action: 'getPaymentSummary',
            },
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

        $divStepElmt.append('<div class="loader" style="text-align: center; padding: "><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 15%;" /></div>');

        var content;

        $.ajax({
            url : './OnBoarding/onboarding.treat.php',
            type : 'POST',
            data: {
                action: 'getPayment',
            },
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

    showPreviousStepButton();

    // Events List

    // Next step button
    $('a#next-step').click(function(e){
        e.preventDefault();
        nextStepAction();
    });

    // Previous step button
    $('a#previous-step').click(function(e){
        e.preventDefault();
        previousStepAction();
    });

    // Phone number verification
    $('a#checkPhoneNumber').click(function(e){
        e.preventDefault();
        var $button = $(this);
        // Display loader here
        // $button.hide();
        // $button.closest('div.column.one-fourth').append('<div class="loader" style="text-align: center; display: flex;"><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 2em;" /></div>');

        // Remove old status message
        $('span#phoneSubscribeError').text('');
        $('span#verificationCodeError').text('');

        // var phoneNumberReg = /([0-9]{10})|(\([0-9]{3}\)\s+[0-9]{3}\-[0-9]{4})/;
        var phoneNumberReg = /(\+212|0)([ \-_/]*)(\d[ \-_/]*){9}/g;
        var phoneNumber = $('input#phoneSubscribe').val();
        phoneNumber = phoneNumber.replace(/\D/g,'');

        // If phone number is valid
        if (phoneNumberReg.test( phoneNumber )) {
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
                        $button.closest('div.column.one-fourth').find('div.loader').remove();
                        $('label#checkPhoneNumberLabel').closest('div.column.one-fourth').hide();
                        $('div#checkPhoneCode').css('display', 'flex');
                        $.notify(data.message, "success");
                    } else {
                        $('span#phoneSubscribeError').text('Une erreur s\'est produite, rééssayez plus tard !');
                        $button.closest('div.column.one-fourth').find('div.loader').remove();
                        $button.show();
                        $.notify(data.message, "error");
                    }
                }
            });
        } else {
            // phone number is incorrect stop the script and display error
            $('span#phoneSubscribeError').text('Veuillez renseigner un numéro de téléphone valide');
            // remove loader at the end
            $button.closest('div.column.one-fourth').find('div.loader').remove();
            $button.show();
        }
    });

    /**
     * Check if phone code generated match with the code entered
     * Create and use ajax request
     */
    $('a#checkPhoneNumberCode').click(function(e){
        e.preventDefault();
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
                    if(data.success) {
                        $button.closest('div.column.one-third').hide();
                        $('div#checkPhoneCode').fadeOut();
                        $('div#editPhoneBloc').fadeOut();
                        // Add indicator to the number and code status
                        $.notify(data.message, 'success');
                    } else {
                        $('span#verificationCodeError').text('Le code de vérification est erroné');
                        $('a#editPhoneNumber').closest('div.column.one-fourth').fadeIn();
                        $.notify(data.message, 'error');
                    }
                }
            });
        } else {
            $('span#verificationCodeError').text('Veuillez saisir le bon code de vérification');
        }
    });

    $('a#editPhoneNumber').click(function(e){
        e.preventDefault();
        // $('input#phoneSubscribe').attr("disabled", false);
        $('input#phoneSubscribe').focus();
        $('a#editPhoneNumber').closest('div.column.one-fourth').fadeOut();
        $('div#checkPhoneCode').fadeOut();

        $('a#checkPhoneNumber').closest('div.column.one-fourth').fadeIn();
        $('a#checkPhoneNumber').fadeIn();
    });

    // Email number verification
    $('a#checkEmail').click(function(e){
        e.preventDefault();
        var $button = $(this);
        // Display loader here
        $button.hide();
        $button.closest('div.column.one-fourth').append('<div class="loader" style="text-align: center; display: flex;"><img src="images/loader/symbol-check-info.svg" style="display: block; margin-left: auto; margin-right: auto; width: 2em;" /></div>');

        // Remove old status message
        $('span#emailError').text('');
        $('span#verificationEmailCodeError').text('');
        $('input#verificationEmailCode').val('');

        var email = $('input#emailSubscribe').val();

        // If email is valid
        if (emailIsValid(email)) {
            $.ajax({
                url : './OnBoarding/onboarding.treat.php',
                type : 'POST',
                data: {
                    action: 'getEmailVerificationCode',
                    email: email
                },
                success : function(data) {
                    //  If code has been send with success
                    data = JSON.parse(data);
                    if(data.success) {
                        // remove loader at the end
                        $button.closest('div.column.one-fourth').find('div.loader').remove();
                        $('label#checkEmailLabel').closest('div.column.one-fourth').hide();
                        $('div#checkEmailCode').css('display', 'flex');
                    } else {
                        $('span#emailError').text('Une erreur s\'est produite, rééssayez plus tard !');
                        $button.closest('div.column.one-fourth').find('div.loader').remove();
                        $button.show();
                    }
                }
            });
        } else {
            // email is not validate
            $('span#emailError').text('Veuillez renseigner un email valide');

            // remove loader at the end
            $button.closest('div.column.one-fourth').find('div.loader').remove();
            $button.show();
        }
    });

    /**
     * Check if email code generated match with the code entered
     * Create and use ajax request
     */
    $('a#checkEmailCodeButton').click(function(e){
        e.preventDefault();
        $('span#verificationEmailCodeError').text('');
        var $button = $(this);

        var $email = $('input#emailSubscribe');
        var $verificationCode = $('input#verificationEmailCode');

        if($email.val() != '' && $verificationCode.val() != '') {
            $.ajax({
                url : './OnBoarding/onboarding.treat.php',
                type : 'POST',
                data: {
                    action: 'emailVerification',
                    email: $email.val(),
                    code: $verificationCode.val()
                },
                success : function(data) {
                    data = JSON.parse(data);
                    console.log(data);
                    if(data.success) {
                        $button.closest('div.column.one-third').hide();
                        $('div#checkEmailCode').fadeOut();
                        $('div#editEmailBloc').fadeOut();
                        // Add indicator to the number and code status
                        $.notify('Le numéro a été validé avec succès', "success");
                    } else {
                        $('span#verificationEmailCodeError').text('Le code de vérification est erroné');
                        $('a#editEmail').closest('div.column.one-fourth').fadeIn();

                        $.notify('Le code de vérification est erroné', 'error');
                    }
                }
            });
        } else {
            $('span#verificationEmailCodeError').text('Veuillez saisir le bon code de vérification');
        }
    });

    $('a#editEmail').click(function(e){
        e.preventDefault();
        // $('input#emailSubscribe').attr("disabled", false);
        $('input#emailSubscribe').focus();

        $('a#editEmail').closest('div.column.one-fourth').fadeOut();
        $('div#checkEmailCode').fadeOut();

        $('a#checkEmail').closest('div.column.one-fourth').fadeIn();
        $('a#checkEmail').fadeIn();
    });

    // Upload file icon event 
    $('i.icofont-cloud-upload.icon__upload').click(function(e){
        $(this).prev('input.file-input').trigger('click');
    });

    // Input file ice event change
    $('input#ice-file').on('change', function(e) { 
        var fileName = e.target.files[0].name;
        $(this).next('i.icofont-cloud-upload.icon__upload').find('span').remove();
        $(this).next('i.icofont-cloud-upload.icon__upload').append( $(`<span style="font-size: 0.3em; display:block">${fileName}</span>`) );
    });

    // Step 3 Status company event
    $('div.vous__stat').click(function(e){
        $('div.vous__stat').removeClass('active');
        $(this).addClass('active');
        $(this).closest('form').find('input[name="companyStatus"]').val( $(this).attr('data-company') );
    });

    // show summary informations
    // button validate and back event
    $('body div.desc').on('click', 'form a.trigger-previous-step', function(e){
        e.preventDefault();
        $('a#previous-step').trigger('click');
        $('div#nextAndPreviousStepButtons').fadeIn();
    });

    $('body div.desc').on('click', 'form a.trigger-next-step, form div.option-payment', function(e){
        e.preventDefault();
        $('a#next-step').trigger('click');
        $('div#nextAndPreviousStepButtons').fadeIn();
    });

    // Step 6 
    $('input.choice').each(function(index, value) {
        $(`input[name="${$(this).attr('name')}Check"]`).val( $(this).is(':checked') ? 1 : 0);
    }).change(function(e) {
        $(`input[name="${$(this).attr('name')}Check"]`).val( $(this).is(':checked') ? 1 : 0);
    });

    // To forced numeric input
    $('input#phoneSubscribe').on("input", function() {
        var dInput = this.value;
        $(this).val( this.value.replace(/\D/g,'') );
    });

    getRegions('select#region', 'select#city');
    // getCities('select#summaryCity', 'select#summaryRegion');
    getActivityArea('select#activityArea', 'select#branch', 'select#sub-branch');
});