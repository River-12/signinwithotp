<?php

namespace Riverstone\SignInWithOtp\Block\Popup\Form;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Riverstone\SignInWithOtp\Helper\Mobile\Data;

class Otp extends Template
{
    /**
     * @var Data
     */
    public $helper;

    /**
     * @param TemplateContext $context
     * @param Data $helper
     */
    public function __construct(TemplateContext $context, Data $helper)
    {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Returns Otp length
     *
     * @return string
     */
    public function getOtpLength()
    {
        return $this->helper->getSmslength();
    }
}
