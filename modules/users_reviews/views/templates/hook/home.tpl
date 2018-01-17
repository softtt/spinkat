<!-- Users reviews -->
{if (isset($reviews) && count($reviews))}
    <div id="reviews-home">
        <div class="home-title">
            <h2 class="title">{l s='Reviews' mod='users_reviews'}</h2>
            <span class="line"></span>
            <span class="page-link">
                <a href="{$link->getPageLink('module-users_reviews-shopreviews')|escape:'html'}" title="{l s='All reviews' mod='users_reviews'}">{l s='All reviews' mod='users_reviews'}</a>
            </span>
        </div>
        <div class="reviews">
            {foreach from=$reviews item=review}
                <div class="review" itemscope itemtype="https://schema.org/Review">
                    <div class="review-title">
                        <h2>{$review['customer_name']}</h2>

                        <div class="star_content review-rating" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                            {section name="i" start=0 loop=5 step=1}
                                {if $review['grade'] le $smarty.section.i.index}
                                    <div class="star"></div>
                                {else}
                                    <div class="star star_on"></div>
                                {/if}
                            {/section}
                            <meta itemprop="worstRating" content="0">
                            <meta itemprop="ratingValue" content="{$review['grade']|escape:'html':'UTF-8'}">
                            <meta itemprop="bestRating" content="5">
                        </div>
                    </div>

                    <div class="review-text">
                        <p itemprop="reviewBody">{$review.text|escape:'html':'UTF-8'|nl2br}</p>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}
<!-- / Users reviews -->
