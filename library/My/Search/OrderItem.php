<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;

class OrderItem extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'order_items');
        $this->setSearchType('itemList');
    }

    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);

        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $rangeFilter = new \Elastica\Filter\Range();
        $rangeFilter->addField('order_item_id', array('from' => 0));
        $filter->addFilter($rangeFilter);

        //set product name = * là giá trị mặc định.. nếu không có search theo query string thì ko lỗi
        $strProductName = $params['itemName'] ? trim($params['itemName']) : '*';
        $productNameQueryString = new QueryString();
        $productNameQueryString->setDefaultField('product_name')
                ->setQuery($strProductName)
                ->setDefaultOperator('AND');
        $productNameBool = new Bool();
        $productNameBool->addMust($productNameQueryString);
        $boolQuery->addMust($productNameBool);

        //kiểm tra có item status hay không, nếu có thì add vào filter
        if (isset($params['itemStatus']) && $params['itemStatus'] >= 0) {
            $filterItemStatus = new Term();
            $filterItemStatus->setTerm('item_status', $params['itemStatus']);
            $filter->addFilter($filterItemStatus);
        }

        //filter sản phẩm theo tracking number
        if ($params['trackingNumber']) {
            $strTracking = trim($params['trackingNumber']);
            $trackingLen = strlen($strTracking);

            $strQuery = $trackingLen <= 3 ? $strTracking : '*' . $strTracking . '*';

            $trackingQueryString = new QueryString();
            $trackingQueryString->setQuery($strQuery)
                    ->setDefaultOperator('AND');
            $trackingLen <= 3 ? $trackingQueryString->setDefaultField('tracking_number.not_analyzed') : $trackingQueryString->setDefaultField('tracking_number.analyzed');

            $trackingBool = new Bool();
            $trackingBool->addMust($trackingQueryString);
            $boolQuery->addMust($trackingBool);
        }

        //kiểm tra và filter sản phẩm theo ngày hàng về
        list($day, $month, $year) = explode('/', $params['receivedDateFrom']);
        $intReceivedDateFrom = (int) General::dateToTimestamp($day, $month, $year);

        list($day, $month, $year) = explode('/', $params['receivedDateTo']);
        $intReceivedDateTo = (int) General::dateToTimestamp($day, $month, $year, 23, 59, 59);

        if (($intReceivedDateFrom && $intReceivedDateTo) && $intReceivedDateFrom <= $intReceivedDateTo) {
            $rangeFilter = new \Elastica\Filter\Range();
            $rangeFilter->addField('received_date', array('from' => $intReceivedDateFrom, 'to' => $intReceivedDateTo));
            $filter->addFilter($rangeFilter);
        }

        //kiểm tra và filter sản phẩm theo ngày dự kiến hàng về kho bên Mỹ
        list($day, $month, $year) = explode('/', $params['predictDateFrom']);
        $intPredictDateFrom = (int) General::dateToTimestamp($day, $month, $year);

        list($day, $month, $year) = explode('/', $params['predictDateTo']);
        $intPredictDateTo = (int) General::dateToTimestamp($day, $month, $year, 23, 59, 59);

        if (($intPredictDateFrom && $intPredictDateTo) && $intPredictDateFrom <= $intPredictDateTo) {
            $rangeFilter = new \Elastica\Filter\Range();
            $rangeFilter->addField('predict_date', array('from' => $intPredictDateFrom, 'to' => $intPredictDateTo));
            $filter->addFilter($rangeFilter);
        }

        //Order ID
        //kiểm tra có orderID hay không
        $arrOrderID = explode('-', $params['orderID'] ? $params['orderID'] : 0);
        $orderID = (int) array_pop($arrOrderID);

        //nếu có orderID thì add vào filter
        if ($orderID) {
            $filterOrderID = new Term();
            $filterOrderID->setTerm('order_id', $orderID);
            $filter->addFilter($filterOrderID);
        }

        //Đợt hàng về
        if (isset($params['vehicleTrip']) && $params['vehicleTrip'] > 0) {
            $filterVehicleTrip = new Term();
            $filterVehicleTrip->setTerm('vehicle_trip', (int) $params['vehicleTrip']);
            $filter->addFilter($filterVehicleTrip);
        }

        //fullname
        if (isset($params['fullname']) && $params['fullname']) {
            $strFullname = trim($params['fullname']);
            $fullnameQueryString = new QueryString();
            $fullnameQueryString->setDefaultField('fullname')
                    ->setQuery($strFullname)
                    ->setDefaultOperator('AND');
            $fullnameBool = new Bool();
            $fullnameBool->addMust($fullnameQueryString);
            $boolQuery->addMust($fullnameBool);
        }

        //kiểm tra nếu có email thì add vào filter
        if ($params['email']) {
            $emailQueryString = new QueryString();
            $emailQueryString->setDefaultField('email')
                    ->setQuery($params['email'])
                    ->setDefaultOperator('AND');
            $emailBool = new Bool();
            $emailBool->addMust($emailQueryString);
            $boolQuery->addMust($emailBool);
        }

        //kiểm tra nếu có số đt thì add vào filter
        if ($params['phone']) {
            $phoneQueryString = new QueryString();
            $phoneQueryString->setDefaultField('phone')
                    ->setQuery($params['phone'])
                    ->setDefaultOperator('AND');
            $phoneBool = new Bool();
            $phoneBool->addMust($phoneQueryString);
            $boolQuery->addMust($phoneBool);
        }

        //kiểm tra nếu có Tỉnh / Thành thì add vào filter
        if (isset($params['cityID']) && $params['cityID'] >= 0) {
            //nếu params = 0 nghĩa là filter tất cả tỉnh thành trừ HN và HCM
            if ($params['cityID'] == 0) {
                $excludeHCM = new \Elastica\Query\Term();
                $excludeHCM->setTerm('city_id', General::HCM);

                $excludeHN = new \Elastica\Query\Term();
                $excludeHN->setTerm('city_id', General::HN);

                $boolQuery->addMustNot($excludeHCM);
                $boolQuery->addMustNot($excludeHN);
            } else {
                $filterCityID = new Term();
                $filterCityID->setTerm('city_id', $params['cityID']);
                $filter->addFilter($filterCityID);
            }
        }

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFilter($filter)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($this->setSort($params));
        $query = $this->setHighLight($query);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);

        $this->setResultSet($resultSet);
        $arrOrderItemList = $this->toArray();
        return $arrOrderItemList;
    }

    /**
     * Get list received date
     * @param int $receivedDateFrom
     * @param int $receivedDateTo
     * @return array received date
     */
    public function getReceivedDate($receivedDateFrom, $receivedDateTo) {
        $arrReceivedDate = array();

        if (!$receivedDateFrom || !$receivedDateTo || ($receivedDateFrom > $receivedDateTo)) {
            return $arrReceivedDate;
        }

        $agg = new \Elastica\Aggregation\Terms('group_by');
        $agg->setField('received_date');
        $agg->setSize(0)->setOrder('_term', 'asc');

        $aggFilter = new \Elastica\Aggregation\Filter('agg_filter');


        $filter = new BoolAnd();

        $filterStatus = new Term();
        $filterStatus->setTerm('item_status', 6);
        $filter->addFilter($filterStatus);

        $rangeFilter = new \Elastica\Filter\Range();
        $rangeFilter->addField('received_date', array('from' => $receivedDateFrom, 'to' => $receivedDateTo));
        $filter->addFilter($rangeFilter);


        $aggFilter->setFilter($filter);
        $aggFilter->addAggregation($agg);

        $query = new Query();
        $query->addAggregation($aggFilter);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query)
                ->getAggregation('agg_filter');

        foreach ($resultSet['group_by']['buckets'] as $result) {
            $day = date('d', $result['key']);
            $arrReceivedDate[(int) $day] = $day;
        }

        return $arrReceivedDate;
    }

    public function getOrderLate() {
        $params = $this->getParams();

        $agg = new \Elastica\Aggregation\Terms('group_by');
        $agg->setField('order_id');
        $agg->setSize(0)->setOrder('_term', 'asc');

        $aggFilter = new \Elastica\Aggregation\Filter('agg_filter');


        $filter = new BoolAnd();
        //sp co status la "Da co tracking - cho hang ve"
        $filterStatus = new Term();
        $filterStatus->setTerm('item_status', 5);
        $filter->addFilter($filterStatus);

        //thoi gian 1 tuan
        $aWeek = 7 * 24 * 60 * 60;

        $intPredictDateFrom = (int) General::dateToTimestamp(date('d'), date('m'), date('Y')) - $aWeek;
        $intPredictDateTo = (int) General::dateToTimestamp(date('d'), date('m'), date('Y'), 23, 59, 59) - $aWeek;

        if ($params['predictDateFrom'] && $params['predictDateTo']) {

            //kiểm tra và filter sản phẩm theo ngày hàng về
            list($day, $month, $year) = explode('/', $params['predictDateFrom']);
            $predictDateFrom = (int) General::dateToTimestamp($day, $month, $year);

            list($day, $month, $year) = explode('/', $params['predictDateTo']);
            $predictDateTo = (int) General::dateToTimestamp($day, $month, $year, 23, 59, 59);

            if (($predictDateFrom && $predictDateTo) && $predictDateFrom <= $predictDateTo) {
                $intPredictDateFrom = $predictDateFrom - $aWeek;
                $intPredictDateTo = $predictDateTo - $aWeek;
            }
        }

        //sp co ngay du kien hang ve + 7 ngay bang ngay hien tai
        $rangeFilter = new \Elastica\Filter\Range();
        $rangeFilter->addField('predict_date', array('from' => $intPredictDateFrom, 'to' => $intPredictDateTo));
        $filter->addFilter($rangeFilter);



        $aggFilter->setFilter($filter);
        $aggFilter->addAggregation($agg);

        $query = new Query();
        $query->addAggregation($aggFilter);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query)
                ->getAggregation('agg_filter')
        ;


        $arrOrderID = array();

        foreach ($resultSet['group_by']['buckets'] as $result) {
            $arrOrderID[] = $result['key'];
        }

        return $arrOrderID;
    }

    private function setSort($params) {
        if ($params['fullname'] || $params['email'] || $params['phone']) {
            return ['_score'];
        }
        if ($params['paymentStatus']) {
            if ($params['paymentStatus'] == 3 || $params['paymentStatus'] == 4) {
                return ['payment_date' => ['order' => 'desc']];
            }
        }
        return ['order_item_id' => ['order' => 'desc']];
    }

}
