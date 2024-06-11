<?php

namespace Riverstone\SignInWithOtp\Controller\Account;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Result\PageFactory;
use Riverstone\SignInWithOtp\Helper\Mobile\Data;
use Psr\Log\LoggerInterface;

class EmailOtpSender extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var EncryptorInterface
     */
    public $encryptor;
    /**
     * @var LoggerInterface
     */
    public $logger;
    /**
     * @var DecoderInterface
     */
    public $encoder;

    /**
     * @param Context $context
     * @param Data $helper
     * @param EncryptorInterface $encryptor
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param DecoderInterface $encoder
     */
    public function __construct(
        Context            $context,
        Data               $helper,
        EncryptorInterface $encryptor,
        PageFactory        $resultPageFactory,
        LoggerInterface    $logger,
        DecoderInterface   $encoder
    ) {
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->encoder = $encoder;
        parent::__construct($context);
    }

    /**
     * OTP sender
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('otp_verify');
        try {
            $params = $this->getRequest()->getParams();
            $param = [];
            foreach ($params as $key => $value) {
                $param[$key] = $this->encoder->decode($value);
            }
            $block->setData('params', $param);
            $this->helper->emailOtpSender($param);
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $resultPage;
    }
}
