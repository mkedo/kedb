<?php

namespace Kedb;

interface Transaction
{
    const READ_UNCOMMITTED = 1;
    const READ_COMMITTED = 2;
    const REPEATABLE_READ = 3;
    const SERIALIZABLE = 4;

    /**
     * @param int $isolationLevel see self::*
     * @return mixed
     * @throws TransactionException
     */
    public function begin($isolationLevel = null);

    /**
     * @throws TransactionException
     */
    public function commit();

    /**
     * @throws TransactionException
     */
    public function rollback();

    /**
     * @return bool
     */
    public function isInTransaction();
}
