<?php
class Retailcrm_Retailcrm_Block_Adminhtml_System_Config_Form_Fieldset_Base extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_fieldRenderer;
    protected $_values;
    protected $_apiKey;
    protected $_apiUrl;
    protected $_isCredentialCorrect;

    public function __construct()
    {
        parent::__construct();

        $this->_apiUrl = Mage::getStoreConfig('retailcrm/general/api_url');
        $this->_apiKey = Mage::getStoreConfig('retailcrm/general/api_key');

        $this->_isCredentialCorrect = false;

        if (!empty($this->_apiUrl) && !empty($this->_apiKey)) {
            $client = Mage::getModel(
                'retailcrm/ApiClient',
                array('url' => $this->_apiUrl, 'key' => $this->_apiKey, 'site' => null)
            );

            try {
                $response = $client->sitesList();
            } catch (Retailcrm_Retailcrm_Model_Exception_CurlException $e) {
                Mage::log($e->getMessage());
            }

            if ($response->isSuccessful()) {
                $this->_isCredentialCorrect = true;
            }

            $lastRun = Mage::getStoreConfig('retailcrm/general/history');

            if (!empty($lastRun)) {
                $lastRun = new DateTime(
                    date(
                        'Y-m-d H:i:s',
                        strtotime('-1 days', strtotime(date('Y-m-d H:i:s')))
                    )
                );
            } else {
                $lastRun = new DateTime($lastRun);
            }

            $history = $client->ordersHistory($lastRun);
            var_dump($history);
            Mage::getModel('core/config')->saveConfig('retailcrm/general/history', $history->getGeneratedAt());
        }
    }
}
