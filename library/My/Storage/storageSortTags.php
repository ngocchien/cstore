<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageSortTags extends AbstractTableGateway {

    protected $table = 'tbl_sort_tags';

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
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function delete($p_arrParams) {
        try {
            $strWhere = $this->_buildWhere($p_arrParams);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Delete($this->table)
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getDetail($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function add($p_arrParams) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams)) {
                return false;
            }
            $result = $this->insert($p_arrParams);
            if ($result) {
                $result = $this->lastInsertValue;
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($p_arrParams, $sortID) {
        try {
            //p($p_arrParams);die("jkl");
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($sortID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'sort_id=' . $sortID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['sort_id'])) {
            $strWhere .= " AND sort_id =" . $arrCondition['sort_id'];
        }
        if (isset($arrCondition['sort_tag'])) {
            $strWhere .= " AND sort_tag =" . $arrCondition['sort_tag'];
        }
        if (isset($arrCondition['sort_product'])) {
            $strWhere .= " AND sort_product =" . $arrCondition['sort_product'];
        }
        if (isset($arrCondition['listProductID'])) {
            $strWhere .= " AND sort_product in (" . $arrCondition['listProductID'] . ')';
        }
        return $strWhere;
    }

}
