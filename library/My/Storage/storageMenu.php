<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageMenu extends AbstractTableGateway {

    protected $table = 'tbl_menus';

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
                    ->order(array('menu_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'menu_id ASC') {
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
//            p($query);die;
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
            $arrResult = $this->select($arrCondition);
            $arrResult = $arrResult->current();
            return get_object_vars($arrResult);
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

    public function edit($p_arrParams, $intMenuID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intMenuID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'menu_id=' . $intMenuID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }
    
    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['menu_id'])) {
            $strWhere .= " AND menu_id=" . $arrCondition['menu_id'];
        }
        if (isset($arrCondition['menu_status'])) {
            $strWhere .= " AND menu_status >=" . $arrCondition['menu_status'];
        }
        
        if (isset($arrCondition['menu_url'])) {
            $strWhere .= " AND menu_url=" . $arrCondition['menu_url'];
        }
        
        if (isset($arrCondition['menu_name_like']) && $arrCondition['menu_name_like']) {
            $keyword = "'%" . $arrCondition['menu_name_like'] . "%'";
            $strWhere .= ' AND menu_name LIKE ' . $keyword;
        }
        
        if (isset($arrCondition['menu_type'])) {
            $strWhere .= " AND menu_type =" . $arrCondition['menu_type'];
        }
        
        if (isset($arrCondition['not_menu_status'])) {
            $strWhere .= " AND menu_status !=" . $arrCondition['not_menu_status'];
        }
        if (isset($arrCondition['menu_name'])) {
            $strWhere .= " AND menu_name ='" . $arrCondition['menu_name']."'";
        }
        
        return $strWhere;
    }

}
