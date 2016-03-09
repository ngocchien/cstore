<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageBuyProduct extends AbstractTableGateway {

    protected $table = 'tbl_buy_product';

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
                    ->order(array('buy_prod_id ASC'));
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
            return $this->update($p_arrParams, 'prod_id = ' . $intProdID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }
    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['buy_prod_id']) && !empty($arrCondition['buy_prod_id'])) {
            $strWhere .= " AND buy_prod_id =" . $arrCondition['buy_prod_id'];
        }
        if (isset($arrCondition['prod_id']) && !empty($arrCondition['prod_id'])) {
            $strWhere .= " AND prod_id =" . $arrCondition['prod_id'];
        }
        if (isset($arrCondition['prod_status_in']) && !empty($arrCondition['prod_status_in'])) {
            $strWhere .= " AND prod_status IN (" . $arrCondition['prod_status_in'] . ")";
        }
        if (isset($arrCondition['prod_name_like']) && !empty($arrCondition['prod_name_like'])) {
            $keyword = "'%" . $arrCondition['prod_name_like'] . "%'";
            $strWhere .= " AND prod_name LIKE " . $keyword;
        }
        if (isset($arrCondition['strStatusList']) && !empty($arrCondition['strStatusList'])) {
            $strWhere .= " AND prod_status IN (" . $arrCondition['strStatusList'] . ")";
        }
        if (!empty($arrCondition['prod_array'])) {
            $strWhere .= " AND (";
            foreach ($arrCondition['prod_array'] as $key => $value) {
                $strWhere .= " prod_id = '" . $value['prod_id'] . "' OR";
            }

            $strWhere = trim($strWhere, "OR") . ")";
        }
        
        return $strWhere;
    }
}
