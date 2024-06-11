<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Riverstone\SignInWithOtp\Helper\Mobile\Data;

class Resend extends Action
{
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var Collection
     */
    public $collection;
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
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @param Context $context
     * @param SessionManagerInterface $session
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     * @param Collection $collection
     * @param EncryptorInterface $encryptor
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context                     $context,
        SessionManagerInterface     $session,
        Data                        $helper,
        JsonFactory                 $resultJsonFactory,
        Collection                  $collection,
        EncryptorInterface          $encryptor,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper = $helper;
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionManager = $session;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * OTP resender
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        try {
            $params = $this->sessionManager->getUserFormData();

            if (isset($params['email_id'])) {
                $collection = $this->collection->addAttributeToSelect('*')
                    ->addAttributeToFilter('email', $params['email_id'])->load()->getData();

                $customerDetails = $this->customerRepository->getById($collection[0]['entity_id'])
                    ->__toArray();
                $collection[0]['mobile_phone'] = $customerDetails['custom_attributes']['mobile_phone']['value'];
            }
            if (isset($params['mobile_number'])) {
                $collection = $this->collection->addAttributeToSelect('*')
                    ->addAttributeToFilter('mobile_phone', $params['mobile_number'])->load()->getData();
            }

            $otpCode = $this->helper->getOtpcode();

            $this->helper->getSendotp($otpCode, $collection[0]['mobile_phone']);
            $this->helper->sendEmailOtp($otpCode, $collection[0]['email']);

            $otp = base64_encode($otpCode);
            $this->helper->setOtpdata($otp, $collection[0]['mobile_phone']);

            $response = ['errors' => false, 'message' => __('OTP send to your Mobile Number and Email')];

        } catch (Exception $e) {
            $response = ['errors' => true, 'message' => $e->getMessage()];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
