<?php

namespace Kedb;

interface Transaction
{
    public function begin($isolationLevel = null);
    public function commit();
    public function rollback();
    public function isInTransaction();
}
