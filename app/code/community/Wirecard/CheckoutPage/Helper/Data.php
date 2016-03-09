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

class Wirecard_CheckoutPage_Helper_Data extends Mage_Payment_Helper_Data
{

    protected $_pluginVersion = '4.0.2';
    protected $_pluginName = 'Wirecard/CheckoutPage';

    /**
     * predefined test/demo accounts
     *
     * @var array
     */
    protected $_presets = array(
        'demo'      => array(
            'settings/customer_id' => 'D200001',
            'settings/shop_id'     => '',
            'settings/secret'      => 'B8AKTPWBRMNBV455FG6M2DANE99WU2',
            'settings/backendpw'   => 'jcv45z'
        ),
        'test_no3d' => array(
            'settings/customer_id' => 'D200411',
            'settings/shop_id'     => '',
            'settings/secret'      => 'CHCSH7UGHVVX2P7EHDHSY4T2S4CGYK4QBE4M5YUUG2ND5BEZWNRZW5EJYVJQ',
            'settings/backendpw'   => '2g4f9q2m'
        ),
        'test_3d'   => array(
            'settings/customer_id' => 'D200411',
            'settings/shop_id'     => '',
            'settings/secret'      => 'DP4TMTPQQWFJW34647RM798E9A5X7E8ATP462Z4VGZK53YEJ3JWXS98B9P4F',
            'settings/backendpw'   => '2g4f9q2m'
        )
    );

    public function getConfigArray()
    {
        $cfg                = Array('LANGUAGE' => $this->getLanguage());
        $cfg['CUSTOMER_ID'] = $this->getConfigData('settings/customer_id');
        $cfg['SHOP_ID']     = $this->getConfigData('settings/shop_id');
        $cfg['SECRET']      = $this->getConfigData('settings/secret');

        return $cfg;
    }

    /**
     * return config array to be used for client lib, backend ops
     *
     * @return array
     */
    public function getBackendConfigArray()
    {
        $cfg             = $this->getConfigArray();
        $cfg['PASSWORD'] = $this->getConfigData('settings/backendpw');

        return $cfg;
    }

    public function getConfigData($field = null, $storeId = null)
    {
        $type =  Mage::getStoreConfig('wirecard_checkoutpage/settings/configuration', $storeId);

        if (isset($this->_presets[$type]) && isset($this->_presets[$type][$field])) {
            return $this->_presets[$type][$field];
        }

        $path = 'wirecard_checkoutpage';
        if ($field !== null) {
            $path .= '/' . $field;
        }

        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * returns config preformated as string, used in support email
     * @return string
     */
    public function getConfigString()
    {
        $ret = '';
        $exclude = array('secret', 'backendpw');
        foreach ($this->getConfigData() as $group => $fields)
        {
            foreach ($fields as $field => $value)
            {
                if (in_array($field, $exclude))
                {
                    continue;
                }
                if (strlen($ret))
                {
                    $ret .= "\n";
                }
                $ret .= sprintf("%s: %s", $field, $value);
            }
        }

        return $ret;
    }

    public function getLanguage()
    {
        $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
        if (is_array($locale) && !empty($locale))
            $locale = $locale[0];
        else
            $locale = $this->getDefaultLocale();

        return $locale;
    }

    public function getPluginVersion()
    {
        return WirecardCEE_QPay_FrontendClient::generatePluginVersion('Magento', Mage::getVersion(), $this->_pluginName, $this->_pluginVersion);
    }

    public function log($message, $level = null)
    {
        if ($level === null) $level = Zend_Log::INFO;

        Mage::log($message, $level, 'wirecard_checkoutpage.log', true);
    }

}