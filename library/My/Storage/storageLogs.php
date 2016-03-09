<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageLogs extends AbstractTableGateway {

    protected $table = 'tbl_user_logs';

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
    
    private function _buildWhere($arrCondition) {
        $strWhere = '';
        
        if (isset($arrCondition['not_cate_id'])) {
            $strWhere .= " AND cate_id !=" . $arrCondition['not_cate_id'];
        }
        
        if (isset($arrCondition['cate_id'])) {
            $strWhere .= " AND cate_id=" . $arrCondition['cate_id'];
        }
        if (isset($arrCondition['cate_status'])) {
            $strWhere .= " AND cate_status=" . $arrCondition['cate_status'];
        }
        if (isset($arrCondition['cate_name'])) {
            $strWhere .= " AND cate_name = '" . $arrCondition['cate_name'] . "'";
        }
        if (isset($arrCondition['cate_slug'])) {
            $strWhere .= " AND cate_slug = '" . $arrCondition['cate_slug'] . "'";
        }
        if (isset($arrCondition['cate_parent'])) {
            $strWhere .= " AND cate_parent=" . $arrCondition['cate_parent'];
        }
        if (isset($arrCondition['cate_type'])) {
            $strWhere .= " AND cate_type=" . $arrCondition['cate_type'];
        }

        if (isset($arrCondition['or_cate_type']) && count($arrCondition['or_cate_type']) > 0) {
            $strType = "";
            foreach ($arrCondition['or_cate_type'] as $val) {
                $strType.=" cate_type = " . $val . " OR";
            }

            $strType = substr($strType, 0, -2);
            $strWhere .= " AND (" . $strType . ")";
        }

        if (isset($arrCondition['not_cate_type'])) {
            $strWhere .= " AND cate_type!=" . $arrCondition['cate_type'];
        }

        if ($arrCondition['cate_grade'] !== '' && $arrCondition['cate_grade'] !== NULL) {
            $strWhere .= ' AND cate_grade NOT LIKE "%' . $arrCondition['cate_grade'] . ':%"';
        }
        if ($arrCondition['categrade'] !== '' && $arrCondition['categrade'] !== NULL) {
            $strWhere .= ' AND cate_grade LIKE "%' . $arrCondition['categrade'] . ':%"';
        }

        if (isset($arrCondition['cate_name_like']) && $arrCondition['cate_name_like']) {
            $keyword = "'%" . $arrCondition['cate_name_like'] . "%'";
            $strWhere .= ' AND cate_name LIKE ' . $keyword;
        }

        if (isset($arrCondition['not_cate_status'])) {
            $strWhere .= " AND cate_status!=" . $arrCondition['not_cate_status'];
        }
        
//die();
        if (isset($arrCondition['listCategoryID'])) {
        //    die("list");
            $strWhere .= " AND cate_id in (" . $arrCondition['listCategoryID'] . ')';
        }
         if (isset($arrCondition['listCategorySlug'])) {
            $strWhere .= " AND cate_slug in (" . $arrCondition['listCategorySlug'] . ")";
        }

        return $strWhere;
    }

}
