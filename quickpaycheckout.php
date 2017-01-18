<?php
/**
* 2016 Quickpay
* 
* NOTICE OF LICENSE
* 
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
*  @author    Binary Limited <info@binary.co.ke>
*  @copyright 2016 Binary Limited
*  @license   http://www.gnu.org/licenses
*/

class QuickpayCheckout extends PaymentModule
{
    private $icon_name;

    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }
        $this->name                   = 'quickpaycheckout';
        $this->tab                    = 'payments_gateways';
        $this->version                = '1.0.0';
        $this->author                 = 'Binary Limited';
        $this->need_instance          = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap              = true;
        $this->module_key             = 'ebaa835946fbc022315133fd7e2d44dd';

        parent::__construct();

        $this->displayName = $this->l('Quickpay Gateway');
        $this->description = $this->l('Quickpay helps you to process card payments quickly and securely');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

//        if (!Configuration::get('MYMODULE_NAME'))
//        {
//            $this->warning = $this->l('No name provided');
//        }
    }

    public function processFileUpload()
    {
        if (
            isset(
                $_FILES['QUICKPAYCHECKOUT_ICON'])
                && isset($_FILES['QUICKPAYCHECKOUT_ICON']['tmp_name'])
                    && !empty($_FILES['QUICKPAYCHECKOUT_ICON']['tmp_name']
            )
        ) {
            $error = ImageManager::validateUpload(
                $_FILES['QUICKPAYCHECKOUT_ICON'],
                Tools::convertBytes(ini_get('upload_max_filesize'))
            );
            if ($error) {
                return $error;
            }
            //clear previous file
            $base_url = _PS_MODULE_DIR_.'quickpaycheckout/views/img/';
            $prev_url = $base_url.Configuration::get('QUICKPAYCHECKOUT_ICON');
            if (file_exists($prev_url)) {
                unlink($prev_url);
            }
            Configuration::updateValue('QUICKPAYCHECKOUT_ICON', $_FILES['QUICKPAYCHECKOUT_ICON']['name']);
            // Copy th image in the module directory with its new name
            if (
                !move_uploaded_file(
                    $_FILES['QUICKPAYCHECKOUT_ICON']['tmp_name'],
                    $base_url.Configuration::get('QUICKPAYCHECKOUT_ICON')
                )
            ) {
                return $this->l('File upload error.');
            }
        }
        return true;
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $qp_name        = (string) (Tools::getValue('QUICKPAYCHECKOUT_NAME'));
            $qp_public_key  = (string) (Tools::getValue('QUICKPAYCHECKOUT_PUBLIC_KEY'));
            $qp_private_key = (string) (Tools::getValue('QUICKPAYCHECKOUT_PRIVATE_KEY'));
            $qp_button_text = (string) (Tools::getValue('QUICKPAYCHECKOUT_BUTTON_TEXT'));
            $qp_desc        = (string) (Tools::getValue('QUICKPAYCHECKOUT_DESC'));
            $qp_env         = (int) Tools::getValue('QUICKPAYCHECKOUT_ENVIRONMENT');
            //handle file upload
            $file_upload    = $this->processFileUpload();
            if ($file_upload !== true) {
                $output .= $this->displayError($file_upload);
            } elseif (!$qp_name || empty($qp_name) || !Validate::isGenericName($qp_name)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } elseif (!$qp_public_key || empty($qp_public_key) || !Validate::isGenericName($qp_public_key)) {
                $output .= $this->displayError($this->l('Public Key is Required'));
            } elseif (
                !$qp_button_text ||
                empty($qp_button_text) ||
                !Validate::isGenericName($qp_button_text) ||
                (Tools::strlen($qp_desc) > 15)
            ) {
                $output .= $this->displayError(
                    $this->l('Button Text is required and should be less than 15 characters')
                );
            } elseif (
                !$qp_desc ||
                empty($qp_desc) ||
                !Validate::isGenericName($qp_desc) ||
                (Tools::strlen($qp_desc) > 30)
            ) {
                $output .= $this->displayError(
                    $this->l('Merchant description is required and should be less than 30 characters')
                );
            } elseif (!$qp_private_key || empty($qp_private_key) || !Validate::isGenericName($qp_private_key)) {
                $output .= $this->displayError($this->l('Private key is required'));
            } else {
                Configuration::updateValue('QUICKPAYCHECKOUT_NAME', $qp_name);
                Configuration::updateValue('QUICKPAYCHECKOUT_PUBLIC_KEY', $qp_public_key);
                Configuration::updateValue('QUICKPAYCHECKOUT_PRIVATE_KEY', $qp_private_key);
                Configuration::updateValue('QUICKPAYCHECKOUT_DESC', $qp_desc);
                Configuration::updateValue('QUICKPAYCHECKOUT_BUTTON_TEXT', $qp_button_text);
                Configuration::updateValue('QUICKPAYCHECKOUT_ENVIRONMENT', $qp_env);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $default_lang   = (int) Configuration::get('PS_LANG_DEFAULT');
        $fields_form = array();
        // Init Fields form array
        $fields_form[0] = array('form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live'),
                        'name' => 'QUICKPAYCHECKOUT_ENVIRONMENT',
                        'desc' => $this->l('Switch between live and test environment'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'qp-environment-live',
                                'value' => 1,
                                'label' => $this->l('Live')
                            ),
                            array(
                                'id' => 'qp-environment-test',
                                'value' => 0,
                                'label' => $this->l('Test')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant Name'),
                        'name' => 'QUICKPAYCHECKOUT_NAME',
                        'size' => 20,
                        'required' => true,
                        'desc' => 'Merchant name registered with Quickpay'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant Description'),
                        'name' => 'QUICKPAYCHECKOUT_DESC',
                        'size' => 30,
                        'required' => true,
                        'desc' => 'Brief Merchant Description'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Public Key'),
                        'name' => 'QUICKPAYCHECKOUT_PUBLIC_KEY',
                        'size' => 200,
                        'required' => true,
                        'desc' => 'Public Key issued by Quickpay'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Private Key'),
                        'name' => 'QUICKPAYCHECKOUT_PRIVATE_KEY',
                        'size' => 200,
                        'required' => true,
                        'desc' => 'Private Key issued by Quickpay'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Payment Button Text'),
                        'name' => 'QUICKPAYCHECKOUT_BUTTON_TEXT',
                        'size' => 20,
                        'required' => true,
                        'desc' => 'Payment Button Text'
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Icon'),
                        'name' => 'QUICKPAYCHECKOUT_ICON',
                        'desc' => $this->l('Payment icon to be displayed on checkout window'),
                        'thumb' => $this->context->link->protocol_content.$this->icon_name,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                )
        ));

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language    = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar

        $helper->title          = $this->displayName;
        $helper->show_toolbar   = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action  = 'submit'.$this->name;
        $helper->toolbar_btn    = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['QUICKPAYCHECKOUT_ENVIRONMENT'] = (int) Tools::getValue(
            'QUICKPAYCHECKOUT_ENVIRONMENT',
            Configuration::get('QUICKPAYCHECKOUT_ENVIRONMENT')
        );
        $helper->fields_value['QUICKPAYCHECKOUT_PUBLIC_KEY']  = Configuration::get('QUICKPAYCHECKOUT_PUBLIC_KEY');
        $helper->fields_value['QUICKPAYCHECKOUT_NAME']        = Configuration::get('QUICKPAYCHECKOUT_NAME');
        $helper->fields_value['QUICKPAYCHECKOUT_DESC']        = (Configuration::get('QUICKPAYCHECKOUT_DESC')
            == '') ? Configuration::get('PS_SHOP_NAME') : Configuration::get('QUICKPAYCHECKOUT_DESC');

        $helper->fields_value['QUICKPAYCHECKOUT_PUBLIC_KEY']  = Configuration::get('QUICKPAYCHECKOUT_PUBLIC_KEY');
        $helper->fields_value['QUICKPAYCHECKOUT_PRIVATE_KEY'] = Configuration::get('QUICKPAYCHECKOUT_PRIVATE_KEY');
        $helper->fields_value['QUICKPAYCHECKOUT_BUTTON_TEXT'] = Configuration::get('QUICKPAYCHECKOUT_BUTTON_TEXT');

        return $helper->generateForm($fields_form);
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        foreach ($params['cart']->getProducts() as $product) {
            $pd = ProductDownload::getIdFromIdProduct((int) ($product['id_product']));
            if ($pd and Validate::isUnsignedInt($pd)) {
                return false;
            }
        }

        //$address = new Address((int) $this->context->cart->id_address_delivery);
        //$country = new Country($address->id_country);
        //$countryCode = $country->iso_code;

        $this->context->smarty->assign(array(
            // 'this_path' 		=> $this->modules_dir,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {

        if (!$this->active) {
            return;
        }

        $order = $params['objOrder'];
        if ($order->getCurrentState() == Configuration::get('PS_OS_PAYMENT')) {
            $status = 2;
        } else {
            $status = 8;
        }
        $this->context->smarty->assign(array(
            "status" => $status
        ));
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookdisplayPaymentTop()
    {
        if (!$this->active || !Tools::getValue('quickpaycheckout_error')) {
            return;
        }
        $this->context->smarty->assign(array(
            "error" => $this->l(Tools::getValue('quickpaycheckout_error'))
        ));
        return $this->display(__FILE__, 'payment_error.tpl');
    }

    //To be implemented in the next release
//    public function hookdisplayAdminOrder($params){
//        if(!$this->active){
//            return;
//        }
//        return $this->display(__FILE__, 'admin_order.tpl');
//    }

    public function install()
    {
        if (is_null($this->warning) && !function_exists('curl_init')) {
            if ($this->l('ERROR_MESSAGE_CURL_REQUIRED') == "ERROR_MESSAGE_CURL_REQUIRED") {
                $this->warning = "cURL is required to use this module. Please install the php extention cURL.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_CURL_REQUIRED');
            }
        }
        if (is_null($this->warning) && !(parent::install() && $this->registerHook('payment')
            && $this->registerHook('paymentReturn') && $this->registerHook('updateOrderStatus')
            && $this->registerHook('displayInvoice') && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayPaymentTop'))) {
            if ($this->l('ERROR_MESSAGE_INSTALL_MODULE') == "ERROR_MESSAGE_INSTALL_MODULE") {
                $this->warning = "There was an Error installing the module.";
            } else {
                $this->warning = $this->l('ERROR_MESSAGE_INSTALL_MODULE');
            }
        }
        //intialize defaults settings
        Configuration::updateValue('QUICKPAYCHECKOUT_NAME', Configuration::get('PS_SHOP_NAME'));
        Configuration::updateValue('QUICKPAYCHECKOUT_ENVIRONMENT', 1);
        Configuration::updateValue('QUICKPAYCHECKOUT_DESC', '');
        Configuration::updateValue('QUICKPAYCHECKOUT_BUTTON_TEXT', 'Checkout');
        return is_null($this->warning);
    }

    public function uninstall()
    {
        if (
            !Configuration::deleteByName('QUICKPAYCHECKOUT_PRIVATE_KEY')
            || !Configuration::deleteByName('QUICKPAYCHECKOUT_ICON')
            || !Configuration::deleteByName('QUICKPAYCHECKOUT_BUTTON_TEXT')
            || !Configuration::deleteByName('QUICKPAYCHECKOUT_PUBLIC_KEY')
            || !Configuration::deleteByName('QUICKPAYCHECKOUT_ENVIRONMENT')
            || !Configuration::deleteByName('QUICKPAYCHECKOUT_DESC')
            || !Configuration::deleteByName('QUICKPAYCHECKOUT_NAME')
            || !$this->unregisterHook('payment')
            || !$this->unregisterHook('paymentReturn')
            || !$this->unregisterHook('updateOrderStatus')
            || !$this->unregisterHook('displayInvoice')
            || !$this->unregisterHook('displayAdminOrder')
            || !$this->unregisterHook('displayPaymentTop')
            || !parent::uninstall()
            ) {
            return false;
        }
        return true;
    }
}
