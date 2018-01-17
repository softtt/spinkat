<?php
/**
 * @property Manufacturer $object
 */
class AdminManufacturersController extends AdminManufacturersControllerCore
{
    public function renderFormAddress()
    {
        // Change table and className for addresses
        $this->table = 'address';
        $this->className = 'Address';
        $id_address = Tools::getValue('id_address');

        // Create Object Address
        $address = new Address($id_address);

        $res = $address->getFieldsRequiredDatabase();
        $required_fields = array();
        foreach ($res as $row) {
            $required_fields[(int)$row['id_required_field']] = $row['field_name'];
        }

        $form = array(
            'legend' => array(
                'title' => $this->l('Addresses'),
                'icon' => 'icon-building'
            )
        );

        if (!$address->id_manufacturer || !Manufacturer::manufacturerExists($address->id_manufacturer)) {
            $form['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Choose the manufacturer'),
                'name' => 'id_manufacturer',
                'options' => array(
                    'query' => Manufacturer::getManufacturers(),
                    'id' => 'id_manufacturer',
                    'name' => 'name'
                )
            );
        } else {
            $form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Manufacturer'),
                'name' => 'name',
                'col' => 4,
                'disabled' => true,
            );

            $form['input'][] = array(
                'type' => 'hidden',
                'name' => 'id_manufacturer'
            );
        }

        $form['input'][] = array(
            'type' => 'hidden',
            'name' => 'alias',
        );

        $form['input'][] = array(
            'type' => 'hidden',
            'name' => 'id_address',
        );

        if (in_array('company', $required_fields)) {
            $form['input'][] = array(
                'type' => 'text',
                'label' => $this->l('Company'),
                'name' => 'company',
                'display' => in_array('company', $required_fields),
                'required' => in_array('company', $required_fields),
                'maxlength' => 16,
                'col' => 4,
                'hint' => $this->l('Company name for this supplier')
            );
        }

        $form['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Country'),
            'name' => 'id_country',
            'required' => false,
            'default_value' => (int)$this->context->country->id,
            'col' => 4,
            'options' => array(
                'query' => Country::getCountries($this->context->language->id),
                'id' => 'id_country',
                'name' => 'name',
            )
        );
        $form['input'][] = array(
            'type' => 'select',
            'label' => $this->l('State'),
            'name' => 'id_state',
            'required' => false,
            'col' => 4,
            'options' => array(
                'query' => array(),
                'id' => 'id_state',
                'name' => 'name'
            )
        );

        $form['input'][] = array(
            'type' => 'textarea',
            'label' => $this->l('Other'),
            'name' => 'other',
            'required' => false,
            'hint' => $this->l('Forbidden characters:').' &lt;&gt;;=#{}',
            'rows' => 2,
            'cols' => 10,
            'col' => 6,
        );
        $form['submit'] = array(
            'title' => $this->l('Save'),
        );

        $this->fields_value = array(
            'name' => Manufacturer::getNameById($address->id_manufacturer),
            'alias' => 'manufacturer',
            'id_country' => $address->id_country
        );

        $this->initToolbar();
        $this->fields_form[0]['form'] = $form;
        $this->getlanguages();
        $helper = new HelperForm();
        $helper->show_cancel_button = true;

        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (empty($back)) {
            $back = self::$currentIndex.'&token='.$this->token;
        }
        if (!Validate::isCleanHtml($back)) {
            die(Tools::displayError());
        }

        $helper->back_url = $back;
        $helper->currentIndex = self::$currentIndex;
        $helper->token = $this->token;
        $helper->table = $this->table;
        $helper->identifier = $this->identifier;
        $helper->title = $this->l('Edit Addresses');
        $helper->id = $address->id;
        $helper->toolbar_scroll = true;
        $helper->languages = $this->_languages;
        $helper->default_form_language = $this->default_form_language;
        $helper->allow_employee_form_lang = $this->allow_employee_form_lang;
        $helper->fields_value = $this->getFieldsValue($address);
        $helper->toolbar_btn = $this->toolbar_btn;
        $this->content .= $helper->generateForm($this->fields_form);
    }

    public function renderForm()
    {
        if (!($manufacturer = $this->loadObject(true))) {
            return;
        }

        $image = _PS_MANU_IMG_DIR_.$manufacturer->id.'.jpg';
        $image_url = ImageManager::thumbnail($image, $this->table.'_'.(int)$manufacturer->id.'.'.$this->imageType, 350,
            $this->imageType, true, true);
        $image_size = file_exists($image) ? filesize($image) / 1000 : false;

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Manufacturers'),
                'icon' => 'icon-certificate'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => 'Заголовок H1',
                    'name' => 'title_for_seo_h1',
                    'lang' => false,
                    'required' => false,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Short description'),
                    'name' => 'short_description',
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 10,
                    'autoload_rte' => 'rte', //Enable TinyMCE editor for short description
                    'col' => 6,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 10,
                    'col' => 6,
                    'autoload_rte' => 'rte', //Enable TinyMCE editor for description
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Logo'),
                    'name' => 'logo',
                    'image' => $image_url ? $image_url : false,
                    'size' => $image_size,
                    'display_image' => true,
                    'col' => 6,
                    'hint' => $this->l('Upload a manufacturer logo from your computer.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta title'),
                    'name' => 'meta_title',
                    'lang' => true,
                    'col' => 4,
                    'hint' => $this->l('Forbidden characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta description'),
                    'name' => 'meta_description',
                    'lang' => true,
                    'col' => 6,
                    'hint' => $this->l('Forbidden characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->l('Meta keywords'),
                    'name' => 'meta_keywords',
                    'lang' => true,
                    'col' => 6,
                    'hint' => array(
                        $this->l('Forbidden characters:').' &lt;&gt;;=#{}',
                        $this->l('To add "tags," click inside the field, write something, and then press "Enter."')
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                )
            )
        );

        if (!($manufacturer = $this->loadObject(true))) {
            return;
        }

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->fields_form['submit'] = array(
            'title' => $this->l('Save')
        );

        foreach ($this->_languages as $language) {
            $this->fields_value['short_description_'.$language['id_lang']] = htmlentities(stripslashes($this->getFieldValue(
                $manufacturer,
                'short_description',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');

            $this->fields_value['description_'.$language['id_lang']] = htmlentities(stripslashes($this->getFieldValue(
                $manufacturer,
                'description',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }

        return AdminController::renderForm();
    }

}
