<?php

namespace Riverstone\SignInWithOtp\Helper\Mobile;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Riverstone\SignInWithOtp\Model\MobileOtpFactory;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Magento\Framework\Url\EncoderInterface;

class Data extends \Riverstone\SignInWithOtp\Helper\Mobile\MobileData
{
    /**
     * @var MobileOtpFactory
     */
    public $otpFactory;
    /**
     * @var Collection
     */
    public $collection;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    protected $transportBuilder;
    protected $state;
    protected $customerFactory;
    protected $encoder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MobileOtpFactory $otpFactory
     * @param Collection $collection
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $state
     * @param CustomerFactory $customerFactory
     * @param EncoderInterface $encoder
     */
    public function __construct(
        Context               $context,
        StoreManagerInterface $storeManager,
        MobileOtpFactory      $otpFactory,
        Collection            $collection,
        TransportBuilder      $transportBuilder,
        StateInterface        $state,
        CustomerFactory       $customerFactory,
        EncoderInterface      $encoder
    ) {
        $this->otpFactory = $otpFactory;
        $this->collection = $collection;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $storeManager, $transportBuilder, $state, $encoder);
    }

    /**
     * OTP update
     *
     * @param string $mobileNumber
     * @return void
     * @throws Exception
     */
    public function setUpdateotpstatus($mobileNumber)
    {
        $customerstatus = $this->otpFactory->create()->getCollection()->addFieldToFilter(
            'customer',
            $mobileNumber
        )->getData();
        if (!empty($customerstatus)) {
            foreach ($customerstatus as $data) {
                $customerstatus1 = $this->otpFactory->create()->load($data['entity_id']);
                $customerstatus1->setStatus('0');
                $customerstatus1->save();
            }
        }
    }

    /**
     * Email sender
     *
     * @param mixed $params
     * @return array
     */
    public function emailOtpSender($params)
    {
        try {
            $otpCode = $this->getotpCode();
            $customerCollection = $this->customerFactory->create()->load($params['id']);
            $response = ['errors' => true, 'message' => __('Something went wrong')];

            if (isset($params['email_id'])) {
                if ($customerCollection->getEmail() === $params['email_id']) {
                    $this->sendEmailOtp($otpCode, $params['email_id']);

                    $otp = base64_encode($otpCode);
                    $this->setOtpdata($otp, $params['email_id'], $params['id']);
                    $response = ['errors' => false, 'message' => __('OTP send to your Email')];

                }
            }
            if (isset($params['mobile_number'])) {
                if ($customerCollection->getMobilePhone() === $params['mobile_number']) {

                    $this->getSendotp($otpCode, $params['mobile_number']);

                    $otp = base64_encode($otpCode);
                    $this->setOtpdata($otp, $params['mobile_number'], $params['id']);
                    $response = ['errors' => false, 'message' => __('OTP send to your Mobile Number')];
                }
            }
        } catch (Exception $e) {
            $response = ['errors' => false, 'message' => __($e->getMessage())];
        }

        return $response;
    }

    /**
     * Returns OTP
     *
     * @return string
     */
    public function getOtpcode()
    {
        $otpType = $this->getSmstype();
        $otpLength = $this->getSmslength();

        if (empty($otpLength)) {
            $otpLength = 4;
        }
        if ($otpType == "numeric") {
            $otpString = '0123456789';
            $otp = substr(str_shuffle($otpString), 0, $otpLength);
        } elseif ($otpType == "alphabets") {
            $otpString = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp = substr(str_shuffle($otpString), 0, $otpLength);
        } elseif ($otpType == "alphanumeric") {
            $otpString = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp = substr(str_shuffle($otpString), 0, $otpLength);
        }
        return $otp;
    }

    /**
     * Stores OTP data
     *
     * @param string $otp
     * @param string $customer
     * @param int $customerId
     * @return void
     * @throws Exception
     */
    public function setOtpdata($otp, $customer, $customerId = null)
    {
        $question = $this->otpFactory->create();
        $question->setOtp($otp);
        $question->setCustomerId($customerId ? $customerId : null);
        $question->setCustomer($customer);
        $question->setStatus('1');
        $question->save();
    }

    /**
     * Otp sender
     *
     * @param string $otp
     * @param string $mobileNumber
     * @return void
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function getSendotp($otp, $mobileNumber)
    {
        $number = $this->getSmsmobile();
        $sid = $this->getSellerId();
        $token = $this->getAUthkey();

        $twilio = new Client($sid, $token);
        $twilio->messages->create($mobileNumber, ["from" => "+" . $number, "body" => $otp]);
    }
}
