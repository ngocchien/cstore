<?php

namespace My\Storage;

use Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\TableGateway\AbstractTableGateway;

class storageGroup extends AbstractTableGateway {

    protected $table = 'tbl_groups';

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
                    ->order(array('grou_id DESC'));
            $query = $sql->getSqlStringForSqlObject($select);
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
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
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

    public function getTotal($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);

            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetail($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('grou_id DESC'));
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

    public function edit($p_arrParams, $intGroupId) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intGroupId)) {
                return $result;
            }
            return $this->update($p_arrParams, 'grou_id=' . $intGroupId);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = null;
        if (empty($arrCondition)) {
            return $strWhere;
        }

        if (isset($arrCondition['grou_id'])) {
            $strWhere .= ' AND grou_id=' . $arrCondition['grou_id'];
        }
        if (isset($arrCondition['grou_name'])) {
            $strWhere .= " AND grou_name='" . $arrCondition['grou_name'] . "'";
        }
        if (isset($arrCondition['grou_status'])) {
            $strWhere .= " AND grou_status='" . $arrCondition['grou_status'] . "'";
        }
        if (isset($arrCondition['not_grou_status'])) {
            $strWhere .= " AND grou_status !=" . $arrCondition['not_grou_status'] . "";
        }
        if (isset($arrCondition['match_name']) && $arrCondition['match_name']) {
            $keyword = '*' . $arrCondition['match_name'] . '*';
            $strWhere .= " AND ( MATCH(grou_name) AGAINST ('" . $keyword . "'  IN BOOLEAN MODE))";
        }
        return $strWhere;
    }

}
