define([
    'jquery',
    'mage/validation'
], function ($, collectorajax, validation) {
    return {
        call: function (ajaxUrl) {

            if (window.showCollectorShipping) {
                $(document).on('mouseover', '.collector-checkout-wrapper', function () {
                    if ($('.collector-checkout.disabled').length > 0) {
                        if (!$('.form-shipping-address').valid()) {
                            $('.collector-checkout').addClass('disabled');
                        } else {
                            var param = {
                                is_ajax: true,
                                type: 'shippingValidate'
                            }
                            $.ajax({
                                url: ajaxUrl,
                                data: param,
                                type: "POST",
                                dataType: 'json',
                                beforeSend: function () {
                                    //jQuery('body').addClass('is-suspended');
                                    window.collector.checkout.api.suspend();
                                },
                                success: function (data) {
                                    if (data.error === 0) {
                                        $('.collector-checkout').removeClass('disabled');
                                    } else {
                                        alert(data.messages);
                                        event.preventDefault();
                                    }
                                },
                                complete: function () {
                                    //jQuery('body').removeClass('is-suspended');
                                    window.collector.checkout.api.resume();
                                }
                            });
                        }
                    }
                });
                $(document).on('change', '.collector_shipping_address input, .collector_shipping_address select', function () {
                        var param = {
                            is_ajax: true,
                            type: 'shippingAddress',
                            name: $(this).attr('name'),
                            value: $(this).val()
                        }
                        $('.collector-checkout').addClass('disabled');

                        //if ($(this).hasClass('validate')) {
                        $(this).mage('validation', {});
                        //}
                        if (!$('.form-shipping-address').valid()) {
                            $('.collector-checkout').addClass('disabled');
                            $('div.mage-error').remove();
                        } else {
                            if ($(this).valid()) {
                                $.ajax({
                                    url: ajaxUrl,
                                    data: param,
                                    type: "POST",
                                    dataType: 'json',
                                    beforeSend: function () {
                                        //jQuery('body').addClass('is-suspended');
                                        window.collector.checkout.api.suspend();
                                    },
                                    complete: function () {
                                        //jQuery('body').removeClass('is-suspended');
                                        window.collector.checkout.api.resume();
                                        require([
                                            'Magento_Customer/js/customer-data'
                                        ], function (customerData) {
                                            var sections = ['cart'];
                                            customerData.invalidate(sections);
                                            customerData.reload(sections, true);
                                        });
                                    },
                                    success: function () {
                                        var param = {
                                            is_ajax: true,
                                            type: 'shippingValidate',
                                            ignore_country: true
                                        }
                                        $.ajax({
                                            url: ajaxUrl,
                                            data: param,
                                            type: "POST",
                                            dataType: 'json',
                                            beforeSend: function () {
                                                //jQuery('body').addClass('is-suspended');
                                                window.collector.checkout.api.suspend();
                                            },
                                            success: function (data) {
                                                if (data.error === 0) {
                                                    $('.collector-checkout').removeClass('disabled');
                                                } else {
                                                    alert(data.messages);
                                                    event.preventDefault();
                                                }
                                            },
                                            complete: function () {
                                                //jQuery('body').removeClass('is-suspended');
                                                window.collector.checkout.api.resume();
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                );
            }
            $(document).on('click', '.col-inc', function () {
                var param = {
                    is_ajax: true,
                    id: this.id,
                    type: "inc"
                };
                $.ajax({
                    url: ajaxUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {
                        if (data.cart) {
                            jQuery('div.collector-cart').replaceWith(data.cart);
                        }
                    },
                    complete: function () {
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                        require([
                            'Magento_Customer/js/customer-data'
                        ], function (customerData) {
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                        });
                    }
                });
            });
            $(document).on('click', '.col-sub', function () {
                var param = {
                    is_ajax: true,
                    id: this.id,
                    type: "sub"
                };
                $.ajax({
                    url: ajaxUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {
                        if (data.cart) {
                            jQuery('div.collector-cart').replaceWith(data.cart);
                        }
                    },
                    complete: function () {
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                        require([
                            'Magento_Customer/js/customer-data'
                        ], function (customerData) {
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                        });
                    }
                });
            });
            $(document).on('click', '.newsletter', function () {
                var param = {
                    is_ajax: true,
                    value: document.getElementById('newsletter-checkbox').checked,
                    type: "newsletter"
                };
                $.ajax({
                    url: ajaxUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {

                    },
                    complete: function () {
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                    }
                });
            });
            $(document).on('click', '.col-del', function () {
                var param = {
                    is_ajax: true,
                    id: this.id,
                    type: "del"
                };
                $.ajax({
                    url: ajaxUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {
                        if (data == "redirect") {
                            require([
                                'Magento_Customer/js/customer-data'
                            ], function (customerData) {
                                var sections = ['cart'];
                                customerData.invalidate(sections);
                                customerData.reload(sections, true);
                            });
                            window.location.href = window.location.protocol + "//" + window.location.host + "/";
                        }
                        if (data.cart) {
                            if (window.showCollectorShipping) {
                                if (!$('.form-shipping-address').valid()) {
                                    $('div.mage-error').remove();
                                    $('.collector-checkout').addClass('disabled');
                                }
                                else {
                                    $('.collector-checkout').removeClass('disabled');
                                }
                            }
                            jQuery('div.collector-cart').replaceWith(data.cart);
                        }
                    },
                    complete: function () {
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                        require([
                            'Magento_Customer/js/customer-data'
                        ], function (customerData) {
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                        });
                    }
                });
            });
            $(document).on('click', '.col-radio', function () {
                var param = {
                    is_ajax: true,
                    id: this.id,
                    type: "radio"
                };
                $.ajax({
                    url: ajaxUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        // Suspend the Checkout, showing a spinner...
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {
                        if (window.showCollectorShipping) {
                            if (!$('.form-shipping-address').valid()) {
                                $('div.mage-error').remove();
                                $('.collector-checkout').addClass('disabled');
                            }
                            else {
                                $('.collector-checkout').removeClass('disabled');
                            }
                        }
                        if (data.cart) {
                            jQuery('div.collector-cart').replaceWith(data.cart);
                        }
                        if (data.checkout) {
                            jQuery('div.collector-checkout').replaceWith(data.checkout);
                        }
                    },
                    complete: function () {
                        // ... and finally resume the Checkout after the backend call is completed to update the checkout
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                    },
                });
            });
            $(document).on('click', '.col-codeButton', function () {
                var param = {
                    is_ajax: true,
                    value: document.getElementById("col-code").value,
                    type: "submit"
                };
                $.ajax({
                    url: ajaxUrl,
                    data: param,
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function () {
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {
                        if (data.cart) {
                            jQuery('div.collector-cart').replaceWith(data.cart);
                        }
                    },
                    complete: function () {
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                        require([
                            'Magento_Customer/js/customer-data'
                        ], function (customerData) {
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                        });
                    }
                });
            });
            $(document).on('click', '#col-businesstypes a', function (e) {
                e.preventDefault();
                var ctype = jQuery(this).attr('id');
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        is_ajax: true,
                        value: ctype,
                        type: "btype"
                    },
                    beforeSend: function () {
                        // Suspend the Checkout, showing a spinner...
                        jQuery('body').addClass('is-suspended');
                        window.collector.checkout.api.suspend();
                    },
                    success: function (data) {
                        if (data.cart) {
                            jQuery('div.collector-cart').replaceWith(data.cart);
                        }
                        if (data.checkout) {
                            jQuery('div.collector-checkout').replaceWith(data.checkout);
                        }
                        if (ctype == "b2b") {
                            jQuery("#b2c").addClass("col-inactive");
                            jQuery("#b2c").removeClass("col-active");
                            jQuery("#b2b").addClass("col-active");
                            jQuery("#b2b").removeClass("col-inactive");
                        }
                        else if (ctype == "b2c") {
                            jQuery("#b2b").addClass("col-inactive");
                            jQuery("#b2b").removeClass("col-active");
                            jQuery("#b2c").addClass("col-active");
                            jQuery("#b2c").removeClass("col-inactive");
                        }
                    },
                    complete: function () {
                        // ... and finally resume the Checkout after the backend call is completed to update the checkout
                        jQuery('body').removeClass('is-suspended');
                        window.collector.checkout.api.resume();
                    },
                });
            });
        }
    }
});
