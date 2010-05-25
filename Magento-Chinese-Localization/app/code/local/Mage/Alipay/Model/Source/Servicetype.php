<?php

class Mage_Alipay_Model_Source_Servicetype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'trade_create_by_buyer', 'label' => Mage::helper('alipay')->__('Products')),
            array('value' => 'create_digital_goods_trade_p', 'label' => Mage::helper('alipay')->__('Virtual Products')),
        );
    }
}



