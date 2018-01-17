{if $DATA_ORG && $DATA_ORG['YA_ORG_ACTIVE'] && $summ >= $DATA_ORG['YA_ORG_MIN']}
    <div class="payment-input-block clearfix">
        <input id="payment_method_yandex_kassa" type="radio" name="payment_method" value="{$link->getModuleLink('yamodule', 'redirectk', [], true)|escape:'quotes':'UTF-8'}">
        <label for="payment_method_yandex_kassa" class="payment-label">
            {l s='Оплата на стороне сервиса Яндекс.Касса' mod='yamodule'}
        </label>
    </div>
{/if}
