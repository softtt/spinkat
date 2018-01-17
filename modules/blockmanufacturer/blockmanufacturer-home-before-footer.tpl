<!-- Block manufacturers before footer -->
{if ($manufacturers && count($manufacturers))}
<div id="manufacturers_block_before_footer">
    {foreach from=$manufacturers item=manufacturer name=manufacturer_list}
        {if $smarty.foreach.manufacturer_list.iteration <= $text_list_nb}
            <a class="lnk_img"
                href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'html':'UTF-8'}"
                title="{$manufacturer.name|escape:'html':'UTF-8'}" >
                    <img src="{$img_manu_dir}{$manufacturer.image|escape:'html':'UTF-8'}-medium_default.jpg" alt="" />
            </a>
        {/if}
    {/foreach}
</div>
<script type="text/javascript">
    $(function() {
        $('#manufacturers_block_before_footer').bxSlider({
            slideWidth: 160,
            slideMargin: 0,
            minSlides: 1,
            maxSlides: 12,
            pager: false,
            moveSlides: 1,
            auto: true,
            // infiniteLoop: false,
            // nextSelector: '.slider-next',
            // prevSelector: '.slider-prev',
            // slideSelector: 'li.product-slide',
        });
    });
</script>
{/if}
<!-- /Block manufacturers before footer -->
