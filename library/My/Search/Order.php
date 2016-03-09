<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Filter\Terms,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;

class Order extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'orders');
        $this->setSearchType('orderList');
    }

    public function getSearchData() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();

        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);

        $boolQuery = new Bool();
        $filter = new BoolAnd();

        //ngay tao don hang
        $intCreatedDateFrom = 0;
        $intCreatedDateTo = 0;
        if ($params['createdDateFrom'] && $params['createdDateTo']) {
            list($day, $month, $year) = explode('/', $params['createdDateFrom']);
            $intCreatedDateFrom = (int) General::dateToTimestamp($day, $month, $year);

            list($day, $month, $year) = explode('/', $params['createdDateTo']);
            $intCreatedDateTo = (int) General::dateToTimestamp($day, $month, $year, 23, 59, 59);
        }

        if (($intCreatedDateFrom && $intCreatedDateTo) && $intCreatedDateFrom <= $intCreatedDateTo) {
            $rangeFilter = new \Elastica\Filter\Range();
            $rangeFilter->addField('created_date', array('from' => $intCreatedDateFrom, 'to' => $intCreatedDateTo));
            $filter->addFilter($rangeFilter);
        } else {
            //set created_date > 0 là giá trị filter mặc định
            $rangeFilter = new \Elastica\Filter\Range();
            $rangeFilter->addField('created_date', array('from' => 0));
            $filter->addFilter($rangeFilter);
        }

        //ngay don hang duoc chuyen tien
        $intPaymentDateFrom = 0;
        $intPaymentDateTo = 0;
        if ($params['paymentDateFrom'] && $params['paymentDateTo']) {
            list($day, $month, $year) = explode('/', $params['paymentDateFrom']);
            $intPaymentDateFrom = (int) General::dateToTimestamp($day, $month, $year);

            list($day, $month, $year) = explode('/', $params['paymentDateTo']);
            $intPaymentDateTo = (int) General::dateToTimestamp($day, $month, $year, 23, 59, 59);
        }
        if (($intPaymentDateFrom && $intPaymentDateTo) && $intPaymentDateFrom <= $intPaymentDateTo) {
            $paymentDateFilter = new \Elastica\Filter\Range();
            $paymentDateFilter->addField('payment_date', array('from' => $intPaymentDateFrom, 'to' => $intPaymentDateTo));
            $filter->addFilter($paymentDateFilter);

            //filter status >= 3
            $filterPaymentStatus = new \Elastica\Filter\Range();
            $filterPaymentStatus->addField('payment_status', array('gte' => 3));
            $filter->addFilter($filterPaymentStatus);
        }

        $strFullname = $params['fullname'] ? $params['fullname'] : '*';
        $fullnameQueryString = new QueryString();
        $fullnameQueryString->setDefaultField('fullname')
                ->setQuery($strFullname)
                ->setDefaultOperator('AND');
        $fullnameBool = new Bool();
        $fullnameBool->addMust($fullnameQueryString);
        $boolQuery->addMust($fullnameBool);

        //kiểm tra có orderID hay không
        $arrOrderID = explode('-', $params['orderID'] ? $params['orderID'] : 0);
        $orderID = (int) array_pop($arrOrderID);

        //nếu có orderID thì add vào filter
        if ($orderID) {
            $filterOrderID = new Term();
            $filterOrderID->setTerm('order_id', $orderID);
            $filter->addFilter($filterOrderID);
        } else {
            if ($params['arrOrder'] && !empty($params['arrOrder'])) {
                $filterOrderID = new Terms();
                $filterOrderID->setTerms('order_id', $params['arrOrder']);
                $filter->addFilter($filterOrderID);
            }
        }

        //kiểm tra có payment status hay không, nếu có thì add vào filter 
        if ($params['paymentStatus']) {
            $arrPaymentStatus = explode(',', $params['paymentStatus']);
            $filterPaymentStatus = new Terms();
            $filterPaymentStatus->setTerms('payment_status', $arrPaymentStatus);
            $filter->addFilter($filterPaymentStatus);
        }

        //Loai order: truc tiep hay bao gia qua email
        if ($params['orderType'] && $params['orderType'] >= 0) {
            $filterOrderType = new Term();
            $filterOrderType->setTerm('create_from_quotation', $params['orderType']);
            $filter->addFilter($filterOrderType);
        }


        //Filter request từ quotation
        //quotationStatus == 2: da tao don hang
        if ($params['quotationStatus'] && $params['quotationStatus'] == 2) {
            $filterQuotationStatus = new \Elastica\Filter\Range();
            //filter status < 3
            $filterQuotationStatus->addField('payment_status', array('lt' => 3));
            $filter->addFilter($filterQuotationStatus);

            //set payment status = -1
            $params['paymentStatus'] = -1;
        }

        //Filter request từ quotation
        //quotationStatus == 3: da chuyen tien
        if ($params['quotationStatus'] && $params['quotationStatus'] == 3) {
            $filterQuotationStatus = new \Elastica\Filter\Range();
            //filter status >= 3
            $filterQuotationStatus->addField('payment_status', array('gte' => 3));
            $filter->addFilter($filterQuotationStatus);

            //set payment status = -1
            $params['paymentStatus'] = -1;
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

        if ($params['note'] && $params['note'] != '') {
            $strNote = $params['note'] ? trim($params['note']) : '*';
            $noteQueryString = new QueryString();
            $noteQueryString->setDefaultField('private_note')
                    ->setQuery($strNote)
                    ->setDefaultOperator('AND');
            $noteBool = new Bool();
            $noteBool->addMust($noteQueryString);
            $boolQuery->addMust($noteBool);
        }

        //kiểm tra nếu có Tỉnh / Thành thì add vào filter
        if ($params['cityID'] && $params['cityID'] >= 0) {
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
        $arrOrderList = $this->toArray();

        return $arrOrderList;
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
        return ['created_date' => ['order' => 'desc']];
    }

}
