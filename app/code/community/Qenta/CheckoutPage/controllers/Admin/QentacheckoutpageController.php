<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by QENTA Payment CEE GmbH
 * (abbreviated to Qenta CEE) and are explicitly not part of the Qenta CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Qenta CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Qenta CEE does not guarantee their full
 * functionality neither does Qenta CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Qenta CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

class Qenta_CheckoutPage_Admin_QentacheckoutpageController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action, implicit target for back button
     * redirect to config
     */
    public function indexAction()
    {
        $redirectUrl = $this->getUrl('adminhtml/system_config/edit/section/qenta_checkoutpage');
        $this->_redirectUrl($redirectUrl);
    }

    public function testconfigAction()
    {
        $redirectUrl = $this->getUrl('adminhtml/system_config/edit/section/qenta_checkoutpage');

        /** @var Qenta_CheckoutPage_Model_Admin_Test $model */
        $model = Mage::getModel('qenta_checkoutpage/admin_test');
        $model->testconfig();
        $this->_redirectUrl($redirectUrl);
    }

    public function contactsupportAction()
    {
        $this->loadLayout();
        $tabs = $this->getLayout()->createBlock('qenta_checkoutpage/admin_tabs');
        $tabs->setActiveTab('support_request');
        $this->_addContent($this->getLayout()->createBlock('qenta_checkoutpage/admin_support_container'))
            ->_addLeft($tabs);
        $this->renderLayout();
    }

    public function sendsupportrequestAction()
    {
        $url = $this->getUrl('adminhtml/qentacheckoutpage/contactsupport');

        if (!($data = $this->getRequest()->getPost())) {
            $this->_redirectUrl($url);
            return;
        }

        $postObject = new Varien_Object();
        $postObject->setData($data);

        /** @var Qenta_CheckoutPage_Model_Admin_Support $model */
        //$model = Mage::getModel('Qenta_CheckoutPage_Model_Admin_Support');
        $model = Mage::getModel('qenta_checkoutpage/admin_support');
        $model->sendEmail($postObject);
        $this->_redirectUrl($url);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/qenta_checkoutpage');
    }
}
