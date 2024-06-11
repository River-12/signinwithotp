<?php

namespace Riverstone\SignInWithOtp\Block\Popup\Form;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Http\Context as HttpContext;

class Register extends Template
{
    public const COUNTRIES_ALLOWED = 'riverstone_sign_in_otp/general/allow_country';
    /**
     * @var Session
     */
    public $customerSession;
    /**
     * @var Json
     */
    public $json;
    /**
     * @var HttpContext
     */
    public $httpContext;
    /**
     * @var Registration
     */
    public $registration;

    /**
     * @param Template\Context $context
     * @param Session $customerSession
     * @param Json $json
     * @param HttpContext $httpContext
     * @param Registration $registration
     */
    public function __construct(
        Template\Context $context,
        Session          $customerSession,
        Json             $json,
        HttpContext      $httpContext,
        Registration     $registration
    ) {
        $this->customerSession = $customerSession;
        $this->json = $json;
        $this->httpContext = $httpContext;
        $this->registration = $registration;
        parent::__construct($context);
    }

    /**
     * Returns allowed countries
     *
     * @return bool|string
     */
    public function getPhoneconfig()
    {

        $config = ["nationalMode" => false,
            "utilsScript" => $this->getViewFileUrl('Riverstone_SignInWithOtp::js/utils.js'),
            "preferredCountries" => ['in']];

        $allowedCountries = $this->_scopeConfig->getValue(self::COUNTRIES_ALLOWED, ScopeInterface::SCOPE_STORE);
        $config["onlyCountries"] = $allowedCountries ? explode(",", $allowedCountries) : '';
        return $this->json->serialize($config);
    }

    /**
     *   Returns customer is logged or not
     *
     * @return bool
     */
    public function customerIsAlreadyLoggedIn()
    {
        return (bool)$this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * Returns registation data
     *
     * @return Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }
}
