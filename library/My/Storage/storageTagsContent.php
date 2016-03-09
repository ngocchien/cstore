<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageTagsContent extends AbstractTableGateway {

    protected $table = 'tbl_tags_content';
    protected $adapter;

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = null) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('tags_cont_sort ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            //p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
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
                    ->order(array('tags_cont_slug ASC'))
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }
    
    public function getListUnlike($arrCondition = null) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('tags_cont_sort ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }
    
    public function updateTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set tags_cont_grade = REPLACE(tags_cont_grade,'" . $dataUpdate['tags_cont_grade'] . "','" . $dataUpdate['grade_update']."'),tags_cont_status =".$dataUpdate['tags_cont_status'].",tags_cont_sort = REPLACE(tags_cont_sort,'" . $dataUpdate['tags_cont_sort'] . "','" . $dataUpdate['sort_update']."') WHERE tags_cont_grade LIKE '" . $dataUpdate['tags_cont_grade'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }
    
    public function updateStatusTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set tags_cont_status = ".$dataUpdate['tags_cont_status']." WHERE tags_cont_grade LIKE '" . $dataUpdate['grade_update'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
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
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetail($arrCondition) {
//        p($arrCondition);die;
        try {
            $strWhere = $this->_buildWhere($arrCondition);
//            p($strWhere);die;
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)->where('1=1' . $strWhere);
//            p($select);die;
            $query = $sql->getSqlStringForSqlObject($select);
//            p($query);die;
          return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
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
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($p_arrParams, $intTagID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intTagID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'tags_cont_id=' . $intTagID);
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

        if ($arrCondition['tags_cont_id'] !== '' && $arrCondition['tags_cont_id'] !== NULL) {
            $strWhere .= " AND tags_cont_id=" . $arrCondition['tags_cont_id'];
        }
        
        if ($arrCondition['tags_cont_slug'] !== '' && $arrCondition['tags_cont_slug'] !== NULL) {
            $strWhere .= " AND tags_cont_slug='" . $arrCondition['tags_cont_slug']."'";
        }

        if (isset($arrCondition['tags_cont_name']) && $arrCondition['tags_cont_name']) {
            $strWhere .= " AND LOWER(tags_cont_name)='" . strtolower($arrCondition['tags_cont_name']) . "'";
        }

        if (isset($arrCondition['tags_cont_desctiprion']) && $arrCondition['tag_desctiprion']) {
            $strWhere .= " AND tag_desctiprion=" . $arrCondition['tag_desctiprion'];
        }

        if (isset($arrCondition['tags_cont_meta_title']) && $arrCondition['tag_meta_title']) {
            $strWhere .= " AND tag_meta_title=" . $arrCondition['tag_meta_title'];
        }

        if (isset($arrCondition['tags_cont_meta_keyword']) && $arrCondition['tag_meta_keyword']) {
            $strWhere .= " AND tag_meta_keyword=" . $arrCondition['tag_meta_keyword'];
        }

        if (isset($arrCondition['tags_cont_meta_desctiption']) && $arrCondition['tag_meta_desctiption']) {
            $strWhere .= " AND tag_meta_desctiption=" . $arrCondition['tag_meta_desctiption'];
        }

        if (isset($arrCondition['tags_cont_status']) && $arrCondition['tags_cont_status']) {
            $strWhere .= " AND tags_cont_status=" . $arrCondition['tags_cont_status'];
        }
        
        if (isset($arrCondition['not_tags_cont_id'])) {
            $strWhere .= " AND tags_cont_id !=" . $arrCondition['not_tags_cont_id'];
        }
        
        if (isset($arrCondition['not_tags_cont_status']) && $arrCondition['not_tags_cont_status']) {
            $strWhere .= " AND tags_cont_status != " . $arrCondition['not_tags_cont_status'];
        }
        if (isset($arrCondition['tags_cont_name_like']) && $arrCondition['tags_cont_name_like']) {
            $strWhere .= " AND tags_cont_name LIKE '%" . $arrCondition['tags_cont_name_like'] . "%'";
        }

        if (isset($arrCondition['tags_cont_meta_desctiption']) && $arrCondition['tag_meta_desctiption']) {
            $strWhere .= " AND tag_meta_desctiption=" . $arrCondition['tag_meta_desctiption'];
        }
        
        if (isset($arrCondition['in_tags_cont_id'])) {
            $strWhere .= " AND tags_cont_id IN(" . $arrCondition['in_tags_cont_id'] . ")";
        }
        
        if ($arrCondition['tags_cont_grade'] !== '' && $arrCondition['tags_cont_grade'] !== NULL) {
            $strWhere .= ' AND tags_cont_grade NOT LIKE "%' . $arrCondition['tags_cont_grade'] . ':%"';
        }
        
        if ($arrCondition['tagsgrade'] !== '' && $arrCondition['tagsgrade'] !== NULL) {
            $strWhere .= ' AND tags_cont_grade LIKE "%' . $arrCondition['tagsgrade'] . ':%"';
        }
        return $strWhere;
    }

}
