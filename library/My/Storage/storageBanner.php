<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageBanner extends AbstractTableGateway {

    protected $table = 'tbl_banners';
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
                    ->order(array('ban_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
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
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
//            die($query);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
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
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetail($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
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

    public function edit($p_arrParams, $intBanID) {       

        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intBanID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'ban_id=' . $intBanID);
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
        if (isset($arrCondition['list_ban_location']) && $arrCondition['list_ban_location'] != '') {
            $arrCondition['list_ban_location'] = explode(',', $arrCondition['list_ban_location']);
            $str = '';
            foreach ($arrCondition['list_ban_location'] as $condition) {
                if ($str == '') {
                    $str = ' FIND_IN_SET (' . $condition . ',ban_location) ';
                } else {
                    $str.= ' OR FIND_IN_SET (' . $condition . ',ban_location) ';
                }
            }
            $strWhere.=' AND (' . $str . ')';
        }
        if (isset($arrCondition['list_ban_cate_id']) && $arrCondition['list_ban_cate_id'] != '') {
            $arrCondition['list_ban_cate_id'] = explode(',', $arrCondition['list_ban_cate_id']);

            $str = '';
            foreach ($arrCondition['list_ban_cate_id'] as $condition) {
                if ($str == '') {
                    $str = ' FIND_IN_SET (' . $condition . ',ban_cate_id) ';
                } else {
                    $str.= ' OR FIND_IN_SET (' . $condition . ',ban_cate_id) ';
                }
            }
            $strWhere.=' AND (' . $str . ')';
        }

        if (isset($arrCondition['s']) && $arrCondition['s']) {
            $keyword = "'%" . $arrCondition['s'] . "%'";
            $strWhere .= ' AND ban_title LIKE ' . $keyword;
        }

        if ($arrCondition['ban_id'] !== '' && $arrCondition['ban_id'] !== NULL) {
            $strWhere .= " AND ban_id=" . $arrCondition['ban_id'];
        }
        if ($arrCondition['is_delete'] !== '' && $arrCondition['is_delete'] !== NULL) {
            $strWhere .= " AND is_delete = " . $arrCondition['is_delete'];
        }
        if ($arrCondition['category_id'] !== '' && $arrCondition['category_id'] !== NULL) {
            $strWhere .= ' AND FIND_IN_SET (' . $arrCondition['category_id'] . ', ban_cate_id)';
        }

        if ($arrCondition['not_ban_cate_id'] !== '' && $arrCondition['not_ban_cate_id'] !== NULL) {
            $strWhere .= ' AND (ban_cate_id is null) ';
        }

        return $strWhere;
    }

}
