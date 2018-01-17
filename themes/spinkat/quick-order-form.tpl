<div class="block_shadow order_form_container">
    <div class="order_form_block">
        <h2>{l s='Order form'}</h2>

        <div id="opc_form_errors" class="alert alert-danger" style="display:none;">
            Не заполнены обязательные поля
        </div>
        <div id="opc_account_errors" class="alert alert-danger" style="display:none;"></div>

        <form action="post" id="checkout_form">
            <fieldset>
                <input type="hidden" name="is_new_customer" id="is_new_customer" value="0">
                <input type="hidden" id="opc_id_customer" name="opc_id_customer" value="{if isset($guestInformations) && isset($guestInformations.id_customer) && $guestInformations.id_customer}{$guestInformations.id_customer}{else}0{/if}" />
                <input type="hidden" name="id_country" id="id_country" value="{$sl_country}">
                <input type="hidden" name="opc_id_address_delivery" id="opc_id_address_delivery" value="{if isset($guestInformations) && isset($guestInformations.id_address_delivery) && $guestInformations.id_address_delivery}{$guestInformations.id_address_delivery}{else}0{/if}" />
                <input type="hidden" name="opc_id_address_invoice" id="opc_id_address_invoice" value="{if isset($guestInformations) && isset($guestInformations.id_address_delivery) && $guestInformations.id_address_delivery}{$guestInformations.id_address_delivery}{else}0{/if}" />

                <div class="required form-group form-group-name">
                    <label for="name"><sup>*</sup>{l s='Name'}</label>
                    <input type="text" name="name" class="form-control" id="name" value="{if isset($guestInformations) && isset($guestInformations.customer_firstname) && $guestInformations.customer_firstname}{$guestInformations.customer_firstname}{/if}">
                </div>
                <div class="form-group form-group-email">
                    <label for="email">{l s='E-mail'}</label>
                    <input type="text" name="email" class="text form-control" id="email"  value="{if isset($guestInformations) && isset($guestInformations.email) && $guestInformations.email}{$guestInformations.email}{/if}">
                </div>
                <div class="required form-group form-group-phone">
                    <label for="phone"><sup>*</sup>{l s='Phone'}</label>
                    <input type="text" name="phone" class="text form-control" id="phone" value="{if isset($guestInformations) && isset($guestInformations.phone) && $guestInformations.phone}{$guestInformations.phone}{/if}">
                </div>

                <div class="required form-group clearfix form-group-payment">
                    <label><sup>*</sup>{l s='Payment method'}</label>
                    {if $HOOK_PAYMENT}
                        <div id="HOOK_PAYMENT">
                            {$HOOK_PAYMENT}
                        </div>
                    {/if}
                </div>

                <div class="required form-group form-group-address1">
                    <label for="address1"><sup>*</sup>{l s='Address'}</label>
                    <textarea name="address1" class="form-control" id="address1" rows="4">{if isset($guestInformations) && isset($guestInformations.address1) && isset($guestInformations) && isset($guestInformations.address1) && $guestInformations.address1}{$guestInformations.address1}{/if}</textarea>
                </div>
                <div class="required form-group">
                    <label for="comment">{l s='Comment'}</label>
                    <textarea name="comment" class="form-control" id="comment" rows="4"></textarea>
                </div>


                <div class="form-group order_checkout_button">
                    <label></label>
                    <button type="submit" class="btn btn-default button button-medium"><span>{l s='Checkout'}</span></button>
                </div>
            </fieldset>
        </form>
    </div>

    <div class="additional_info_block">
        <p>
            <strong>Обратите внимание!</strong>
            <br>
            Поля, помеченные звездочкой <sup>*</sup>, обязательны для заполнения.
        </p>

        <p>Наш магазин обязуется сохранять конфиденциальность указанной Вами информации и не передавать ее третьим лицам. Вся информация, которую Вы укажете при регистрации, будет храниться в защищенной базе данных. Доступ к этой информации будут иметь только лица непосредственно работающие с Вашим заказом.</p>

        <p>
            <strong>Внимание!</strong>
            <br>
            Пожалуйста, перед тем как продолжить оформление заказа, проверьте правильно ли Вы указали номер контактного телефона. При неправильно указанном номере или его отсутствии мы не сможем выполнить Ваш заказ.
        </p>
    </div>
</div>
