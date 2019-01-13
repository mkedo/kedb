<?php
namespace Kedb\Spi\Common;

use Kedb\Connection;
use Kedb\Query;
use Kedb\Transaction;
use Kedb\TransactionException;

class CmTransaction implements Transaction
{
    private static $txn_map = array(
        Transaction::READ_UNCOMMITTED => 'READ UNCOMMITTED',
        Transaction::READ_COMMITTED => 'READ COMMITTED',
        Transaction::REPEATABLE_READ => 'REPEATABLE READ',
        Transaction::SERIALIZABLE => 'SERIALIZABLE'
    );

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Transaction nesting level
     * @var int
     */
    private $txnDepth = 0;

    /**
     * @var int
     */
    private $currentIsolationLevel = 0;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __destruct()
    {
        if ($this->txnDepth > 0) {
            trigger_error("Destructing txn manager while in transaction", E_USER_WARNING);
        }
    }

    /**
     * @inheritdoc
     */
    public function begin($isolationLevel = null)
    {
        if ($isolationLevel === null) {
            $isolationLevel = Transaction::SERIALIZABLE;
        }
        if (!isset(self::$txn_map[$isolationLevel])) {
            throw new TransactionException("Unknown isolation level $isolationLevel");
        }
        if ($this->txnDepth == 0) {
            $this->connection->plainQuery("BEGIN TRANSACTION ISOLATION LEVEL " . self::$txn_map[$isolationLevel]);
            $this->txnDepth++;
            $this->currentIsolationLevel = $isolationLevel;
        } else {
            if ($isolationLevel > $this->currentIsolationLevel) {
                throw new TransactionException(
                    sprintf(
                        "Can not raise transaction isolation level. Current: %s. Requested: %s.",
                        self::$txn_map[$this->currentIsolationLevel],
                        self::$txn_map[$isolationLevel]
                    )
                );
            }
            $savePointName = "s" . $this->txnDepth;
            $savePointQuery = new Query("SAVEPOINT ?t", [$savePointName]);
            $savePointQuery->exec($this->connection);
            $this->txnDepth++;
        }
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        if ($this->txnDepth > 1) {
            $savePointName = "s" . ($this->txnDepth - 1);
            $savePointQuery = new Query("RELEASE ?t", [$savePointName]);
            $savePointQuery->exec($this->connection);
            $this->txnDepth--;
        } elseif ($this->txnDepth == 1) {
            $this->connection->plainQuery("COMMIT");
            $this->txnDepth--;
        } else {
            throw new TransactionException("There is no transaction in progress");
        }
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        if ($this->txnDepth > 1) {
            $savePointName = "s" . ($this->txnDepth - 1);
            $savePointQuery = new Query("ROLLBACK TO SAVEPOINT ?t", [$savePointName]);
            $savePointQuery->exec($this->connection);
            $this->txnDepth--;
        } elseif ($this->txnDepth == 1) {
            $this->connection->plainQuery("ROLLBACK");
            $this->txnDepth--;
        } else {
            throw new TransactionException("There is no transaction in progress");
        }
    }

    /**
     * @inheritdoc
     */
    public function isInTransaction()
    {
        return $this->txnDepth > 0;
    }
}
