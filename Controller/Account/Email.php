<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Riverstone\SignInWithOtp\Helper\Mobile\MobileData;
use Magento\Customer\Model\CustomerFactory;

class Email extends Action
{
    /**
     * @var MobileData
     */
    public $helper;
    /**
     * @var CustomerFactory
     */
    public $customerFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @param Context $context
     * @param MobileData $helper
     * @param CustomerFactory $customerFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context         $context,
        MobileData      $helper,
        CustomerFactory $customerFactory,
        JsonFactory     $resultJsonFactory
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Returns email
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $response = ['errors' => true, 'message' => __("Something went wrong")];
        try {
            $customer = $this->customerFactory->create()->load($params['id']);
            $this->helper->sendOtpVerifyEmail($customer);
            $response = ['errors' => false, 'message' => __("Verification email sent")];
        } catch (\Exception $e) {
            $response = ['errors' => true, 'message' => __($e->getMessage())];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
