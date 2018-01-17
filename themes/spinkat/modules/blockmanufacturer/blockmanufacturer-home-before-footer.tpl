<!-- Block manufacturers before footer -->
{if ($manufacturers && count($manufacturers))}
<div id="manufacturers-block-before-footer">
    <div class="container">
        <span class="slider-prev"></span>
        <div id="manufacturers-block-before-footer-slider">
            {foreach from=$manufacturers item=manufacturer name=manufacturer_list}
                {if $smarty.foreach.manufacturer_list.iteration <= $text_list_nb}
                    <a class="lnk_img"
                        href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'html':'UTF-8'}"
                        title="{$manufacturer.name|escape:'html':'UTF-8'}" >
                            <img src="{$img_manu_dir}{$manufacturer.image|escape:'html':'UTF-8'}-medium_default.jpg" alt="" />
                    </a>
                {/if}
            {/foreach}
        </div><!-- #manufacturers-block-before-footer-slider -->
        <span class="slider-next"></span>
    </div>
</div><!-- #manufacturers-block-before-footer -->
<script type="text/javascript">
    $(function() {
        $('#manufacturers-block-before-footer-slider').bxSlider({
            slideWidth: 155,
            slideMargin: 1,
            minSlides: 1,
            maxSlides: 7,
            pager: false,
            moveSlides: 1,
            auto: true,
            nextSelector: '#manufacturers-block-before-footer .slider-next',
            nextText: '',
            prevSelector: '#manufacturers-block-before-footer .slider-prev',
            prevText: '',
            // infiniteLoop: false,
        });
    });
</script>
{/if}
<!-- /Block manufacturers before footer -->
