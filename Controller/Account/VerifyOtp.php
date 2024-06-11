<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Riverstone\SignInWithOtp\Helper\Mobile\Data;
use Riverstone\SignInWithOtp\Model\MobileOtpFactory;
use Magento\Customer\Model\CustomerFactory;

class VerifyOtp extends Action
{
    /**
     * @var MobileOtpFactory
     */
    public $otpFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var EncryptorInterface
     */
    public $encryptor;
    /**
     * @var CustomerFactory
     */
    public $customerFactory;

    /**
     * @param Context $context
     * @param MobileOtpFactory $otpFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     * @param EncryptorInterface $encryptor
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context                 $context,
        MobileOtpFactory        $otpFactory,
        JsonFactory             $resultJsonFactory,
        Data                    $helper,
        EncryptorInterface      $encryptor,
        CustomerFactory $customerFactory
    ) {
        $this->otpFactory = $otpFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->customerFactory = $customerFactory;
        return parent::__construct($context);
    }

    /**
     * OTP verifier
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $otpbymobile = $this->getRequest()->getParams();
        $otps = '';
        foreach ($otpbymobile as $key => $value) {
            if (str_contains($key, 'otp')) {
                $otps .= $value;
            }
        }
        $otp = base64_encode($otps);
        $otpvalue = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)->getData();
        $status = $this->otpFactory->create()->getCollection()->addFieldToFilter('otp', $otp)
            ->addFieldToSelect('status')->getData();
        $expiredtime = $this->helper->getExpiretime();

        if (!empty($otpvalue)) {
            $customer = $this->customerFactory->create()->load((int)($otpvalue[0]['customer_id']));
            $createdAt = (int)strtotime($otpvalue[0]['created_at']);
            $expire = (int)time() - $createdAt;
            $otpstatus = $status[0]['status'];
            $response = $this->sendOTP($otpstatus, $expire, $expiredtime, $otpvalue, $customer);
        } elseif (empty($otpvalue)) {
            $response = ['errors' => true, 'message' => __("Invalid OTP, Please try again")];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * Verify and save data
     *
     * @param int $otpstatus
     * @param int $expire
     * @param int $expiredtime
     * @param any $otpvalue
     * @param any $customer
     * @return array
     */
    public function sendOTP($otpstatus, $expire, $expiredtime, $otpvalue, $customer)
    {
        if ($otpstatus == 1) {
            if ($expire <= $expiredtime) {
                $response = ['errors' => false, 'message' => __("OTP verified successfully")];
                if (preg_match("/@/", $otpvalue[0]['customer'])) {
                    $customer->setIsEmailVerified(1);
                } elseif (!(preg_match("/@/", $otpvalue[0]['customer']))) {
                    $customer->setIsPhoneVerified(1);
                }
                $customer->save();
            } elseif ($expire > $expiredtime) {
                $response = ['errors' => true, 'message' => __("OTP has been expired, Please try again")];
            }
        } elseif ($otpstatus != 1) {
            $response = ['errors' => true, 'message' => __("OTP has been expired, Please try again")];
        }
        return $response;
    }
}
