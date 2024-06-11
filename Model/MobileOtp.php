<?php

namespace Riverstone\SignInWithOtp\Model;

use Magento\Framework\Model\AbstractModel;
use Riverstone\SignInWithOtp\Model\ResourceModel\MobileOtp as ResourceModel;

class MobileOtp extends AbstractModel
{
    /**
     * Construct
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
