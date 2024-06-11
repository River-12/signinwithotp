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

class OtpSender extends Action
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
     * @var JsonFactory
     */
    public $resultJsonFactory;

    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $filterGroupBuilder;

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
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->helper = $helper;
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionManager = $session;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder=$searchCriteriaBuilder;
        $this->filterBuilder=$filterBuilder;
        $this->filterGroupBuilder=$filterGroupBuilder;
        parent::__construct($context);
    }

    /**
     * OTP  sender
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $params = $this->getRequest()->getParams();
        $this->sessionManager->setUserFormData($params);
        $response = ['errors' => true, 'message' =>'Something went wrong'];

        try {
            if (isset($params['email_id'])) {
                $collection = $this->collection->addAttributeToSelect('*')
                    ->addAttributeToFilter('email', $params['email_id'])->load()->getData();

                if ($collection) {
                    $customerDetails = $this->customerRepository->getById($collection[0]['entity_id'])
                        ->__toArray();

                    $collection[0]['mobile_phone'] = isset($customerDetails['custom_attributes']['mobile_phone']) ?
                        $customerDetails['custom_attributes']['mobile_phone']['value'] : null;
                }
            }
            if (isset($params['mobile_number'])) {
                $collection = $this->collection->addAttributeToSelect('*')
                    ->addAttributeToFilter('mobile_phone', $params['mobile_number'])->load()->getData();
                $logger->info(print_r($collection,true));
            }

            if (empty($collection)) {
                $response = ['errors' => true, 'message' => __("Mobile Number/Email is Not Registered.
                Please create a account before logging in.")];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
            if ($collection) {
                $otpCode = $this->helper->getOtpcode();
                $email=$collection[0]['email'];

                $this->helper->sendEmailOtp($otpCode,$email );

                $otp = base64_encode($otpCode);
                $this->helper->setOtpdata($otp, $collection[0]['email']);

                $response = ['errors' => false, 'message' => __('
                OTP send your Email only,since mobile number is not registered')];

                if ($collection[0]['mobile_phone']) {
                    $this->helper->getSendotp($otpCode, $collection[0]['mobile_phone']);
                    $response = ['errors' => false, 'message' => __('OTP send to your Mobile Number and Email')];
                }
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
        } catch (Exception $e) {
            $response = ['errors' => true, 'message' => $e->getMessage()];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
