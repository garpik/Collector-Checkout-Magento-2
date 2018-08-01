<?php

namespace Boolfly\PaymentFee\Model\Config;

class ActiveMethods
{
    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ActiveMethods constructor.
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
    }

    protected function getPaymentMethods()
    {
        return $this->paymentConfig->getActiveMethods();
    }

    public function toOptionArray()
    {
        $methods = [
            [
                'value' => '',
                'label' => ''
            ]
        ];
        foreach ($this->getPaymentMethods() as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = [
                'label' => $paymentTitle,
                'value' => $paymentCode
            ];
        }
        return $methods;
    }
}
