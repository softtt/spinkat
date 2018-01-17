{if $DATA_ORG && $DATA_ORG['YA_ORG_ACTIVE'] && $summ >= $DATA_ORG['YA_ORG_MIN']}
    <div class="payment-input-block clearfix">
        <input id="payment_method_yandex_kassa_{$pt|escape:'htmlall':'UTF-8'}" type="radio" name="payment_method" value="{$link->getModuleLink('yamodule', 'redirectk', ['type' => {$pt|escape:'htmlall':'UTF-8'}], true)|escape:'quotes':'UTF-8'}">
        <label for="payment_method_yandex_kassa_{$pt|escape:'htmlall':'UTF-8'}" class="payment-label">
            {$buttontext|escape:'htmlall':'UTF-8'}
        </label>
    </div>
{/if}
