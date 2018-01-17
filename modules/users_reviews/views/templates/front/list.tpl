{capture name=path}{l s='Users shop reviews' mod='users_reviews'}{/capture}

<div>
    <h1 class="page-heading">{l s='Reviews' mod='users_reviews'}</h1>

    {if isset($reviews) && count($reviews)}
        <div class="reviews block_shadow">
            {foreach from=$reviews item=review}
                {if $review['text']}
                    <div class="review row" itemprop="review" itemscope itemtype="https://schema.org/Review">
                        <meta itemprop="itemReviewed" content="Интернет-магазин спиннингов и катушек SPINKAT.RU">
                        <div class="review_author col-sm-2">
                            {*<span>{l s='Grade' mod='users_reviews'}&nbsp;</span>*}
                            <div class="star_content clearfix" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                                {section name="i" start=0 loop=5 step=1}
                                    {if $review.grade le $smarty.section.i.index}
                                        <div class="star"></div>
                                    {else}
                                        <div class="star star_on"></div>
                                    {/if}
                                {/section}
                                <meta itemprop="worstRating" content="0">
                                <meta itemprop="ratingValue" content="{$review.grade|escape:'html':'UTF-8'}">
                                <meta itemprop="bestRating" content="5">
                            </div>
                            <p itemprop="author">{$review['customer_name']|escape:'html':'UTF-8'|nl2br}</p>
                        </div> <!-- .review_author -->

                        <div class="review_details col-sm-10">
                            <p itemprop="reviewBody">{$review['text']|escape:'html':'UTF-8'|nl2br}</p>
                        </div><!-- .review_details -->

                    </div><!-- .review -->
                {/if}
            {/foreach}
        </div><!-- .reviews -->
    {else}
        <p class="warning alert alert-warning">{l s='There are no reviews yet' mod='users_reviews'}</p>
    {/if}

    {* Add review form *}
    <div id="add-review" class="add-review block_shadow">

        {if isset($logged) AND $logged}
        <form class="std clearfix" action="#add-review" method="post">
            <fieldset>
                    <h3 class="page-subheading">{l s='New review' mod='users_reviews'}</h3>

                    {if $confirmation}
                        <p class="alert alert-success">{l s='Your message has been successfully sent to our team.' mod='users_reviews'}</p>
                    {/if}

                    {if isset($errors) && count($errors)}
                        <div class="alert alert-danger">
                            <ol>
                            {foreach from=$errors key=k item=error}
                                <li>{$error}</li>
                            {/foreach}
                            </ol>
                        </div>
                    {/if}

                    <div class="form-group">
                        <label for="customer_name">{l s='Your name' mod='users_reviews'}</label>
                        <input type="text" class="form-control" name="customer_name" id="customer_name"
                            value="{if isset($post_name)}{$post_name|escape:'html':'UTF-8'}{else}{if isset($logged) AND $logged}{$customerName|escape:'html':'UTF-8'}{/if}{/if}" />
                    </div>
                    <div class="form-group">
                        <label for="email">{l s='Your email' mod='users_reviews'}</label>
                        <input type="text" class="form-control" name="email" id="email"
                            value="{if isset($post_email)}{$post_email|escape:'html':'UTF-8'}{else}{if isset($logged) AND $logged AND isset($email) AND $email}{$email|escape:'html':'UTF-8'}{/if}{/if}" />
                    </div>
                    <div class="form-group">
                        <label>{l s='Grade' mod='users_reviews'}</label>
                        <div class="star_content">
                            <input class="star" type="radio" name="grade" value="1" />
                            <input class="star" type="radio" name="grade" value="2" />
                            <input class="star" type="radio" name="grade" value="3" />
                            <input class="star" type="radio" name="grade" value="4" />
                            <input class="star" type="radio" name="grade" value="5" checked="checked" />
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group">
                        <label for="text">{l s='Review' mod='users_reviews'}</label>
                        <textarea class="form-control" id="text" name="text" cols="26" rows="5">{if isset($post_text)}{$post_text|escape:'html':'UTF-8'}{/if}</textarea>
                    </div>

                    <button type="submit" class="button btn btn-default button-medium" name="submitNewReview" id="submitNewReview">
                        <span>
                            {l s='Add review' mod='users_reviews'}
                        </span>
                    </button>
            </fieldset>
        </form>
        {else}
            <div class="warning alert alert-warning">
                <a href="{$link->getPageLink('authentication', true, null, ['back' => $post->url])|escape:'html':'UTF-8'}">{l s='Only registered and logged customers can add reviews' mod='users_reviews'}</a>
            </div><!-- .warning -->
        {/if}

    </div><!-- #add-review -->
</div>
