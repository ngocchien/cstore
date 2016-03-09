<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate;

class storageLinkCategoryBrand extends AbstractTableGateway {

    protected $table = 'tbl_link_category_brand';

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = array()) {
        //p($arrCondition);die;
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('link_cate_brand_id DESC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit, $strOrder) {
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

    public function edit($p_arrParams, $intLinkID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intLinkID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'link_cate_brand_id=' . $intLinkID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetailLinkCateBrand($arrCondition, $intPage = 1, $intLimit, $strOrder) {
        try {
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where(array(
//                        'link_cate_brand_brand' => $arrCondition,
                        'link_cate_brand_category' => $arrCondition))
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            return ($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = null;
        if (empty($arrCondition)) {
            return $strWhere;
        }

        if ($arrCondition['link_cate_brand_id'] !== '' && $arrCondition['link_cate_brand_id'] !== NULL) {
            $strWhere .= " AND link_cate_brand_id=" . $arrCondition['link_cate_brand_id'];
        }

        if ($arrCondition['link_cate_brand_brand'] !== '' && $arrCondition['link_cate_brand_brand'] !== NULL) {
            $strWhere .= " AND link_cate_brand_brand=" . $arrCondition['link_cate_brand_brand'];
        }

        if ($arrCondition['link_cate_brand_category'] !== '' && $arrCondition['link_cate_brand_category'] !== NULL) {
            $strWhere .= " AND link_cate_brand_category=" . $arrCondition['link_cate_brand_category'];
        }

        if (isset($arrCondition['link_desctiprion']) && $arrCondition['link_desctiprion']) {
            $strWhere .= " AND link_desctiprion=" . $arrCondition['link_desctiprion'];
        }

        if (isset($arrCondition['link_meta_title']) && $arrCondition['link_meta_title']) {
            $strWhere .= " AND link_meta_title=" . $arrCondition['link_meta_title'];
        }

        if (isset($arrCondition['link_meta_keyword']) && $arrCondition['link_meta_keyword']) {
            $strWhere .= " AND link_meta_keyword=" . $arrCondition['link_meta_keyword'];
        }

        if (isset($arrCondition['link_meta_desctiption']) && $arrCondition['link_meta_desctiption']) {
            $strWhere .= " AND link_meta_desctiption=" . $arrCondition['link_meta_desctiption'];
        }

        if (isset($arrCondition['link_status']) && $arrCondition['link_status']) {
            $strWhere .= " AND link_status=" . $arrCondition['link_status'];
        }

        if (isset($arrCondition['not_link_status']) && $arrCondition['not_link_status']) {
            $strWhere .= " AND link_status != " . $arrCondition['not_link_status'];
        }

        if (isset($arrCondition['not_link_cate_brand_id']) && $arrCondition['not_link_cate_brand_id']) {
            $strWhere .= " AND link_cate_brand_id != " . $arrCondition['not_link_cate_brand_id'];
        }


        if (!empty($arrCondition['listBrand'])) {
            $strWhere .= " AND ( link_cate_brand_brand in (" . $arrCondition['listBrand'] . ')';
        }
        if (!empty($arrCondition['listCate'])) {
            $strWhere .= " AND ( link_cate_brand_category in (" . $arrCondition['listCate'] . ')';
        }
        if (isset($arrCondition['listCateId']) && $arrCondition['listCateId']) {
            $strWhere .= " AND ( link_cate_brand_brand in (" . $arrCondition['listCateId'] . ') OR link_cate_brand_category in (' . $arrCondition['listCateId'] . '))';
        }

        return $strWhere;
    }

}
