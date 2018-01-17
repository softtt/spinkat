<?php

class ManufacturerController extends ManufacturerControllerCore
{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->context->smarty->assign([
            'allow_tags' => true
        ]);

        parent::initContent();
    }

    protected function assignOne()
    {
        // $this->manufacturer->description = Tools::nl2br(trim($this->manufacturer->description));
        $nbProducts = $this->manufacturer->getProducts($this->manufacturer->id, null, null, null, $this->orderBy, $this->orderWay, true);
        $this->pagination((int)$nbProducts);

        $products = $this->manufacturer->getProducts($this->manufacturer->id, $this->context->language->id, (int)$this->p, (int)$this->n, $this->orderBy, $this->orderWay);
        $this->addColorsToProductList($products);

        $this->context->smarty->assign(array(
            'nb_products' => $nbProducts,
            'products' => $products,
            'path' => ($this->manufacturer->active ? Tools::safeOutput($this->manufacturer->name) : ''),
            'manufacturer' => $this->manufacturer,
            'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM'),
            'body_classes' => array($this->php_self.'-'.$this->manufacturer->id, $this->php_self.'-'.$this->manufacturer->link_rewrite)
        ));
    }
}
