{*
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA

* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
{if isset($best_sellers) && $best_sellers}
    <div id="blockbestsellers-home">
        <div class="home-title">
            <h2 class="title">{l s='Best sellers' mod='blockbestsellers'}</h2>
            <span class="line"></span>
            <span class="slider-nav">
                <span class="slider-prev"></span>
                <span class="slider-next"></span>
            </span>
            <span class="page-link">
                <a href="{$link->getPageLink('best-sales')|escape:'html'}"
                    title="{l s='All best sellers' mod='blockbestsellers'}">
                    {l s='All best sellers' mod='blockbestsellers'}
                </a>
            </span>
        </div>
        {include file="$tpl_dir./product-list.tpl" products=$best_sellers class='blockbestsellers tab-pane' id='blockbestsellers' slider=1}

        <script type="text/javascript">
            $(function() {
                initBestsellersSlider();
            });

            function initBestsellersSlider() {
                var blockbestsellers_slider = $('#blockbestsellers-home #blockbestsellers').bxSlider({
                    pager: false,
                    nextText: '',
                    prevText: '',
                    // nextSelector: '#blockbestsellers-home .slider-next',
                    // prevSelector: '#blockbestsellers-home .slider-prev',
                });

                var options = {
                    slideWidth: 195,
                    slideMargin: 0,
                    minSlides: 1,
                    maxSlides: 5,
                    pager: false,
                    moveSlides: 1,
                    auto: true,
                    infiniteLoop: false,
                    nextText: '',
                    prevText: '',
                    // nextSelector: '#blockbestsellers-home .slider-next',
                    // prevSelector: '#blockbestsellers-home .slider-prev',
                };

                initSliders();
                $(window).resize(initSliders);

                function initSliders() {
                    if (($(window).width() + scrollCompensate()) < 768)
                        options.slideWidth = $(window).width();
                    else
                        options.slideWidth = 195;

                    if (typeof blockbestsellers_slider.reloadSlider === 'function') {
                        options.nextSelector = '#blockbestsellers-home .slider-next';
                        options.prevSelector = '#blockbestsellers-home .slider-prev';
                        blockbestsellers_slider.reloadSlider(options);

                        $('#blockbestsellers-home .bx-controls').hide();
                    }
                }

                // $('#blockbestsellers-home #blockbestsellers').bxSlider(options);
            }
        </script>
    </div>
    <div class="clearfix"></div>
{/if}
