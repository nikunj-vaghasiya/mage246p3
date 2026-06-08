<?php

namespace Digital\Logcleaner\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const DEFAULT_CLEANUP_FREQUENCY_DAYS = 60;
    public const XML_PATH_DAYS_CONFIGURATION = "log_cleaner/config/days";

    protected $_storeManager;
    protected $directoryList;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\DirectoryList $directoryList
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Returns Days value from configuration
     *
     * @return string
     */
    public function getCleanupDaysFreq()
    {
        $cleanupDays = $this->scopeConfig->getValue(self::XML_PATH_DAYS_CONFIGURATION);
        return ($cleanupDays) ? trim($cleanupDays) : self::DEFAULT_CLEANUP_FREQUENCY_DAYS;
    }

    /**
     * Returns Root Directory Path of server
     *
     * @return string
     */
    public function getRootDirPath()
    {
        return $this->directoryList->getRoot();
    }
}
