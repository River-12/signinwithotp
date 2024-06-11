<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Riverstone\SignInWithOtp\Helper\Mobile\Data;
use Riverstone\SignInWithOtp\Model\MobileOtpFactory;

class VerifyLoginOtp extends Action
{
    /**
     * @var CustomerFactory
     */
    public $customer;
    /**
     * @var MobileOtpFactory
     */
    public $otpFactory;
    /**
     * @var Session
     */
    public $customersession;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var Collection
     */
    public $collection;
    /**
     * @var EncryptorInterface
     */
    public $encryptor;

    /**
     * @param Context $context
     * @param CustomerFactory $customer
     * @param MobileOtpFactory $otpFactory
     * @param Session $customersession
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     * @param Collection $collection
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context                 $context,
        CustomerFactory         $customer,
        MobileOtpFactory        $otpFactory,
        Session                 $customersession,
        JsonFactory             $resultJsonFactory,
        Data                    $helper,
        Collection              $collection,
        EncryptorInterface      $encryptor
    ) {
        $this->customer = $customer;
        $this->otpFactory = $otpFactory;
        $this->customersession = $customersession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->collection = $collection;
        $this->encryptor = $encryptor;
        return parent::__construct($context);
    }

    /**
     * OTP verifier
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $otpbymobile = $this->getRequest()->getParam('otp');
        $otp = base64_encode($otpbymobile);
        $otpvalue = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)->getData();
        $status = $this->otpFactory->create()
            ->getCollection()->addFieldToFilter('otp', $otp)->addFieldToSelect('status')->getData();
        $expiredtime = $this->helper->getExpiretime();

        if (!empty($otpvalue)) {
            $collection = $this->customer->create()->getCollection()
                ->addAttributeToFilter('email', $otpvalue[0]['customer'])->getData();
            $createdAt = (int)strtotime($otpvalue[0]['created_at']);
            $now = time();
            $now = (int)$now;
            $expire = $now -= $createdAt;
            $otpstatus = $status[0]['status'];
            if ($otpstatus == 1) {
                if ($expire <= $expiredtime) {
                    $response = ['errors' => false, 'message' => __("OTP verified successfully")];
                    $customer = $this->customer->create()->load($collection[0]['entity_id']);
                    $customerSession = $this->customersession;
                    $customerSession->setCustomerAsLoggedIn($customer);
                    $customerSession->regenerateId();
                } elseif ($expire > $expiredtime) {
                    $response = ['errors' => true, 'message' => __("OTP has been expired, Please try again")];
                }
            } elseif ($otpstatus != 1) {
                $response = ['errors' => true, 'message' => __("OTP has been expired, Please try again")];
            }
        } elseif (empty($otpvalue)) {
            $response = ['errors' => true, 'message' => __("Invalid OTP, Please try again")];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
