<?php
class PricesDropController extends PricesDropControllerCore
{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    /*
    * module: ext_sales_page
    * date: 2015-10-31 20:19:30
    * version: 1.1.0
    */
    public function initContent()
    {
        FrontController::initContent();
        $this->productSort();
        $nbProducts = Product::getPricesDrop($this->context->language->id, null, null, true, null, null, false,
            false, null, true);
        $this->pagination($nbProducts);
        $products = Product::getPricesDrop($this->context->language->id, (int)$this->p - 1, (int)$this->n, false,
            $this->orderBy, $this->orderWay, false, false, null, true);
        $this->addColorsToProductList($products);
        $this->context->smarty->assign(array(
            'products' => $products,
            'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
            'nbProducts' => $nbProducts,
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
            'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM')
        ));
        $this->setTemplate(_PS_THEME_DIR_.'prices-drop.tpl');
    }
}
