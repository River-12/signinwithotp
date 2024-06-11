<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Riverstone\SignInWithOtp\Helper\Mobile\MobileData;

class PostOtp extends Action
{
    /**
     * @var Collection
     */
    public $collection;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var CustomerFactory
     */
    public $customer;
    /**
     * @var Session
     */
    public $customersession;
    /**
     * @var EncryptorInterface
     */
    public $encryptor;
    /**
     * @var MobileData
     */
    public $helper;

    /**
     * @param Context $context
     * @param CustomerFactory $customer
     * @param Session $customersession
     * @param JsonFactory $resultJsonFactory
     * @param Collection $collection
     * @param EncryptorInterface $encryptor
     * @param MobileData $helper
     */
    public function __construct(
        Context                 $context,
        CustomerFactory         $customer,
        Session                 $customersession,
        JsonFactory             $resultJsonFactory,
        Collection              $collection,
        EncryptorInterface      $encryptor,
        MobileData              $helper
    ) {
        $this->collection = $collection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customer = $customer;
        $this->customersession = $customersession;
        $this->encryptor = $encryptor;
        $this->helper = $helper;
        return parent::__construct($context);
    }

    /**
     * OTP verify
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $collection = $this->collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('mobile_phone', $params['mobile_number'])->load()->getData();
            $collectionEmail = $this->collection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('email', $params['email'])->load()->getData();

            if (!(empty($collection) && empty($collectionEmail))) {
                $response = ['errors' => true, 'message' => __(
                    "Account using this email or mobile already exists"
                )];
            }
            if ((empty($collection) && empty($collectionEmail))) {
                $customer = $this->customer->create();
                $customer->setEmail($params['email']);
                $customer->setFirstname($params['firstname']);
                $customer->setLastname($params['lastname']);
                $customer->setPassword($params['password']);
                $customer->setDob($params['dob']);
                $customer->save();

                $customerData = $customer->getDataModel();
                $customerData->setCustomAttribute('is_email_verified', 0);
                $customerData->setCustomAttribute('mobile_phone', $params['mobile_number']);
                $customerData->setCustomAttribute('is_phone_verified', 0);
                $customer->updateData($customerData);
                $customer->save();
                $this->helper->sendOtpVerifyEmail($customer);
                $customer = $this->customer->create()->load($customer->getEntityId());
                $customerSession = $this->customersession;
                $customerSession->setCustomerAsLoggedIn($customer);
                $customerSession->regenerateId();
                $response = ['errors' => false, 'message' => __("User Created Successfully.")];
            }
        } catch (Exception $e) {
            $response = ['errors' => false, 'message' => __($e->getMessage())];
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
