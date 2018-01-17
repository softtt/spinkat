/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function() {
    if (!!$.prototype.fancybox) {
        $('#one_click_checkout button').fancybox({
            'autoSize' : false,
            'height' : 'auto',
            'width' : '285',
            'hideOnContentClick': false,
            'padding': 25,
        });
    }

    $(document).off('click', '#submitsimple_one_click_checkout').on('click', '#submitsimple_one_click_checkout', function(e){
        e.preventDefault();

        var errors = false;
        var customer_data = {};

        $('#one_click_checkout_form_data input, #one_click_checkout_form_data textarea').each(function (i, el) {
            if (validateField($(el)) === false)
                errors = true;

            var field_id = $(el).attr('name');
            customer_data[field_id] = $(el).val();
        });

        if (errors) {
            oneClickCheckout.showNotification('error_validation');
            $('#submitsimple_one_click_checkout').removeProp('disabled').removeClass('disabled');
            return;
        } else {
            oneClickCheckout.showNotification('none');
            oneClickCheckout.placeOrder($('#product_page_product_id').val(),
                $('#idCombination').val(),
                $('#quantity_wanted').val(),
                customer_data
            );
        }
    });
});

var oneClickCheckout = {

    placeOrder: function(id_product, id_combination, quantity, customer_data) {
        var result = false;

        if (typeof oneclickorder_url == 'undefined')
            return (false);

        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: oneclickorder_url,
            async: false,
            cache: false,
            dataType : "json",
            data: {
                placeOrder: 1,
                ajax: true,
                id_product: id_product,
                id_product_attribute: id_combination,
                quantity: quantity,
                customer_data: customer_data
            },
            success: function(jsonData,textStatus,jqXHR)
            {
                if (jsonData.errors === true)
                {
                    oneClickCheckout.showNotification(jsonData.error_type);
                    submit_button.prop('disabled', false);
                }
                else if (jsonData.errors === false && jsonData.order_placed === true)
                {
                    $('#one_click_checkout_form #one_click_checkout_form_data').hide();
                    $('#one_click_checkout_form #simple_one_click_checkout_form_success').show();
                }

                result = true;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
                var error = "Impossible to process order checkout.<br/>textStatus: '" + textStatus + "'<br/>errorThrown: '" + errorThrown + "'<br/>responseText:<br/>" + XMLHttpRequest.responseText;
                if (!!$.prototype.fancybox)
                    $.fancybox.open([
                    {
                        type: 'inline',
                        autoScale: true,
                        minHeight: 30,
                        content: '<p class="fancybox-error">' + error + '</p>'
                    }],
                    {
                        padding: 0
                    });
                else
                    alert(error);

                //reactive the button when adding has finished
                $('#submitsimple_one_click_checkout').removeProp('disabled').removeClass('disabled');
            }
        });

        return true;
    },


    showNotification: function(type) {
        var type = type || 'none';

        var notifications = {
            'error_validation' : $('#one_click_checkout_form_data .error.validation'),
            'error_callback' : $('#one_click_checkout_form_data .error.callback'),
        };

        $.each(notifications, function() {
            $(this).hide();
        });

        if (type === 'none')
            return;

        if (type in notifications)
            notifications[type].show();
    }
}
