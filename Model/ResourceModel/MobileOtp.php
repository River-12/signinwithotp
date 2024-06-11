<?php

namespace Riverstone\SignInWithOtp\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MobileOtp extends AbstractDb
{

    /**
     * Construct
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init('riverstone_mobile_otp', 'entity_id');
    }
}
