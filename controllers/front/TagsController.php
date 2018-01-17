<?php


class TagsController extends FrontController
{
    public $php_self = 'tags';

    public $tag;

    public function init()
    {
        parent::init();
    }

    public function setMedia()
    {
        parent::setMedia();
            $this->addCSS(_THEME_CSS_DIR_.'product_list.css');
            $this->addJqueryPlugin(array('fancybox', 'idTabs', 'scrollTo', 'serialScroll', 'bxslider'));
            $this->addJS(array(
                _THEME_JS_DIR_.'tools.js',  // retro compat themes 1.5
                _THEME_JS_DIR_.'product.js'
            ));
    }

    public function canonicalRedirection($canonical_url = '')
    {
        parent::canonicalRedirection($this->context->link->getTagLink((int)Tools::getValue('id_tag')));
    }

    public function initContent()
    {
        parent::initContent();
        $this->setTemplate(_PS_THEME_DIR_.'tags.tpl');
        $id = (int)Tools::getValue('id_tag');
        $id_lang = Context::getContext()->language->id;
        $tag = new Tag($id);
        $this->tag = $tag;

        $products = Tag::getProductsForFront($id, $id_lang, Context::getContext());

        $models = Tag::getModelsForFront($id_lang, $id, Context::getContext());

        $series = Tag::getSeriesByModels($models, $id_lang, Context::getContext());

        foreach ($series as $serie) {
            $product_ids = [];
            foreach ($products as $product) {
                array_push($product_ids, $product['id_product']);
            }
            if (! in_array($serie['id_product'], $product_ids)) {
                array_push($products, $serie);
            }
        }

        foreach ($products as &$product) {
            $min_price = 0;
            foreach ($models as $model) {
                if ($model['id_product'] == $product['id_product']) {
                    if ($min_price == 0) {
                        $min_price = $model['price'];
                    } elseif($min_price > $model['price']) {
                        $min_price = $model['price'];
                    }
                    $product['price_min'] = $min_price;
                }
            }
            $product['link'] = $product['link'].'?id_tag='.$id;
        }

        $this->context->smarty->assign(array(
            'body_classes' => array($this->php_self.'-'.$tag->id, $this->php_self.'-'.$tag->link_rewrite),
            'tag' => $tag,
            'products' => $products,
            'allow_tags' => true
        ));
    }
}

