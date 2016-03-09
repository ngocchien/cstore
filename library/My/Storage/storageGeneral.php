<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageGeneral extends AbstractTableGateway {

    protected $table = 'tbl_general';

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
                    ->order(array('gene_id DESC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    
    public function getLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'gene_id DESC') {
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
    
    public function getDetail($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            //p($query);die;
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
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
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
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

    public function edit($p_arrParams, $intGeneID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intGeneID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'gene_id=' . $intGeneID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['gene_id'])) {
            $strWhere .= " AND gene_id=" . $arrCondition['gene_id'];
        }
        if (isset($arrCondition['gene_status'])) {
            $strWhere .= " AND gene_status =" . $arrCondition['gene_status'];
        }
        if (isset($arrCondition['not_gene_status'])) {
            $strWhere .= " AND gene_status !=" . $arrCondition['not_gene_status'];
        }
        if (isset($arrCondition['gene_code'])) {
            $strWhere .= " AND gene_code= '" . $arrCondition['gene_code'] . "'";
        }
        if (isset($arrCondition['gene_content']) && $arrCondition['gene_content']) {
            $keyword = "'%" . $arrCondition['gene_content'] . "%'";
            $strWhere .= ' AND gene_content LIKE ' . $keyword;
        }
        if (isset($arrCondition['gene_like']) && $arrCondition['gene_like']) {
            $keyword = "'%" . $arrCondition['gene_like'] . "%'";
            $strWhere .= ' AND (gene_content LIKE ' . $keyword . 'OR gene_title LIKE ' . $keyword . ')';
        }
        return $strWhere;
    }
}
