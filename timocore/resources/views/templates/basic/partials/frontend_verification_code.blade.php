<div class="mb-0 mt-5"> 
    <div class="verification-code">
        <div class="d-flex gap-xl-3 gap-sm-2 gap-1">
            <input type="number" placeholder="-" class="text-center form--control code-input" minlength="1" maxlength="1" required>
            <input type="number" placeholder="-" class="text-center form--control code-input" minlength="1" maxlength="1" required>
            <input type="number" placeholder="-" class="text-center form--control code-input" minlength="1" maxlength="1" required>
            <input type="number" placeholder="-" class="text-center form--control code-input" minlength="1" maxlength="1" required>
            <input type="number" placeholder="-" class="text-center form--control code-input" minlength="1" maxlength="1" required>
            <input type="number" placeholder="-" class="text-center form--control code-input" minlength="1" maxlength="1" required>
        </div>
    </div> 

    <input type="hidden" name="code" id="verificationCode" autocomplete="off">
</div>
@push('script')
    <script>
        (function($) {
            "use strict";

            let allSelected = false;

            $('.code-input').on('keydown', function(event) {
                if (event.key === 'Backspace') {
                    if (allSelected) {
                        $('.code-input').val('');
                        $('.code-input').removeClass('selected');
                        allSelected = false;
                    } else if ($(this).val() === '') {
                        var prevInput = $(this).prev('.code-input');
                        if (prevInput.length) {
                            prevInput.val('').focus();
                        }
                    }
                } else if (event.key === 'a' && (event.ctrlKey || event.metaKey)) {
                    event.preventDefault();
                    $('.code-input').addClass('selected');
                    $('.code-input').first().focus();
                    allSelected = true;
                } else {
                    $('.code-input').removeClass('selected');
                    allSelected = false;
                }
                codeSetToCodeInput();
            });



            $('.code-input').on('input', function() {
                if ($(this).val().length > $(this).attr('maxlength')) {
                    $(this).val($(this).val().slice(0, $(this).attr('maxlength')));
                }

                if ($(this).val().length == $(this).attr('maxlength')) {
                    $(this).next('.code-input').focus();
                }
                codeSetToCodeInput();
                submitCodeForm();
            });


            $('.code-input').on('paste', function(event) {
                var clipboardData = (event.originalEvent || event).clipboardData.getData('text');
                var inputFields = $('.code-input');

                if (clipboardData.length === inputFields.length && /^\d+$/.test(clipboardData)) {
                    for (var i = 0; i < clipboardData.length; i++) {
                        $(inputFields[i]).val(clipboardData[i]);
                    }
                    $(inputFields[inputFields.length - 1]).focus();
                    event.preventDefault();
                }
                codeSetToCodeInput();
                submitCodeForm();
            });


            let verificationCode = $('#verificationCode');

            function codeSetToCodeInput() {
                let combinedValue = '';

                $('.code-input').each(function() {
                    let val = $(this).val().trim();
                    if (val) {
                        combinedValue += val;
                    }
                });

                verificationCode.val(combinedValue);
            }

            function submitCodeForm() {
                if (verificationCode.val().length == 6) {
                    $('.submit-form').find('button[type=submit]').html('<i class="las la-spinner fa-spin"></i>');
                    $('.submit-form').submit()
                }
            }

        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        .account-form .form--control {
            padding-inline: 2px;
        }

        .account-form .form--control {
            background: #dddddd6c !important;
        }

        .account-form .form--control:focus,
        .account-form .form--control:not(:placeholder-shown) {
            background: #FFF !important
        }
    </style>
@endpush
