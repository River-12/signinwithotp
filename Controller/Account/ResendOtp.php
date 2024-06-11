<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Riverstone\SignInWithOtp\Helper\Mobile\Data;

class ResendOtp extends Action
{
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var SessionManagerInterface
     */
    public $sessionManager;
    /**
     * @var EncryptorInterface
     */
    public $encryptor;

    /**
     * @param Context $context
     * @param SessionManagerInterface $session
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context                 $context,
        SessionManagerInterface $session,
        Data                    $helper,
        JsonFactory             $resultJsonFactory,
        EncryptorInterface      $encryptor
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionManager = $session;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * Resend otp
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $otpCode = $this->helper->getOtpcode();

            if (isset($params['email_id'])) {
                $this->helper->setUpdateotpstatus($params['email_id']);
                $this->helper->sendEmailOtp($otpCode, $params['email_id']);

                $otp = base64_encode($otpCode);
                $this->helper->setOtpdata($otp, $params['email_id']);
                $response = ['errors' => false, 'message' => __('OTP send to your Email')];
            }
            if (isset($params['mobile_number'])) {
                $this->helper->setUpdateotpstatus($params['mobile_number']);

                $this->helper->getSendotp($otpCode, $params['mobile_number']);

                $otp = base64_encode($otpCode);
                $this->helper->setOtpdata($otp, $params['mobile_number']);

                $response = ['errors' => false, 'message' => __('OTP send to your Mobile Number')];
            }
        } catch (Exception $e) {
            $response = ['errors' => true, 'message' => $e->getMessage()];
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
