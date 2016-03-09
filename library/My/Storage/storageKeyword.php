<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql;

class storageKeyword extends AbstractTableGateway {

    protected $table = 'tbl_keyword';

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
                    ->order(array('word_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);

            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getFirstDataNull() {
        try {
            $adapter = $this->adapter;
            $query = 'select *'
                    . ' from ' . $this->table
                    . " where word_iscrawler =0 and word_status = 1"
                    //  . ' order by word_id ASC'
                    . ' limit 1';
            //p($query);die;
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getDataNull() {
        try {
            $adapter = $this->adapter;
            $query = 'select *'
                    . ' from ' . $this->table
                    . " where word_iscrawler =0 and word_status = 1"
                    . ' order by word_id ASC'
                    . ' limit 4'
                    . ' offset 0';
            //p($query);die;
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
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

    public function edit($p_arrParams, $intKeywordID) {
        try {
            $result = false;
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intKeywordID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'word_id=' . $intKeywordID);
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
            // p($query);die;
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getLimit($arrCondition = [], $intPage = 1, $intLimit, $strOrder, $colSelect = []) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $strScore = !empty($arrCondition['search']) ? ',MATCH(word_key) AGAINST("' . $arrCondition['search'] . '" ) as score' : ',0 as score';
            $col = "*";
            if (!empty($colSelect)) {
                $col = "";
                foreach ($colSelect as $value) {
                    $col.="," . $value;
                }
            }
            $col = trim($col, ",");
            $query = 'select ' . $col . ' ' . $strScore
                    . ' from ' . $this->table
                    . ' where 1=1' . $strWhere
                    . ' order by ' . $strOrder
                    . ' limit ' . $intLimit
                    . ' offset ' . ($intLimit * ($intPage - 1));
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getLimitNoMemcached($arrCondition = [], $intPage = 1, $intLimit, $strOrder) {
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

    /* ------get Max ID */

    public function getMax($arrCondition = []) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns(array('maxID' => new \Zend\Db\Sql\Expression('MAX(word_id)')))
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['maxID'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function AddList($arrKeyword = array()) {
        if (!empty($arrKeyword)) {
            $size = count($arrKeyword);
            $num = $size / 30;
            $j = 0;
            for ($i = 1; $i < $num + 1; $i++) {
                $adapter = $this->adapter;
                $query = 'ALTER TABLE tbl_keyword AUTO_INCREMENT = 0;insert into tbl_keyword (word_key,word_slug,word_data,word_parent,word_samelevel,word_level,word_loop,word_iscrawler) values';
                for ($j; $j < 30 * $i; $j++) {
                    $value = $arrKeyword[$j];
                    if (!empty($value)) {
                        $query .= "('" . $value['word_key'] . "','" . $value['word_slug'] . "','" . $value['word_data'] . "'," . $value['word_parent'] . ",'" . $value['word_samelevel'] . "'," . $value['word_level'] . ',' . $value['word_loop'] . ',' . $value['word_iscrawler'] . '),';
                    }
                }
                $query = rtrim($query, ',');
                $query .= ' on duplicate key update word_id = LAST_INSERT_ID(word_id)';

                $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            }
        }
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder, $arrCol) {
        try {

            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns($arrCol)
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

//    public function getTotal($arrCondition = []) {
//        try {
//            $strWhere = $this->_buildWhere($arrCondition);
//            $adapter = $this->adapter;
//            $sql = new Sql($adapter);
//            $select = $sql->Select($this->table)
//                    ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
//                    ->where('1=1' . $strWhere);
//            $query = $sql->getSqlStringForSqlObject($select);
//            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
//        } catch (\Zend\Http\Exception $exc) {
//            if (APPLICATION_ENV !== 'production') {
//                die($exc->getMessage());
//            }
//            return false;
//        }
//    }

    /* end get Max ID */

    private function _buildWhere($arrCondition) {
        $strWhere = '';
        if (isset($arrCondition['word_id'])) {
            $strWhere .= " AND word_id= '" . $arrCondition['word_id'] . "'";
        }
        if (isset($arrCondition['null_word_data'])) {
            $strWhere .= " AND word_data != '' AND word_data != '[]'";
        }
        if (isset($arrCondition['word_status'])) {
            $strWhere .= " AND word_status=" . $arrCondition['word_status'];
        }
        if (isset($arrCondition['word_slug'])) {
            $strWhere .= " AND word_slug = '" . $arrCondition['word_slug'] . "'";
        }
        if (isset($arrCondition['word_name_like']) && !empty($arrCondition['word_name_like'])) {
//            if (strpos($arrCondition['word_name_like'], '%')) {
//                $keyword = "'" . $arrCondition['word_name_like'] . "%'";
//                $strWhere .= ' AND word_key LIKE ' . $keyword;
//            } else {
            $keyword = "'" . $arrCondition['word_name_like'] . "'";
            $strWhere .= ' AND word_key LIKE ' . $keyword;
//            }
        }

        if (isset($arrCondition['limit'])) {
            $page = empty($arrCondition['page']) ? 1 : $arrCondition['page'];
            $strWhere .= " AND word_id >" . ($arrCondition['limit'] * ($page - 1) ) . " AND word_id <" . $arrCondition['limit'] * $page;
        }

        if (!empty($arrCondition['not_word_status'])) {
            $strWhere .= " AND word_status !=" . $arrCondition['not_word_status'];
        }
        if (isset($arrCondition['word_id_less'])) {
            $strWhere .= " AND word_id <='" . $arrCondition['word_id_less'] . "'";
        }

        if (isset($arrCondition['word_id_between'])) {
            $strWhere .= " AND word_id > " . ($arrCondition['word_id_between'] - 50) . " AND word_id <  " . ($arrCondition['word_id_between'] + 50) . " ";
        }

        if (isset($arrCondition['level != loop'])) {
            $strWhere .= " AND word_level != word_loop";
        }
        if (!empty($arrCondition['search'])) {
            $strWhere .= " AND (MATCH(word_key) AGAINST('" . $arrCondition['search'] . "'))";
        }

        if (!empty($arrCondition['word_samelevel_not_NULL'])) {
            $strWhere .= " AND word_samelevel != NULL";
        }
        if (!empty($arrCondition['word_level_word_loop'])) {
            $strWhere .= " AND word_level != word_loop  AND word_id >160000 ";
        }
        if (!empty($arrCondition['word_slug_array'])) {
            $strWhere .= " AND (";
            foreach ($arrCondition['word_slug_array'] as $key => $value) {
                $strWhere .= " word_slug = '" . $value . "' OR";
            }

            $strWhere = trim($strWhere, "OR") . ")";
        }

        return $strWhere;
    }

}
