var codeSent = false;
var checkoutForm = (function ($) {
    var form = $('#checkout-form'),
        pub = {
            init: function() {
                initNextStep();
                initBilling();
                initPayment();
                initDiscountCode();
                initSummary();
                initCheckout();
            }
        };
    function initNextStep() {       
        $(document).on('click', '.next-step', function(e) {
            e.preventDefault();
            var el = $(this),
                step = $(el).data('step'),
                validateOption = $(el).data('validate');
            $('#loadmoreajaxloader').show();
            setTimeout(function() {
                $.ajax({
                    type : 'POST',
                    url : '/order/checkout/setstep',
                    async : false,
                    cache : false,
                    data : {step: step},
                    success : function(response) {
                        if (validateOption) {
                            if (validate(validateOption)) {
                                var nextEl = $('.step-' + step).removeClass('disable'),
                                    scroll = nextEl.data('scroll');
                                scrollContentTo(scroll ? $(scroll) : nextEl);
                            }
                        } else {
                            var nextEl = $('.step-' + step).removeClass('disable'),
                                scroll = nextEl.data('scroll');
                            scrollContentTo(scroll ? $(scroll) : nextEl);
                        }
                        sendCheckoutStatus(step);
                        if (step == 2 && $('#Order_guest:checked').length > 0) {
                            sentCheckoutOption(step, 'guest');
                        }
                        $('#loadmoreajaxloader').hide();
                    }
                });
            }, 10);
        });
    }
    function initBilling() {
        $(document).on('change', '#Order_shippingmethodoption_id', function() {
            var el = $(this);
            $('#loadmoreajaxloader').show();
            setTimeout(function() {
                $.ajax({
                    type : 'POST',
                    url : '/order/checkout/GetShippingOptionPrice',
                    async : false,
                    cache : false,
                    data : {
                        option_id : el.val()
                    },
                    dataType: 'json',
                    success : function(result) {
                        $('.order-total-price').text(result.orderTotalLabel);
                        $('.float-cart-btn strong').text(result.orderTotalLabel);
                        $('.order-shipping-price').text(result.shippingPriceLabel);
                        if (result.orderTotalWitoutSavigsLabel) {
                            $('.order-total-price-without-savings').text(result.orderTotalWitoutSavigsLabel);
                        } else {
                            $('.order-total-price-without-savings').text('');
                        }
                        $('#loadmoreajaxloader').hide();
                        reloadSummary();
                    }
                });
            }, 10);
        }).on('change', '#OrderContact_3_country', function() {
            var el = $(this);
            $('#loadmoreajaxloader').show();
            setTimeout(function() {
                $.ajax({
                    type : 'POST',
                    url : '/nomenclator/states',
                    async : false,
                    cache : false,
                    data  : {
                        country_name: el.val(),
                        field_name: 'state',
                        model_name: 'OrderContact[3]'
                    },
                    success : function(response) {
                        $('#OrderContact_3_state').parent().html(response);
                        $('#loadmoreajaxloader').hide();
                    }
                });
            }, 10);
        });
    }
    function initPayment() {
        $(document).on('click', 'input[name="Order[payment_option]"]', function() {
            var el = $(this),
                payment = el.val();

            $('.container-payment').hide();

            if($("#container-" + payment).length > 0) {
                $("#container-" + payment).show();
            }
        }).on('click', '.btn-paypal', function(e) {
            e.preventDefault();
            var el = $(this);
            $('.btn-paypal').removeClass('active');
            el.addClass('active');
            $('#PaypalPaymentMethod_type').val(el.data('type'));
            $('#PaypalPaymentMethod_type_em_').text('');
            getPaypal();
            scrollContentTo($('#checkoutButtonSubmit'));
        });
    }
    function initDiscountCode() {
        $(document).on('change', '#promoCode', function(e) {
            if (!codeSent) {
                var el = $(this);
                promoCode(el);
            }
        }).on('keydown', '#promoCode', function(e) {
            if (e.which == 13 || e.keyCode == 13) {
                e.preventDefault();
                var el = $(this);
                promoCode(el);
                codeSent = true;
                setTimeout(function() {
                    codeSent = false;
                }, 100);
            }
        });
    }
    function initSummary() {
        $(document).on('click', '.drivers .driver-experience', function(e) {
            e.preventDefault();
            var el = $(this);
            if (!el.hasClass('active')) {
                $('.experience-data').hide().removeClass('active');
                $('.driver-experience').removeClass('active');
                $('.driver-experience i').removeClass('fa-caret-down').addClass('fa-caret-right');
                el.addClass('active').find('i').removeClass('fa-caret-right').addClass('fa-caret-down');
                $('.experience-data-' + el.data('id')).slideDown().addClass('active');
            }
        });
    }
    function initCheckout() {
        var error = $('.help-block.error:visible');
        if (error.length) {
            scrollContentTo(error);
        }
        $(document).on('click', '#checkoutButtonSubmit', function(e) {
            if ($('input[name="Order[payment_option]"]:checked').val() == 'paypal') {
                e.preventDefault();
                if (validate('Payment')) {
                    if (orderTerms === true || $('#Order_terms:checked').length > 0) {
                        checkout.paypal.initAuthFlow();
                    } else {
                        $('#Order_terms_em_').text('You should agree to Terms of Sale').show();
                    }
                }
            }
        }).on('submit', '#checkout-form', function(e) {
            if (validate('Payment')) {
                $('#loadmoreajaxloader').show();
            } else {
                e.preventDefault();
            }
        });
    }
    function promoCode(el) {
        $('#loadmoreajaxloader').show();
        setTimeout(function() {
            $.ajax({
                type : 'POST',
                url : '/order/checkout/applyDiscount',
                async : false,
                cache : false,
                data  : {
                    discount_code: el.val()
                },
                dataType: 'json',
                success : function(result) {
                    $('#summary-data').replaceWith(result.html);
                    getPaypal();
                    if (result.message) {
                        alert(result.message);
                    }
                }
            });
        }, 10);
    }
    function getPaypal() {
        $('#loadmoreajaxloader').show();
        setTimeout(function() {
            $.ajax({
                type : 'GET',
                url : '/order/checkout/ppcheckout',
                async : false,
                cache : false,
                dataType: 'json',
                success : function(result) {
                    if ($('#paypal-checkout-script').length > 0) {
                        $('#paypal-checkout-script').replaceWith(result.html);
                    } else {
                        $('body').append(result.html);
                    }
                    $('#loadmoreajaxloader').hide();
                }
            });
        }, 10);
    }
    function scrollContentTo(el) {
        $('html, body').animate({
            scrollTop: el.offset().top - (isMobile ? 60 : 150),
        }, 500);
    }
    function validate(validateAction) {
        var valid = false;
        $.ajax({
            type : 'POST',
            url : '/order/checkout/validate' + validateAction,
            async : false,
            cache : false,
            data : form.serialize(),
            dataType: 'json',
            success : function(result) {
                $('.help-block').hide();
                if(getResponseSize(result) > 0) {
                    showAttributesErrors(result);
                    valid = false;
                } else {
                    valid = true;
                }
                $('#loadmoreajaxloader').hide();
            }
        });
        return valid;
    }
    function getResponseSize(response){
        var size = 0, key;
        for (key in response) {
            if (response.hasOwnProperty(key)) size++;
        }
        return size;
    }
    function showAttributesErrors(errors) {
        for (var key in errors) {
            $('#' + key + '_em_').text(errors[key][0]).addClass('error').show();
        }
    }
    /* Tracking Enhance Ecommerce */
    function sendCheckoutStatus(step, option) {
        if (typeof ga == 'function') {
            var cartStructures = JSON.parse(cartStructure);
            products = cartStructures.products;
            $.each(products, function (index, product) {
                ga('ec:addProduct', {
                    'id': product.id,
                    'name': product.name,
                    'category': product.category,
                    'brand': product.brand,
                    'price': product.price,
                    'quantity': product.quantity,
                });
            });

            promo = cartStructures.promo;
            if (promo) {
                ga('ec:addPromo', {
                    'id': promo.id,
                    'name': promo.name,
                    'creative': promo.creative,
                    'position': promo.position,
                });
            }

            ga('ec:setAction', 'checkout', {step: step});
            ga('send', {hitType: 'event', eventCategory: 'Ecommerce', eventAction: 'Checkout', eventLabel: undefined, eventValue: undefined});
        }
    }
    function sentCheckoutOption(step, option) {
        if (typeof ga == 'function') {
            ga('ec:setAction', 'checkout_option', {
                'step': step,
                'option': option
            });
            ga('send', 'event', 'Checkout', 'Option');
        }
    }
    function reloadSummary() {
        if (!$('.float-cart-btn').hasClass('active')) {
            $('.summary-panel').remove();
            if (typeof summary != 'undefined') {
                summary.getBoxContent();
            }
        }
    }
    return pub;
})(jQuery);