{if isset($delivery_option_list)}
    {foreach $delivery_option_list as $id_address => $option_list}
    <div class="delivery_options">
        {foreach $option_list as $key => $option}
            <div class="delivery_option {if ($option@index % 2)}alternate_{/if}item">
                <input id="delivery_option_{$id_address|intval}_{$option@index}" class="delivery_option_radio" type="radio" name="delivery_option[{$id_address|intval}]" data-key="{$key}" data-id_address="{$id_address|intval}" value="{$key}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if} />
                {if $option.unique_carrier}
                    {foreach $option.carrier_list as $carrier}
                        <label for="delivery_option_{$id_address|intval}_{$option@index}" class="carrier_title">{$carrier.instance->name|escape:'htmlall':'UTF-8'}</label>
                    {/foreach}
                {/if}
            </div> <!-- end delivery_option -->
        {/foreach}
    </div> <!-- end delivery_options -->

    <div class="hook_extracarrier" id="HOOK_EXTRACARRIER_{$id_address}">
        {if isset($HOOK_EXTRACARRIER_ADDR) &&  isset($HOOK_EXTRACARRIER_ADDR.$id_address)}{$HOOK_EXTRACARRIER_ADDR.$id_address}{/if}
    </div>

    {foreachelse}
        {assign var='errors' value=' '|explode:''}
        <p class="alert alert-warning" id="noCarrierWarning">
            {foreach $cart->getDeliveryAddressesWithoutCarriers(true, $errors) as $address}
                {if empty($address->alias)}
                    {l s='No carriers available.'}
                {else}
                    {assign var='flag_error_message' value=false}
                    {foreach $errors as $error}
                        {if $error == Carrier::SHIPPING_WEIGHT_EXCEPTION}
                            {$flag_error_message = true}
                            {l s='The product selection cannot be delivered by the available carrier(s): it is too heavy. Please amend your cart to lower its weight.'}
                        {elseif $error == Carrier::SHIPPING_PRICE_EXCEPTION}
                            {$flag_error_message = true}
                            {l s='The product selection cannot be delivered by the available carrier(s). Please amend your cart.'}
                        {elseif $error == Carrier::SHIPPING_SIZE_EXCEPTION}
                            {$flag_error_message = true}
                            {l s='The product selection cannot be delivered by the available carrier(s): its size does not fit. Please amend your cart to reduce its size.'}
                        {/if}
                    {/foreach}
                    {if !$flag_error_message}
                        {l s='No carriers available for the address "%s".' sprintf=$address->alias}
                    {/if}
                {/if}
                {if !$address@last}
                    <br />
                {/if}
            {foreachelse}
                {l s='No carriers available.'}
            {/foreach}
        </p>
    {/foreach}
{/if}
