<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;
use Elastica\Query\Prefix;

class News extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'news');
        $this->setSearchType('newsList');
    }

    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);

        $boolQuery = new Bool();
        $filter = new BoolAnd();
        $strCatalogID= 0;
        
        // filter catalog for News
        (int) $params['lv1Cat'] > 0 ? $strLv1Cat = $params['lv1Cat'] . ',' : $strLv1Cat = '';
        (int) $params['lv2Cat'] > 0 ? $strLv2Cat = $params['lv2Cat'] . ',' : $strLv2Cat = '';
        (int) $params['lv3Cat'] > 0 ? $strLv3Cat = $params['lv3Cat'] . ',' : $strLv3Cat = '';
        $strCatalogID = $strLv1Cat . $strLv2Cat . $strLv3Cat;
        $strCatalogID = rtrim($strCatalogID, ',');
        
        if($strCatalogID){
            $newsCatalogQueryString = new Prefix();
            $newsCatalogQueryString->setPrefix('catalog_id', $strCatalogID);
            $newsCatalogBool = new Bool();
            $newsCatalogBool->addMust($newsCatalogQueryString);
            $boolQuery->addMust($newsCatalogBool);
        }
                
        $strNewsName = $params['newsName'] ? $params['newsName'] : '*';
        $newsNameQueryString = new QueryString();
        $newsNameQueryString->setDefaultField('news_name')
                ->setQuery($strNewsName)
                ->setDefaultOperator('AND');
        $newsNameBool = new Bool();
        $newsNameBool->addMust($newsNameQueryString);
        $boolQuery->addMust($newsNameBool);
        
        $filterIsDeleted = new Term();
        $filterIsDeleted->setTerm('is_deleted', 0);
        $filter->addFilter($filterIsDeleted);
        
        if (isset($params['newsType'])) {
        	$filterIsNewsType = new Term();
        	$filterIsNewsType->setTerm('news_type', (int) $params['newsType']);
        	$filter->addFilter($filterIsNewsType);
        }
        
        if ($params['status'] > -1) {
            $filterStatus = new Term();
            $filterStatus->setTerm('status', (int) $params['status']);
            $filter->addFilter($filterStatus);
        }


        $arrSort = array('_score');

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFilter($filter)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);

        $query = $this->setHighLight($query);
        
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);

        $this->setResultSet($resultSet);
        $arrNewsList = $this->toArray();

        return $arrNewsList;
    }

}
