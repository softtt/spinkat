// quick-order.js

$(document).ready(function() {
    // Update order message
    $('#comment').blur(function() {
        $('#opc_delivery_methods-overlay').fadeIn('slow');
        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: orderOpcUrl + '?rand=' + new Date().getTime(),
            async: false,
            cache: false,
            dataType : "json",
            data: 'ajax=true&method=updateMessage&message=' + encodeURIComponent($('#comment').val()) + '&token=' + static_token ,
            success: function(jsonData)
            {
                if (jsonData.hasError)
                {
                    var errors = '';
                    for(var error in jsonData.errors)
                        //IE6 bug fix
                        if(error !== 'indexOf')
                            errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
                    alert(errors);
                }
            // else
                // $('#opc_delivery_methods-overlay').fadeOut('slow');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                // if (textStatus !== 'abort')
                //     alert("TECHNICAL ERROR: unable to save message \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                // $('#opc_delivery_methods-overlay').fadeOut('slow');
            }
        });
    });

    $('#checkout_form').on('submit', function (e) {
        e.preventDefault()

        $('#opc_form_errors').slideUp('slow')
        $('.form-group').removeClass('form-error')

        var form = $(this)
        var required_fields = ['name', 'phone', 'address1']
        var errors = false

        required_fields.forEach(function (field, i, arr) {
            if (form.find('#' + field).val() == '') {
                errors = true
                $('.form-group-' + field).addClass('form-error')
            }
        })

        if (form.find('input[name=payment_method]:checked').length == 0) {
            $('.form-group-payment').addClass('form-error')
        }

        if (errors) {
            $('#opc_form_errors').slideDown('slow')
            return;
        }

        // Submit user account
        var callingFile = ''
        var params = ''

        if (parseInt($('#opc_id_customer').val()) == 0) {
            callingFile = authenticationUrl
            params = 'submitAccount=true&'
        } else {
            callingFile = orderOpcUrl
            params = 'method=editCustomer&'
        }

        params += form.serialize()

        params += '&customer_firstname='+encodeURIComponent($('#name').val())+'&'

        // Clean the last &
        params = params.substr(0, params.length-1)

        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: callingFile + '?rand=' + new Date().getTime(),
            async: false,
            cache: false,
            dataType : "json",
            data: 'ajax=true&'+params+'&token=' + static_token ,
            success: function(jsonData) {
                if (jsonData.hasError) {
                    var tmp = ''
                    var i = 0
                    for (var error in jsonData.errors)
                        //IE6 bug fix
                        if (error !== 'indexOf') {
                            i = i+1
                            tmp += '<li>'+jsonData.errors[error]+'</li>'
                        }
                    tmp += '</ol>'
                    var errors = '<b>'+txtThereis+' '+txtErrors+':</b><ol>'+tmp
                    $('#opc_account_errors').slideUp('fast', function() {
                        $(this).html(errors).slideDown('slow', function() {
                            $.scrollTo('#opc_account_errors', 800)
                        })
                    })
                } else {
                    $('#opc_account_errors').slideUp('slow', function() {
                        $(this).html('')
                    })
                }

                isGuest = parseInt($('#is_new_customer').val()) == 1 ? 0 : 1
                // update addresses id
                if (jsonData.id_address_delivery !== undefined && jsonData.id_address_delivery > 0)
                    $('#opc_id_address_delivery').val(jsonData.id_address_delivery)
                if (jsonData.id_address_invoice !== undefined && jsonData.id_address_invoice > 0)
                    $('#opc_id_address_invoice').val(jsonData.id_address_invoice)

                if (jsonData.id_customer !== undefined && jsonData.id_customer !== 0 && jsonData.isSaved) {
                    // update token
                    static_token = jsonData.token

                    // It's not a new customer
                    if ($('#opc_id_customer').val() !== '0')
                        if (!saveAddress('delivery'))
                            return false

                    // update id_customer
                    $('#opc_id_customer').val(jsonData.id_customer)

                    // force to refresh carrier list
                    if (isGuest)
                    {
                        isLogged = 1
                        updateAddressSelection(false)
                    }
                    else
                        updateNewAccountToAddressBlock(false)

                    // Proceed form checkout and go to order validation
                    if (form.find('input[name=payment_method]:checked').val()) {
                        document.location.href = form.find('input[name=payment_method]:checked').val()

                        if (typeof yaCounter25186562 !== 'undefined') {
                            yaCounter25186562.reachGoal('NEW_ORDER', yaParams)
                        }
                    }
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus !== 'abort')
                {
                    error = "TECHNICAL ERROR: unable to save account \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus
                    if (!!$.prototype.fancybox)
                        $.fancybox.open([
                            {
                                type: 'inline',
                                autoScale: true,
                                minHeight: 30,
                                content: '<p class="fancybox-error">' + error + '</p>'
                            }
                        ], {
                            padding: 0
                        })
                    else
                        alert(error)
                }
                // $('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeIn('slow')
            }
        })
    })
})
