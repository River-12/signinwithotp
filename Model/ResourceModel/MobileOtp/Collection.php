<?php

namespace Riverstone\SignInWithOtp\Model\ResourceModel\MobileOtp;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Riverstone\SignInWithOtp\Model\ResourceModel\MobileOtp as ResourceModel;
use Riverstone\SignInWithOtp\Model\MobileOtp as Model;

class Collection extends AbstractCollection
{
    /**
     * Construct
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
