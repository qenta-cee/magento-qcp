<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

class Wirecard_CheckoutPage_Block_Admin_Support_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/sendsupportrequest', array('id' => $this->getRequest()->getParam('id'))),
                'method'  => 'post'
            )
        );
        $fieldset = $form->addFieldset('form_form', array('legend' => Mage::helper('wirecard_checkoutpage')->__('Item information')));


        $fieldset->addField('to', 'select', array(
            'label'    => Mage::helper('wirecard_checkoutpage')->__('To'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'to',
            'options'  => array(
                'support.at@wirecard.com' => 'Support AT',
                'support@wirecard.com' => 'Support DE'
            )
        ));

        $fieldset->addField('replyto', 'text', array(
            'label'    => Mage::helper('wirecard_checkoutpage')->__('Your e-mail address'),
            'class'    => 'validate-email',
            'name'     => 'replyto'
        ));

        $fieldset->addField('description', 'textarea', array(
            'label'    => Mage::helper('wirecard_checkoutpage')->__('Your message'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'description',
            'style'    => 'height:30em;width:50em'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
