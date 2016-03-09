<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageContent extends AbstractTableGateway {

    protected $table = 'tbl_contents';

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
                    ->order(array('cont_id DESC'));

            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'cont_id DESC') {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            
            $select = $sql->Select()
                    ->from(array('ct' => 'tbl_contents'))->columns(array('cont_id', 'cont_title', 'cont_slug', 'cate_id', 'cont_created', 'cont_viewer', 'cont_status', 'cont_image','cont_summary','cont_content'))
                    // ->join(array('us' => 'tbl_users'), 'us.user_id = ct.user_id', array('user_id', 'user_name', 'user_fullname'))
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

    public function getLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'cont_id DESC') {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $strScore = !empty($arrCondition['search']) ? ',MATCH(cont_title,cont_content,cont_summary) AGAINST("' . $arrCondition['search'] . '" ) as score' : ',0 as score';
            $query = 'select *' . $strScore
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhere
                    . ' order by ' . $strOrder
                    . ' limit ' . $intLimit
                    . ' offset ' . ($intLimit * ($intPage - 1));
//            p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    
    
    
    public function getLimitUnion($cateID) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "SELECT * FROM(SELECT *FROM tbl_contents WHERE main_cate_id = ".$cateID." and cont_status = 1 UNION SELECT * FROM  tbl_contents WHERE FIND_IN_SET(" . $cateID . ",cate_id) AND cont_status = 1 )  as tb_temp LIMIT 0,10";
        //p($query);die;
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        return $result;
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
//            $validator = new Validate();
//            $noRecordExists = $validator->noRecordExists($p_arrParams['cont_title'], $this->table, 'cont_title', $this->adapter);
//            if (!$noRecordExists) {
//                return false;
//            }
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

    public function edit($p_arrParams, $intProductID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intProductID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'cont_id=' . $intProductID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (!empty($arrCondition['cont_id_smaller'])) {
            $strWhere .= " AND cont_id <" . $arrCondition['cont_id_smaller'];
        }
        
        if (!empty($arrCondition['cont_slug'])) {
            $strWhere .= " AND cont_slug='" . $arrCondition['cont_slug']."'";
        }

        if (!empty($arrCondition['cont_id'])) {
            $strWhere .= " AND cont_id=" . $arrCondition['cont_id'];
        }
        if (!empty($arrCondition['cont_name'])) {
            $strWhere .= " AND cont_title=" . $arrCondition['cont_title'];
        }
        if (!empty($arrCondition['cont_status'])) {
            $strWhere .= " AND cont_status =" . $arrCondition['cont_status'];
        }

        if (!empty($arrCondition['not_cont_status'])) {
            $strWhere .= " AND cont_status !=" . $arrCondition['not_cont_status'];
        }

        if (!empty($arrCondition['user_id'])) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }

        if (!empty($arrCondition['main_cate_id'])) {
            $strWhere .= " AND main_cate_id=" . $arrCondition['main_cate_id'];
        }
        if (!empty($arrCondition['tags_cont_id'])) {
            $strWhere .= " AND FIND_IN_SET('" . $arrCondition['tags_cont_id'] . "',tags_cont_id)";
        }

        if (!empty($arrCondition['listCategoryID'])) {
            $strWhere .= ' AND FIND_IN_SET( main_cate_id,"' . $arrCondition['listCategoryID'] . '")';
        }
        if (!empty($arrCondition['listContentID'])) {
            $strWhere .= " AND cont_id in (" . $arrCondition['listContentID'] . ')';
        }

        if (!empty($arrCondition['cate_id_or_main_cate_id'])) {
            $listCate = explode(',', $arrCondition['cate_id_or_main_cate_id']);
            $str_cate = '';
            $str_main = '';
            foreach ($listCate as $value) {
                $str_cate.= " OR FIND_IN_SET('" . $value . "',cate_id) ";
                $str_main.= " OR main_cate_id = " . $value;
            }
            $strWhere .= " AND (  " . ltrim($str_main, ' OR ') . $str_cate . ")";
//            p($strWhere);die;
        }

        if (!empty($arrCondition['cont_title_like']) && $arrCondition['cont_title_like']) {
            $keyword = "'%" . $arrCondition['cont_title_like'] . "%'";
            $strWhere .= ' AND cont_title LIKE ' . $keyword;
        }
        if (!empty($arrCondition['not_cate_id'])) {
            $strWhere .= " AND NOT FIND_IN_SET('" . $arrCondition['not_cate_id'] . "',cate_id)";
        }
        if (!empty($arrCondition['cate_id'])) {
            $strWhere .= " AND FIND_IN_SET('" . $arrCondition['cate_id'] . "',cate_id)";
        }
        if (!empty($arrCondition['tags_id_not'])) {
            $strWhere .= " AND (tags_cont_id  IS NULL OR NOT FIND_IN_SET('" . $arrCondition['tags_id_not'] . "',tags_cont_id))";
        }
        if (!empty($arrCondition['not_main_cate_id'])) {
            $strWhere .= " AND main_cate_id !=" . $arrCondition['not_main_cate_id'];
        }
        if (!empty($arrCondition['search']) && $arrCondition['search']) {
            $strWhere .= ' AND ( MATCH(cont_title,cont_content,cont_summary) AGAINST("' . $arrCondition['search'] . '" ) OR cont_title LIKE "%' . $arrCondition['search'] . '%" OR REPLACE(cont_title," ","") LIKE "%' . str_replace(" ", "", $arrCondition['search']) . '%" )';
        }

        if (!empty($arrCondition['s'])) {
            $strWhere .= " AND cont_title LIKE '%" . $arrCondition['s'] . "%'";
        }
         if (!empty($arrCondition['cont_meta_robot'])) {
            $strWhere .= " AND cont_meta_robot LIKE '".$arrCondition['cont_meta_robot']."%'";
        }

//        p($strWhere);die;
        return $strWhere;
    }

}
