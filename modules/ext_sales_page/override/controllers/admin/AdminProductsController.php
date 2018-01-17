<?php

class AdminProductsController extends AdminProductsControllerCore
{
    /**
     * @param Product $product
     * @throws Exception
     * @throws SmartyException
     */
    public function initFormInformations($product)
    {
        $product->show_on_sales_page = $this->getFieldValue($product, 'show_on_sales_page');

        parent::initFormInformations($product);
    }

    /**
     * @param Product|ObjectModel $object
     * @param string              $table
     */
    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);

        if ($this->isTabSubmitted('Prices')) {
            $object->show_on_sales_page = (int)Tools::getValue('show_on_sales_page');
        }
    }
}
