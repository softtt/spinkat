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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !isset($content_only) || !$content_only}
                    </div><!-- #center_column -->
                    {if isset($right_column_size) && !empty($right_column_size)}
                        <div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} right_column column">{$HOOK_RIGHT_COLUMN}</div>
                    {/if}


                    </div><!-- .row -->
                </div><!-- #columns -->
            </div><!-- .columns-container -->

            <div id="before-footer">
                {if $page_name == 'category' && isset($category) && $category->description}
                    <div class="container category-desc">
                        <div class="text-block">{$category->description}</div>
                    </div>
                {/if}
                {hook h='displayAfterColumnsBeforeFooter'}
            </div>

            {if isset($HOOK_FOOTER)}
                <!-- Footer -->
                <div class="footer-container">
                    <footer id="footer">
                        <div class="footer-top">
                            <div class="container">
                                <div class="top-footer-1">{hook h='displayTopFooter1'}</div>
                                <div class="top-footer-2">{hook h='displayTopFooter2'}</div>
                                <div class="top-footer-3">{hook h='displayTopFooter3'}</div>
                            </div>
                        </div>
                        <div class="footer-bottom">
                            <div class="container">
                                <div class="copyright">
                                    {l s='Copyright © '}{'Y'|date}<br>
                                    {l s='Спиннинги и катушки'|escape:'html'}<br>
                                    {l s='Все права защищены'|escape:'html'}
                                </div>
                                {*
                                <div class="developed-by">
                                    {l s='Разработка сайта'|escape:'html'}<br>
                                    <a target="_blank" rel="nofollow" href="#">Victor Scherba</a>
                                </div>
                                *}
                                <div class="hook-footer">{$HOOK_FOOTER}</div>
                                <div class="banners"></div>
                            </div>
                        </div>
                    </footer>
                </div><!-- #footer -->
            {/if}
        </div><!-- #page -->
{/if}

        {include file="$tpl_dir./global.tpl"}

        {if $smarty.server.HTTP_HOST == 'spinkat.ru'}
            {literal}
                <!-- Yandex.Metrika counter -->
                <script type="text/javascript">
                    var yaParams = {};

                    (function (d, w, c) {
                        (w[c] = w[c] || []).push(function() {
                            try {
                                w.yaCounter25186562 = new Ya.Metrika({
                                    id:25186562,
                                    clickmap:true,
                                    trackLinks:true,
                                    accurateTrackBounce:true,
                                    webvisor:true
                                });
                            } catch(e) { }
                        });

                        var n = d.getElementsByTagName("script")[0],
                            s = d.createElement("script"),
                            f = function () { n.parentNode.insertBefore(s, n); };
                        s.type = "text/javascript";
                        s.async = true;
                        s.src = "https://mc.yandex.ru/metrika/watch.js";

                        if (w.opera == "[object Opera]") {
                            d.addEventListener("DOMContentLoaded", f, false);
                        } else { f(); }
                    })(document, window, "yandex_metrika_callbacks");
                </script>
                <noscript><div><img src="https://mc.yandex.ru/watch/25186562" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
                <!-- /Yandex.Metrika counter -->

                <!-- Google analitycs -->
                <script>
                    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                    ga('create', 'UA-40873556-1', 'spinkat.ru');
                    ga('send', 'pageview');
                </script>
                <!-- /Google analitycs -->

                <script type='text/javascript'>
                    // window['li'+'v'+'eT'+'e'+'x'] = true,
                    // window['live'+'TexI'+'D'] = 119006,
                    // window['liveT'+'ex_ob'+'jec'+'t'] = true;
                    // (function() {
                    //     var t = document['cre'+'a'+'teElem'+'e'+'nt']('script');
                    //     t.type ='text/javascript';
                    //     t.async = true;
                    //     t.src = '//cs'+'15'+'.l'+'ivete'+'x.'+'ru'+'/js'+'/clie'+'nt.js';
                    //     var c = document['getElemen'+'tsByTag'+'Na'+'me']('script')[0];
                    //     if ( c ) c['p'+'ar'+'en'+'t'+'Nod'+'e']['i'+'nsertB'+'efore'](t, c);
                    //     else document['docume'+'n'+'tElemen'+'t']['firs'+'t'+'Ch'+'ild']['app'+'en'+'dCh'+'ild'](t);
                    // })();
                </script>

            {/literal}
        {/if}
    </body>
</html>
