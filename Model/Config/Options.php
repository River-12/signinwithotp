<?php

namespace Riverstone\SignInWithOtp\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'twilio', 'label' => __('Twilio')]];
    }
}
