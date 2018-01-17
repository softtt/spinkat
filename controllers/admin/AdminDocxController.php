<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminDocxControllerCore extends AdminController
{
    public function postProcess()
    {
        parent::postProcess();

        // We want to be sure that displaying PDF is the last thing this controller will do
        exit;
    }

    public function initProcess()
    {
        parent::initProcess();
        $access = Profile::getProfileAccess($this->context->employee->id_profile, (int)Tab::getIdFromClassName('AdminOrders'));
        if ($access['view'] === '1' && ($action = Tools::getValue('submitAction'))) {
            $this->action = $action;
        } else {
            $this->errors[] = Tools::displayError('You do not have permission to view this.');
        }
    }

    public function processGenerateInvoiceDocx()
    {
        if (Tools::isSubmit('id_order')) {
            $order = new Order((int)Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($order)) {
                die(Tools::displayError('The order cannot be found within your database.'));
            }
            $docx = new DocxGenerator($order);
            $docx->make();
        } else {
            die(Tools::displayError('The order ID -- is missing.'));
        }
    }
}
