<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageImages extends AbstractTableGateway {

    protected $table = 'content';

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('content_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }


    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['advi_id'])) {
            $strWhere .= " AND advi_id =" . $arrCondition['advi_id'];
        }
        if (isset($arrCondition['advi_status'])) {
            $strWhere .= " AND advi_status =" . $arrCondition['advi_status'];
        }
        if (isset($arrCondition['prod_id'])) {
            $strWhere .= " AND prod_id =" . $arrCondition['prod_id'];
        }
        if (isset($arrCondition['advi_phone'])) {
            $strWhere .= " AND advi_phone =" . $arrCondition['advi_phone'];
        }
        return $strWhere;
    }

}
