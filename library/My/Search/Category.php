<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Query\Term,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;

class Category extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'category');
        $this->setSearchType('categoryList');
    }
    
    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);

        $boolQuery = new Bool();

        $strCategoryTree = $params['categoryTree'];
        $isHaveFormula = (int) $params['haveFormula'];
        $isBanned = isset($params['banned']) ? (int) $params['banned'] : '';

        if ($strCategoryTree) {
            $arrCategoryList = explode(' > ', $strCategoryTree);

            $strCategoryTree = '';
            $tmp = '';
            foreach ($arrCategoryList as $k => $category) {
                $tmp .= $k > 0 ? ' > ' . $category : $category;
                $strCategoryTree .= '"' . $tmp . '" ';
            }
            $strCategoryTree = rtrim($strCategoryTree, ' ');
            
            $queryString = new QueryString();
            $queryString->setDefaultField('category_tree')
                    ->setQuery($strCategoryTree);
            $boolQuery->addMust($queryString);
        }

        if ($isHaveFormula) {
            $filterHaveFormula = new Term();
            $filterHaveFormula->setParam('haveFormula', 1);
            $boolQuery->addMust($filterHaveFormula);
        }

        if ($isBanned) {
            $filterBanned = new Term();
            $filterBanned->setParam('banned', 0);
            $boolQuery->addMust($filterBanned);
        }

        $filterIsDeleted = new Term();
        $filterIsDeleted->setParam('is_deleted', 0);
        $boolQuery->addMust($filterIsDeleted);

        //set query
        $query = new Query();
        $query->setQuery($boolQuery)->setFrom($intFrom)->setSize($intLimit);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($this->setHighLight($query));
        $this->setResultSet($resultSet);
        return $this->toArray();
    }

}
