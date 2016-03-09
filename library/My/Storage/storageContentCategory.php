<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageContentCategory extends AbstractTableGateway {

    protected $table = 'tbl_content_categorys';

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }
    
    public function getDetail($arrCondition = array()) {
        //p($arrCondition);die;
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

    public function edit($p_arrParams, $intContentCategoryID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intContentCategoryID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'cate_id=' . $intContentCategoryID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }
    
    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['cate_id'])) {
            $strWhere .= " AND cate_id = " . $arrCondition['cate_id'];
        }
        if (isset($arrCondition['cate_status'])) {
            $strWhere .= " AND cate_status = " . $arrCondition['cate_status'];
        }
        return $strWhere;
    }
}
