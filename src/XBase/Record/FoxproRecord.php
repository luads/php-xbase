<?php

namespace XBase\Record;

class FoxproRecord extends AbstractRecord
{
    /**
     * @deprecated since 1.3 and will be delete in 2.0. Use get()
     */
    public function getGeneral(string $columnName)
    {
        return $this->data[$columnName];
    }
}
