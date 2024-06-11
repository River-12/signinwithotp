<?php

namespace Riverstone\SignInWithOtp\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class SmsType implements OptionSourceInterface
{

    /**
     * OTP config
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'numeric', 'label' => __('Numeric')],
            ['value' => 'alphaNumeric', 'label' => __('Alpha Numeric')],
            ['value' => 'alphabets', 'label' => __('Alphabets')]

        ];
    }
}
