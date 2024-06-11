<?php

namespace Riverstone\SignInWithOtp\Helper\Mobile;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url\EncoderInterface;

class MobileData extends AbstractHelper
{
    public const SMS_TYPE = 'riverstone_sign_in_otp/general/sms_type';
    public const SMS_LENGTH = 'riverstone_sign_in_otp/general/sms_length';
    public const MOBILE_NUMBER = 'riverstone_sign_in_otp/general/phone_mobile';
    public const SELLER_ID = 'riverstone_sign_in_otp/general/account_sid';
    public const AUTHORIZATION_KEY = 'riverstone_sign_in_otp/general/auth_token';
    public const EXPIRE_TIME = 'riverstone_sign_in_otp/general/expire_time';
    public const ENABLE = 'riverstone_sign_in_otp/general/enable';
    public const EMAIL_TEMPLATE = 'riverstone_sign_in_otp/general/email_template';
    public const EMAIL_SENDER = 'riverstone_sign_in_otp/general/email_sender';
    public const VERIFY_EMAIL_TEMPLATE = 'riverstone_sign_in_otp/general/verify_email_template';
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StateInterface
     */
    protected $state;
    /**
     * @var EncoderInterface
     */
    protected $encoder;
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $state
     * @param EncoderInterface $encoder
     */
    public function __construct(
        Context               $context,
        StoreManagerInterface $storeManager,
        TransportBuilder      $transportBuilder,
        StateInterface        $state,
        EncoderInterface $encoder
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->encoder = $encoder;
        return parent::__construct($context);
    }

    /**
     * Returns Expire time
     *
     * @return string
     */
    public function getExpiretime()
    {
        return $this->getConfigvalue(self::EXPIRE_TIME, $this->getStoreId());
    }

    /**
     * Returns config
     *
     * @param String $path
     * @param int $storeId
     * @return string
     */
    public function getConfigvalue($path, $storeId)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Returns store id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Returns SMS type
     *
     * @return string
     */
    public function getSmstype()
    {
        return $this->getConfigvalue(self::SMS_TYPE, $this->getStoreId());
    }

    /**
     * Returns SMS length
     *
     * @return string
     */
    public function getSmslength()
    {
        return $this->getConfigvalue(self::SMS_LENGTH, $this->getStoreId());
    }

    /**
     * Returns sender TWILIO mobile number
     *
     * @return string
     */
    public function getSmsmobile()
    {
        return $this->getConfigvalue(self::MOBILE_NUMBER, $this->getStoreId());
    }

    /**
     * Returns sender TWILIO id
     *
     * @return string
     */
    public function getSellerId()
    {
        return $this->getConfigvalue(self::SELLER_ID, $this->getStoreId());
    }

    /**
     * Returns sender TWILIO key
     *
     * @return string
     */
    public function getAUthkey()
    {
        return $this->getConfigvalue(self::AUTHORIZATION_KEY, $this->getStoreId());
    }

    /**
     * Returns module enable
     *
     * @param int $storeId
     * @return string
     */
    public function getModuleEnable()
    {
        return $this->getConfigvalue(self::ENABLE, $this->getStoreId());
    }

    /**
     * Email otp sender
     *
     * @param string $otp
     * @param string $email
     * @return void
     */
    public function sendEmailOtp($otp, $email)
    {
        $templateId = $this->scopeConfig->getValue(self::EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
        $from = $this->scopeConfig->getValue(self::EMAIL_SENDER, ScopeInterface::SCOPE_STORE);
        $toEmail = $email;

        try {
            $templateVars = ['otp' => $otp];

            $storeId = $this->storeManager->getStore()->getId();

            $this->state->suspend();

            $storeScope = ScopeInterface::SCOPE_STORE;
            $templateOptions = ['area' => Area::AREA_FRONTEND, 'store' => $storeId];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)->setTemplateVars($templateVars)->setFrom($from)
                ->addTo($toEmail)->getTransport();
            $transport->sendMessage();
            $this->state->resume();
        } catch (Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     *  Send verification email
     *
     * @param mixed $customer
     * @return void
     */
    public function sendOtpVerifyEmail($customer)
    {
        $templateId = $this->scopeConfig->getValue(self::VERIFY_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
        $from = $this->scopeConfig->getValue(self::EMAIL_SENDER, ScopeInterface::SCOPE_STORE);
        $toEmail = $customer->getEmail();

        $mobileVerifyLink = $this->_getUrl(
            'riverstone_sign_in_otp/account/emailotpsender',
            ['mobile_number' => $this->encoder->encode($customer->getMobilePhone()),
                'id' => $this->encoder->encode($customer->getEntityId())]
        );
        $emailVerifyLink = $this->_getUrl(
            'riverstone_sign_in_otp/account/emailotpsender',
            ['email_id'=>$this->encoder->encode($toEmail),'id'=>$this->encoder->encode($customer->getEntityId())]
        );

        try {
            $templateVars = ['mobile_verify_link' => $mobileVerifyLink,
                'email_verify_link' => $emailVerifyLink,
                'is_email_verified' => $customer->getIsEmailVerified() ? true : false,
                'is_phone_verified' => $customer->getMobilePhone() ? $customer->getIsPhoneVerified() ?
                    true : false : true,
                'store_name' => $this->storeManager->getStore()->getName(),
                'customer' => $customer];

            $storeId = $this->storeManager->getStore()->getId();

            $this->state->suspend();

            $storeScope = ScopeInterface::SCOPE_STORE;
            $templateOptions = ['area' => Area::AREA_FRONTEND, 'store' => $storeId];
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $templateId,
                $storeScope
            )->setTemplateOptions($templateOptions)->setTemplateVars($templateVars)
                ->setFrom($from)->addTo($toEmail)->getTransport();
            $transport->sendMessage();
            $this->state->resume();
        } catch (Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}
