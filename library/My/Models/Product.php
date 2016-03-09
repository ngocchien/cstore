<?php

namespace My\Models;

class Product extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageProduct($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpProduct');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getLimitUnion($cateID) {
        return $this->getParentTable()->getLimitUnion($cateID);
    }

    public function getListProductID($arrCondition = array()) {
//        p($arrCondition);die;
        return $this->getParentTable()->getListProductID($arrCondition);
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getImages($arrCondition, $intPage, $intLimit) {
        $arrResult = $this->getParentTable()->getImages($arrCondition, $intPage, $intLimit);
        return $arrResult;
    }

    public function getListLimitJoinSort($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitJoinSortProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrConditionProd) > 0) {
            foreach ($arrConditionProd as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        if (count($arrConditionSort) > 0) {
            foreach ($arrConditionSort as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitJoinSort($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitJoinSortTags($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitJoinSortTagsProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrConditionProd) > 0) {
            foreach ($arrConditionProd as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        if (count($arrConditionSort) > 0) {
            foreach ($arrConditionSort as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitJoinSortTags($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitSortingPriceJoinSort($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitJoinSortProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrConditionProd) > 0) {
            foreach ($arrConditionProd as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        if (count($arrConditionSort) > 0) {
            foreach ($arrConditionSort as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitJoinSort($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitSortingPriceJoinSortTags($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitJoinSortTagsProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrConditionProd) > 0) {
            foreach ($arrConditionProd as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        if (count($arrConditionSort) > 0) {
            foreach ($arrConditionSort as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitJoinSortTags($arrConditionProd, $arrConditionSort, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitSortingPrice($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitProductSortingPrice:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitSortingPrice($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }

    public function getTotalProdJoinSort($arrConditionProd, $arrConditionSort) {
        return $this->getParentTable()->getTotalProdJoinSort($arrConditionProd, $arrConditionSort);
    }

    public function getDetail($arrCondition) {
        $keyCaching = 'getDetailProduct:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getDetail($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function edit($p_arrParams, $intProductID) {
        $ttl = 60 * 60 * 24 * 7;
        $intResult = $this->getParentTable()->edit($p_arrParams, $intProductID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function editViewer($intProdID) {
        $intResult = $this->getParentTable()->editViewer($intProdID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

}
