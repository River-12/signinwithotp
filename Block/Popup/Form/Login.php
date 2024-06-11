<?php

namespace Riverstone\SignInWithOtp\Block\Popup\Form;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Store\Model\ScopeInterface;

class Login extends Template
{
    public const COUNTRIES_ALLOWED = "riverstone_sign_in_otp/general/allow_country";
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
     * @var TemplateContext
     */
    public $context;
    /**
     * @var FormKey
     */
    public $formKey;

    /**
     * @param TemplateContext $context
     * @param Session $customerSession
     * @param Json $json
     * @param HttpContext $httpContext
     * @param FormKey $formKey
     */
    public function __construct(
        TemplateContext $context,
        Session         $customerSession,
        Json            $json,
        HttpContext     $httpContext,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->customerSession = $customerSession;
        $this->json = $json;
        $this->httpContext = $httpContext;
        $this->context = $context;
        $this->formKey = $formKey;
        parent::__construct($context);
    }

    /**
     * Returns customer is logged or not
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Returns login URL
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('customer/ajax/login');
    }

    /**
     * Return allow countries for phone number
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
     *  Returns customer is logged or not
     *
     * @return bool
     */
    public function customerIsAlreadyLoggedIn()
    {
        return (bool)$this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * Returns form keys
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
