<!-- Block payment logo module -->
<div id="payment_logo_block_left" class="payment_logo_block">
    <a class="block_title" href="{$link->getCMSLink($cms_payement_logo)|escape:'html'}">
        {l s='Payment options:' mod='blockpaymentlogo'}
    </a>
    <div class="payment_options">
        <!--noindex-->
        	<a class="option visa" href="{$link->getCMSLink($cms_payement_logo)|escape:'html'}" rel="nofollow"></a>
        	<a class="option mastercard" href="{$link->getCMSLink($cms_payement_logo)|escape:'html'}" rel="nofollow"></a>
        	<!-- <a class="option paypal" href="{$link->getCMSLink($cms_payement_logo)|escape:'html'}"></a> -->
    	<!--/noindex-->
    </div>
</div>
<!-- /Block payment logo module -->
