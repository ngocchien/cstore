<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageBuyLog extends AbstractTableGateway {

    protected $table = 'tbl_buy_logs';

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
                    ->order(array('logs_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
//            p($query);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        try {

            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
           //p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    public function getTotal($arrCondition = []) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            //p($query);die;
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
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
//            p($query);die;
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

    public function edit($p_arrParams, $intProdID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intProdID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'logs_id = ' . $intProdID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }
    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['id_or_name'])) {
            $strWhere .= " AND (prod_code LIKE '" . $arrCondition['id_or_name'] ."' OR prod_name LIKE '%" . $arrCondition['id_or_name'] . "%')";
        }
        if (isset($arrCondition['date_from'])) {
            $strWhere .= " AND buy_time >=" . $arrCondition['date_from'];
        }
        if (isset($arrCondition['date_to'])) {
            $strWhere .= " AND buy_time <=" . $arrCondition['date_to'];
        }
        
        return $strWhere;
    }
}
