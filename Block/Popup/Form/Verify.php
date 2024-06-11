<?php

namespace Riverstone\SignInWithOtp\Block\Popup\Form;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Verify extends Template
{
    /**
     * @var Session
     */
    public $customer;
    /**
     * @var CustomerFactory
     */
    public $customerFactory;

    /**
     * @param Context $context
     * @param Session $customer
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session          $customer,
        CustomerFactory  $customerFactory,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Checks verification is done or not
     *
     * @return int
     */
    public function getIsEmailVerified()
    {
        $isVerified = 1;
        $customer = $this->customerFactory->create()->load($this->customer->getCustomerId());
        if ($customer->getIsPhoneVerified() || $customer->getIsEmailVerified()) {
            $isVerified = 0;
        }
        return $isVerified;
    }

    /**
     * Returns customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->customer->getCustomerId();
    }

    /**
     * Returns customer logged or not
     *
     * @return int
     */
    public function getIsLoggedIn()
    {
        return $this->customer->isLoggedIn() ? 1 : 0;
    }
}
