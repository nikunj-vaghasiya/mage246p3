<?php
declare(strict_types=1);

namespace Digital\Logcleaner\Cron;

use Digital\Logcleaner\Helper\Data as LogCleanerHelper;
use Digital\Logcleaner\Model\Days;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class Logcleaner
{
    private const LOG_FILE       = 'db_cleanup.log';
    private const LOG_ERROR_FILE = 'db_cleanup_error.log';

    /**
     * Table name => datetime column mapping
     */
    private const TABLE_DATE_FIELDS = [
        'report_event'                    => 'logged_at',
        'report_viewed_product_index'     => 'added_at',
        'report_compared_product_index'   => 'added_at',
        'customer_visitor'                => 'last_visit_at',
        'search_query'                    => 'updated_at',
    ];

    public function __construct(
        private readonly LoggerInterface  $logger,
        private readonly DateTime         $dateTime,
        private readonly LogCleanerHelper $logCleanerHelper,
        private readonly ResourceConnection $resourceConnection,
        private readonly DirectoryList    $directoryList
    ) {}

    public function execute(): void
    {
        $connection  = $this->resourceConnection->getConnection();
        $cleanupDays = (int) $this->logCleanerHelper->getCleanupDaysFreq();
        $isRemoveAll = $cleanupDays === Days::REMOVE_ALL;

        foreach (self::TABLE_DATE_FIELDS as $table => $dateField) {
            $tableName = $this->resourceConnection->getTableName($table);

            try {
                if ($isRemoveAll) {
                    $connection->truncateTable($tableName);
                    $this->writeLog(self::LOG_FILE, "Records deleted (truncated) from {$tableName}");
                    continue;
                }

                $cutoffDate = $this->dateTime->gmtDate(
                    'Y-m-d H:i:s',
                    strtotime("-{$cleanupDays} days")
                );

                $count = $connection->fetchOne(
                    $connection->select()
                        ->from($tableName, ['cnt' => new \Zend_Db_Expr('COUNT(*)')])
                        ->where("`{$dateField}` < ?", $cutoffDate)
                );

                if ((int) $count > 0) {
                    $connection->delete(
                        $tableName,
                        ["`{$dateField}` < ?" => $cutoffDate]
                    );
                    $this->writeLog(
                        self::LOG_FILE,
                        "Records deleted from {$tableName}. Last {$cleanupDays} days retained."
                    );
                } else {
                    $this->writeLog(self::LOG_FILE, "Table data is up to date: {$tableName}");
                }

            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->writeLog(self::LOG_ERROR_FILE, $e->getMessage());
            }
        }
    }

    private function writeLog(string $file, string $message): void
    {
        try {
            $logDir  = $this->directoryList->getPath('log');
            $logPath = $logDir . DIRECTORY_SEPARATOR . $file;
            $line    = '[' . $this->dateTime->gmtDate() . '] ' . $message . PHP_EOL;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            error_log($line, 3, $logPath);
        } catch (\Exception $e) {
            $this->logger->error('LogCleaner: could not write log — ' . $e->getMessage());
        }
    }
}