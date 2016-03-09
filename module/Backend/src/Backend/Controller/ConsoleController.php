<?php

namespace Backend\Controller;

use My\General,
    My\Controller\MyController;

class ConsoleController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceContent \My\Models\Content */
    /* @var $serviceDetailContent \My\Models\DetailContent */
    /* @var $serviceImages \My\Models\Images */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        
    }

    public function indexAction() {
        die();
    }

    private function flush() {
        ob_end_flush();
        ob_flush();
        flush();
    }

    public function keyword($page) {
        $doc = '<?xml version="1.0" encoding="UTF-8"?>';
        $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $doc .= '</urlset>';
        $xml = new \SimpleXMLElement($doc);
        $this->flush();
        $intLimit = 20000;
        $instanceSearchKeyword = new \My\Search\Keywords();
        $instanceSearchKeyword->setParams(array('page' => $page, 'source' => array('word_id', 'word_slug')))->setLimit($intLimit);
        $arrKeyword = $instanceSearchKeyword->getKeyword();
        if (!empty($arrKeyword)) {
            foreach ($arrKeyword as $key => $keyword) {
                //$strProducURL = BASE_URL . $this->url()->fromRoute('product', array('productslug' => $product['prod_slug'], 'productId' => $product['prod_id']));
                $strKeywordURL = BASE_URL . '/s/tim-kiem-' . $keyword['word_slug'] . '.html';

                $url = $xml->addChild('url');
                $url->addChild('loc', $strKeywordURL);
                $url->addChild('lastmod', date('c', time()));
                $url->addChild('changefreq', 'daily');
                $url->addChild('priority', 0.6);
            }
            unset($arrKeyword);
            $link = '/xml/keyword_' . $page . '.xml';
            unlink(PUBLIC_PATH . $link);
            $result = file_put_contents(PUBLIC_PATH . $link, $xml->asXML());

            if ($result) {
                echo General::getColoredString("Sitemap " . $link . " done", 'blue', 'cyan');
                $temp = $this->keyword($page + 1);
                return $temp;
            } else {
                echo General::getColoredString("error" . $link, 'red', 'cyan');
            }
        } else {
            $this->flush();
            return $page - 1;
        }
    }

    private function content() {
        $doc = '<?xml version="1.0" encoding="UTF-8"?>';
        $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $doc .= '</urlset>';
        $xml = new \SimpleXMLElement($doc);
        $this->flush();
        $serviceContent = $this->serviceLocator->get('My\Models\Content');
        $listContent = $serviceContent->getList(array('not_cont_status' => -1, 'cont_meta_robot' => 'index'));
        foreach ($listContent as $content) {
            //$strContentURL = BASE_URL . $this->url()->fromRoute('content_detail', array('contslug' => $content['cont_slug'], 'contId' => $content['cont_id']));
            $strContentURL = BASE_URL . '/co/' . $content['cont_slug'] . '-' . $content['cont_id'] . '.html';
            $url = $xml->addChild('url');
            $url->addChild('loc', $strContentURL);
            $url->addChild('lastmod', date('c', time()));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', 0.7);
        }

        unlink(PUBLIC_PATH . '/xml/content.xml');
        $result = file_put_contents(PUBLIC_PATH . '/xml/content.xml', $xml->asXML());
        if ($result) {
            echo General::getColoredString("Sitemap content done", 'blue', 'cyan');
            $this->flush();
        }
    }

    private function brand() {
        $doc = '<?xml version="1.0" encoding="UTF-8"?>';
        $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $doc .= '</urlset>';
        $xml = new \SimpleXMLElement($doc);
        $this->flush();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $listBrand = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 1));
        foreach ($listBrand as $brand) {
            //$strBrandURL = BASE_URL . $this->url()->fromRoute('brand', array('brandSlug' => $brand['cate_slug']));
            $strBrandURL = BASE_URL . '/br/' . $brand['cate_slug'] . '.html';
            $url = $xml->addChild('url');
            $url->addChild('loc', $strBrandURL);
            $url->addChild('lastmod', date('c', time()));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', 0.7);
        }

        unlink(PUBLIC_PATH . '/xml/brand.xml');
        $result = file_put_contents(PUBLIC_PATH . '/xml/brand.xml', $xml->asXML());
        if ($result) {
            echo General::getColoredString("Sitemap brand done", 'blue', 'cyan');
            $this->flush();
        }
    }

    private function arrCateBrand() {

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $listProduct = $serviceProduct->getList(array('prod_actived' => 1));

        $arrData = [];
        $strIdBrand = "";
        $strIdCate = "";
        // Lấy danh sách tất cả các sản phẩm
        foreach ($listProduct as $key => $product) {
            $sBr = trim(trim($product['bran_id']), ",");
            $sCa = trim(trim($product['cate_id']), ",");
            if (!empty($sBr) && !empty($sCa)) {
                // $arrData[] = ['cate' => $strIdCategory, 'brand' => $strIdBrand];
                $brEx = explode(",", $sBr);
                $caEx = explode(",", $sCa);
                foreach ($caEx as $valca) {
                    foreach ($brEx as $valbr) {
                        $arrData[$valca][] = $valbr;
                    }
                }

                $strIdBrand.= $sBr . ",";
                $strIdCate.= $sCa . ",";
                ;
            }
        }
        unset($listProduct);
        $strIdBrand = trim($strIdBrand, ",");
        $strIdCate = trim($strIdCate, ",");
        $strIdBrand = implode(",", array_unique(explode(",", $strIdBrand)));
        $strIdCate = implode(",", array_unique(explode(",", $strIdCate)));

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $listBrand = $serviceCategory->getList(array('listCategoryID' => $strIdBrand, 'cate_type' => 1));
        $listCategory = $serviceCategory->getList(array('listCategoryID' => $strIdCate, 'cate_type' => 0));

        $arrCateNew = [];
        $arrBrandNew = [];
        // Định dạng lại array cate và brand 
        foreach ($listBrand as $key => $brand) {
            $arrBrandNew[$brand['cate_id']] = ['cate_id' => $brand['cate_id'], 'cate_slug' => $brand['cate_slug']];
        }
        unset($listBrand);

        foreach ($listCategory as $key => $category) {
            $arrCateNew[$category['cate_id']] = ['cate_id' => $category['cate_id'], 'cate_slug' => $category['cate_slug']];
        }


        unset($listCategory);
        $arrURL = [];
        foreach ($arrData as $key => $value) {
            $category = $arrCateNew[$key];
            foreach ($value as $idBr) {
                $brand = $arrBrandNew[$idBr];
                if (!empty($category['cate_slug']) && !empty($brand['cate_slug'])) {
                    $arrURL[] = BASE_URL . '/ca/' . $category['cate_slug'] . '-' . $category['cate_id'] . '/' . $brand['cate_slug'] . '.html';
                }
            }
        }
        return $arrURL;
    }

    private function categoryBrand($page = 0) {
        $arrData = $this->arrCateBrand();
        $limit = 20000;
        if (count($arrData) < $page * $limit) {
            return $page;
        }

        $doc = '<?xml version="1.0" encoding="UTF-8"?>';
        $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $doc .= '</urlset>';
        $xml = new \SimpleXMLElement($doc);
        $this->flush();

        for ($i = $page * $limit; $i < (($page + 1) * $limit); $i++) {
            if (!empty($arrData[$i])) {
                $url = $xml->addChild('url');
                $url->addChild('loc', $arrData[$i]);
                $url->addChild('lastmod', date('c', time()));
                $url->addChild('changefreq', 'daily');
                $url->addChild('priority', 0.8);
            }
        }

        unlink(PUBLIC_PATH . '/xml/category_brand.xml');
        $result = file_put_contents(PUBLIC_PATH . '/xml/category_brand_' . ($page + 1) . '.xml', $xml->asXML());
        if ($result) {
            echo General::getColoredString("Sitemap category brand " . ($page + 1) . " done", 'blue', 'cyan');
            $this->flush();
        }

        return $this->categoryBrand($page + 1);
    }

    private function category() {
        $doc = '<?xml version="1.0" encoding="UTF-8"?>';
        $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $doc .= '</urlset>';
        $xml = new \SimpleXMLElement($doc);
        $this->flush();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $listCategory = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 0));
        foreach ($listCategory as $category) {
            //$strCategoryURL = BASE_URL . $this->url()->fromRoute('category', array('categorySlug' => $category['cate_slug'], 'categoryID' => $category['cate_id']));
            $strCategoryURL = BASE_URL . '/ca/' . $category['cate_slug'] . '-' . $category['cate_id'] . '.html';
            $url = $xml->addChild('url');
            $url->addChild('loc', $strCategoryURL);
            $url->addChild('lastmod', date('c', time()));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', 0.7);
        }

        unlink(PUBLIC_PATH . '/xml/category.xml');
        $result = file_put_contents(PUBLIC_PATH . '/xml/category.xml', $xml->asXML());
        if ($result) {
            echo General::getColoredString("Sitemap category done", 'blue', 'cyan');
            $this->flush();
        }
    }

    private function sitemapOther() {
        $doc = '<?xml version="1.0" encoding="UTF-8"?>';
        $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $doc .= '</urlset>';
        $xml = new \SimpleXMLElement($doc);
        $this->flush();
        $arrData = ['http://megavita.vn/'];
        foreach ($arrData as $value) {
            $strCategoryURL = $value;
            $url = $xml->addChild('url');
            $url->addChild('loc', $strCategoryURL);
            $url->addChild('lastmod', date('c', time()));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', 1);
        }

        unlink(PUBLIC_PATH . '/xml/other.xml');
        $result = file_put_contents(PUBLIC_PATH . '/xml/other.xml', $xml->asXML());
        if ($result) {
            echo General::getColoredString("Sitemap orther done", 'blue', 'cyan');
            $this->flush();
        }
    }

    function _curl($url, $post = "", $usecookie = false, $header = false, $master = null) {
        $ch = curl_init();
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; rv:14.0) Gecko/20100101 Firefox/14.0.1");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if ($usecookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $usecookie);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $usecookie);
        }
        if ($header)
            curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($master)) {
            curl_multi_add_handle($master, $ch);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    function _curlMulti($url, $post = "", $usecookie = false, $header = false, $master = null) {
        $curly = array();
        $result = array();
        foreach ($url as $key => $value) {
            $curly[$key] = curl_init();
            if ($post) {
                curl_setopt($curly[$key], CURLOPT_POST, 1);
                curl_setopt($curly[$key], CURLOPT_POSTFIELDS, $post);
            }
            curl_setopt($curly[$key], CURLOPT_URL, $value);
            curl_setopt($curly[$key], CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curly[$key], CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; rv:14.0) Gecko/20100101 Firefox/14.0.1");
            curl_setopt($curly[$key], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curly[$key], CURLOPT_SSL_VERIFYHOST, 0);
            if ($usecookie) {
                curl_setopt($curly[$key], CURLOPT_COOKIEJAR, $usecookie);
                curl_setopt($curly[$key], CURLOPT_COOKIEFILE, $usecookie);
            }
            if ($header)
                curl_setopt($curly[$key], CURLOPT_HEADER, 1);
            curl_setopt($curly[$key], CURLOPT_TIMEOUT, 50);
            curl_setopt($curly[$key], CURLOPT_CONNECTTIMEOUT, 50);
            curl_setopt($curly[$key], CURLOPT_RETURNTRANSFER, 1);
            if (!empty($master)) {
                curl_multi_add_handle($master, $curly[$key]);
            }
        }
        $running = null;
        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);
        foreach ($curly as $id => $c) {
            $result[$id] = curl_multi_getcontent($c);
            //Code to fetch header info
            curl_multi_remove_handle($master, $c);
        }
        curl_multi_close($master);
        return $result;
    }

    function keywordAPI($keyword) {
        echo General::getColoredString("Dang lay du lieu tu khoa '" . $keyword . "'", 'blue', 'cyan');
        $master = curl_multi_init();
        $arrReturn = ["st" => -1, "msg" => "Không xác định", "data" => []];
        if ($keyword != "") {
            $urlKeywork = "http://api.keywordtool.io/v1/search/google?apikey=fb8cad201deea30493e29ec126911eaa5ea217bc&keyword=" . $keyword . "&country=us&language=vi&output=json";
            $result = $this->_curl($urlKeywork, "", false, false, $master);
            $arrData = json_decode($result, true);
            //p($arrData);die;
            if (!empty($arrData['results'])) {
                $arr = [];
                foreach ($arrData['results'] as $key) {
                    foreach ($key as $k => $value) {
                        $keyW = (trim($value['string']));
                        $arr[] = (trim($keyW));
                    }
                }

                $arrReturn = ["st" => 1, "msg" => "Thành công", "data" => $arr];
            } else {
                $arrReturn = ["st" => -1, "msg" => "Khong tim thay du lieu", "data" => $result];
            }
        } else {
            $arrReturn = ["st" => -1, "msg" => "Từ khóa tìm kiếm rỗng", "data" => []];
        }
        return $arrReturn;
    }

    function keywordAPIMulti($keyword) {

        //echo General::getColoredString("Dang lay du lieu tu khoa '" . $keyword . "'", 'blue', 'cyan');
        $master = curl_multi_init();
        $arrReturn = ["st" => -1, "msg" => "Không xác định", "data" => []];
        if (!empty($keyword)) {
            $arrUrl = [];
            foreach ($keyword as $value) {
                $urlKeywork = "http://api.keywordtool.io/v1/search/google?apikey=fb8cad201deea30493e29ec126911eaa5ea217bc&keyword=" . urlencode($value) . "&country=us&language=vi&output=json";
                $arrUrl[] = $urlKeywork;
            }
            //$urlKeywork = "http://api.keywordtool.io/v1/search/google?apikey=fb8cad201deea30493e29ec126911eaa5ea217bc&keyword=" . $keyword . "&country=us&language=vi&output=json";
            $result = $this->_curlMulti($arrUrl, "", false, false, $master);

            $arrResult = [];
            foreach ($result as $key => $value) {
                $arrData = json_decode($value, true);
                if (!empty($arrData['results'])) {
                    $arr = [];
                    foreach ($arrData['results'] as $key) {
                        foreach ($key as $k => $value) {
                            $keyW = (trim($value['string']));
                            $arr[] = (trim($keyW));
                        }
                    }
                    $arrResult[] = $arr;
                } else {
                    if (isset($arrData['error'])) {
                        $arrResult[] = "TIMEOUT";
                    }
                }
            }


            if (!empty($arrResult)) {
                $arrReturn = ["st" => 1, "msg" => "Thành công", "data" => $arrResult];
            } else
                $arrReturn = ["st" => -1, "msg" => "Khong tim thay du lieu", "data" => $result];
        } else {
            $arrReturn = ["st" => -1, "msg" => "Từ khóa tìm kiếm rỗng", "data" => []];
        }
        return $arrReturn;
    }

    function mixArray($arrBefore = array()) {
        $arrAfter = [];
        $num = count($arrBefore);
        for ($i = 0; $i < $num; $i++) {
            $index = array_rand($arrBefore);
            $arrAfter[] = $arrBefore[$index];
            //unset($arrBefore[$index]);
        }
        return $arrAfter;
    }

    function crawlerKeywordAction() {
        for ($j = 0; $j < 4; $j++) {
            //while (1) {
            ini_set('memory_limit', '528M');
            $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
            //slastic
            //$keyword = $serviceKeyword->getFirstDataNull();
            //        p($params);die;
            $instanceSearchKeyword = new \My\Search\Keywords();
            $keyword = current($instanceSearchKeyword->getDataNull());


            $serviceGeneral = $this->serviceLocator->get('My\Models\General');
            $generalDetail = $serviceGeneral->getDetail(array('gene_code' => 'crawler'));
            if (empty($generalDetail)) {
                $generalDetail['gene_content'] = 0;
            }
            if ($keyword['word_id'] < (int) $generalDetail['gene_content']) {
                for ($i = $keyword['word_id']; $i < $generalDetail['gene_content']; $i++) {
                    $serviceKeyword->edit(array('word_iscrawler' => 1, 'word_data' => '[]'), $i);
                }
                $keyword = $serviceKeyword->getDetail(array('word_id' => $generalDetail['gene_content']));
            }

            $serviceKeyword->edit(array('word_iscrawler' => 2, 'word_data' => '[]'), $keyword['word_id']);
            $dataResult = $this->keywordAPI($keyword['word_key']);
            //print_r($keyword);die();
            if ((int) $dataResult['st'] == 1) {

                $arrData = $dataResult['data'];
                // print_r($arrData);
                $arrData = array_unique($arrData);

                if (!empty(array_search($keyword['word_key'], $arrData))) {
                    unset($arrData[array_search($keyword['word_key'], $arrData)]);
                }
                $dataJson = str_replace("'", "\'", json_encode($arrData, JSON_UNESCAPED_UNICODE));

                //echo $dataJson;
                $serviceKeyword->edit(array('word_data' => $dataJson, 'word_iscrawler' => 1), $keyword['word_id']);
                // Update elasticsearch 

                $arrDocument = [];
                $getDetailKeyword = $serviceKeyword->getDetail(array('word_id' => $keyword['word_id']));
                if (!empty($getDetailKeyword)) {
                    $arrDataElastic = [
                        'word_id' => (int) $getDetailKeyword['word_id'],
                        'word_key' => $getDetailKeyword['word_key'],
                        'word_slug' => $getDetailKeyword['word_slug'],
                        'word_data' => $getDetailKeyword['word_data'],
                        'word_status' => (int) $getDetailKeyword['word_status'],
                        'word_parent' => (int) $getDetailKeyword['word_parent'],
                        'word_samelevel' => $getDetailKeyword['word_samelevel'],
                        'word_level' => (int) $getDetailKeyword['word_level'],
                        'word_loop' => (int) $getDetailKeyword['word_loop'],
                        'word_volume' => (int) $getDetailKeyword['word_volume'],
                        'word_iscrawler' => (int) $getDetailKeyword['word_iscrawler']
                    ];
                    $arrDocument[] = new \Elastica\Document($getDetailKeyword['word_id'], $arrDataElastic);
                    echo General::getColoredString("Created document Update for word_id=" . $getDetailKeyword['word_id'] . " Successfully", 'cyan');
                    $this->flush();
                }

                if ($keyword['word_loop'] != $keyword['word_level']) {
                    $arrKeyword = [];
                    $arrSlug = [];
                    foreach ($arrData as $value) {
                        $word_slug = General::getSlug($value);
                        $mixArr = $this->mixArray($arrData);
                        $arrCondition = array(
                            'word_key' => str_replace("'", "`", $value),
                            'word_slug' => $word_slug,
                            'word_parent' => $keyword['word_id'],
                            'word_samelevel' => str_replace("'", "\'", json_encode($mixArr, JSON_UNESCAPED_UNICODE)),
                            'word_loop' => $keyword['word_loop'],
                            'word_level' => $keyword['word_level'] + 1,
                            'word_data' => NULL,
                        );
                        $arrSlug[] = strtolower($word_slug);
                        if ($keyword['word_loop'] == $keyword['word_level'] + 1) {
                            $arrCondition['word_data'] = '[]';
                            $arrCondition['word_iscrawler'] = 1;
                        }
                        $arrKeyword[] = $arrCondition;
                    }

                    echo General::getColoredString("Chuan bi add vao danh sach", 'cyan');
                    $serviceKeyword->AddList($arrKeyword);
                    // sleep(5);
                    echo General::getColoredString("Da add vao danh sach thanh cong", 'cyan');
                    if (!empty($arrSlug)) {
                        //$arrDocument = [];
                        $result = $serviceKeyword->getList(['word_slug_array' => $arrSlug]);
                        foreach ($result as $key => $value) {
                            $arrDataElastic = [
                                'word_id' => (int) $value['word_id'],
                                'word_key' => $value['word_key'],
                                'word_name' => $value['word_key'],
                                'word_slug' => $value['word_slug'],
                                'word_data' => $value['word_data'],
                                'word_status' => (int) $value['word_status'],
                                'word_parent' => (int) $value['word_parent'],
                                'word_samelevel' => $value['word_samelevel'],
                                'word_level' => (int) $value['word_level'],
                                'word_loop' => (int) $value['word_loop'],
                                'word_volume' => (int) $value['word_volume'],
                                'word_iscrawler' => (int) $value['word_iscrawler']
                            ];
                            $arrDocument[] = new \Elastica\Document($value['word_id'], $arrDataElastic);
                            echo General::getColoredString("Created document for word_id=" . $value['word_id'] . " Successfully", 'cyan');
                            $this->flush();
                        }
                    }
                }

                if (!empty($arrDocument)) {
                    $instanceSearchKeyword = new \My\Search\Keywords();
                    //$instanceSearchKeyword->createIndex();
                    $result = $instanceSearchKeyword->add($arrDocument);
                    if ($result) {
                        echo General::getColoredString("Migrated all " . count($arrDocument) . " document Successfully", 'blue', 'cyan');
                        $this->flush();
                    }
                } else {
                    echo General::getColoredString("Khong ton tai arrDocument", 'blue', 'cyan');
                }
            } else {
                echo General::getColoredString("Khong co du lieu tra ve tu API", 'blue', 'cyan');
                // Không tìm thấy kết quả từ API sẽ update lại tình trạng của keyword
//            $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
//            $serviceKeyword->edit(array('word_iscrawler' => 1), $keyword['word_id']);
            }
            echo General::getColoredString("crawler thanh cong", 'blue', 'cyan');
        }
    }

    function crawlerKeywordMultiAction() {
        die("lock");
        ini_set('memory_limit', '528M');
        $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
        //$arrKeyword = $serviceKeyword->getDataNull();
        $instanceSearchKeyword = new \My\Search\Keywords();
        $arrKeyword = $instanceSearchKeyword->getDataNull();
// jump keyword
//        $serviceGeneral = $this->serviceLocator->get('My\Models\General');
//        $generalDetail = $serviceGeneral->getDetail(array('gene_code' => 'crawler'));
//        if (empty($generalDetail)) {
//            $generalDetail['gene_content'] = 0;
//        }
//
//        if ($keyword['word_id'] < (int) $generalDetail['gene_content']) {
//            for ($i = $keyword['word_id']; $i < $generalDetail['gene_content']; $i++) {
//                $serviceKeyword->edit(array('word_iscrawler' => 1, 'word_data' => '[]'), $i);
//            }
//            $keyword = $serviceKeyword->getDetail(array('word_id' => $generalDetail['gene_content']));
//        }
//end jump
        $listKeyword = array();
        if (!empty($arrKeyword)) {
            foreach ($arrKeyword as $key => $keyword) {
                $keyword['word_iscrawler'] = 2;
                $keyword['word_data'] = '[]';
                $arrDocument[] = new \Elastica\Document($keyword['word_id'], $keyword);
                $instanceSearchKeyword = new \My\Search\Keywords();
                $result = $instanceSearchKeyword->add($arrDocument);
                $listKeyword[$key] = $keyword['word_key'];
            }
        }
        if (!empty($listKeyword)) {
            $dataResult = $this->keywordAPIMulti($listKeyword);
        }
        unset($listKeyword);
        if ((int) $dataResult['st'] == 1) {
            $arrData = $dataResult['data'];
            // p($arrData);die;
            $arrSlug = [];
            $arrDataElastic = [];
            $arrWordCrawler = [];
            foreach ($arrData as $key => $listKey) {
                $word = $arrKeyword[$key];
                $word_id = $word['word_id'];
                $word_loop = $word['word_loop'];
                $word_level = $word['word_level'];
                if ($listKey != "TIMEOUT") {
                    $listKey = array_unique($listKey);
                    if (in_array($arrKeyword[$key]['word_key'], $listKey)) {
                        unset($listKey[array_search($arrKeyword[$key]['word_key'], $listKey)]);
                    }
                    $dataJson = str_replace("'", "`", json_encode($listKey, JSON_UNESCAPED_UNICODE));

                    $serviceKeyword->edit(array('word_data' => $dataJson, 'word_iscrawler' => 1), $word_id);

                    // Update elasticsearch
                    $word['word_data'] = $dataJson;
                    $word['word_iscrawler'] = 1;
                    $arrDataElastic [] = $word;
                    if ($word_loop != $word_level) {
                        $arrKeywordChild = [];
                        foreach ($listKey as $value) {
                            $word_slug = General::getSlug($value);
                            shuffle($listKey);
                            $arrCondition = array(
                                'word_key' => str_replace("'", "`", $value),
                                'word_slug' => $word_slug,
                                'word_parent' => $word_id,
                                'word_samelevel' => str_replace("'", "\'", json_encode($listKey, JSON_UNESCAPED_UNICODE)),
                                'word_loop' => $word_loop,
                                'word_level' => $word_level + 1,
                                'word_data' => NULL,
                                'word_iscrawler' => 0,
                            );
                            $arrSlug[] = strtolower($word_slug);
                            if ($word_loop == $word_level + 1) {
                                $arrCondition['word_data'] = '[]';
                                $arrCondition['word_iscrawler'] = 1;
                            }
                            $arrKeywordChild[] = $arrCondition;
                            $arrWordCrawler[] = $arrCondition;
                        }
                        $serviceKeyword->AddList($arrKeywordChild);
                        echo General::getColoredString("Da add vao danh sach thanh cong", 'cyan');
                    }
                } else {
                    $word['word_data'] = '[]';
                    $word['word_iscrawler'] = 0;
                    $arrDataElastic [] = $word;
                }
            }
            if (!empty($arrSlug)) {
                $result = $serviceKeyword->getList(['word_slug_array' => $arrSlug]);
                foreach ($result as $key => $value) {
                    $arrDataElastic [] = $value;
                }
            }
            if (!empty($arrDataElastic)) {
                $this->insertElastic($arrDataElastic);
            }
//            }
        } else {
            echo General::getColoredString("Khong co du lieu tra ve tu API", 'blue', 'cyan');
        }
        echo General::getColoredString("crawler thanh cong", 'blue', 'cyan');
        die();
    }

    function updateIsCrawlerAction() {
        ini_set('memory_limit', '528M');
        $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');

        $instanceSearchKeyword = new \My\Search\Keywords();
        $arrKeyword = $serviceKeyword->getList(array('word_data' => '[]', 'word_level_word_loop' => true));
        $arrDataElastic = [];
        if (!empty($arrKeyword)) {
            foreach ($arrKeyword as $key => $value) {
                $arrDataElastic [] = [
                    'word_id' => (int) $value['word_id'],
                    'word_key' => $value['word_key'],
                    'word_name' => $value['word_key'],
                    'word_slug' => $value['word_slug'],
                    'word_data' => $value['word_data'],
                    'word_status' => (int) $value['word_status'],
                    'word_parent' => (int) $value['word_parent'],
                    'word_samelevel' => $value['word_samelevel'],
                    'word_level' => (int) $value['word_level'],
                    'word_loop' => (int) $value['word_loop'],
                    'word_volume' => (int) $value['word_volume'],
                    'word_iscrawler' => 0
                ];
            }
        }

        if (!empty($arrDataElastic)) {
            $this->insertElastic($arrDataElastic);
        }

        echo General::getColoredString("crawler thanh cong", 'blue', 'cyan');
        die();
    }

    public function insertElastic($array = [], $level = 1) {
        $arrDocument = [];
        $numSet = 400;
        $loop = (count($array) > $numSet) ? $numSet : count($array);
        $level = ($level < 1) ? 1 : $level;
        $noteLoop = $loop * $level;
        // chỗ này sai kiểm tra lại
        if ($noteLoop > count($array) && count($array) > $numSet)
            $noteLoop = $noteLoop - $numSet + (count($array) % $numSet);


        for ($i = ($numSet * ($level - 1)); $i < $noteLoop; $i++) {
            $arrDataElastic = [
                'word_id' => (int) $array[$i]['word_id'],
                'word_key' => $array[$i]['word_key'],
                'word_name' => $array[$i]['word_key'],
                'word_slug' => $array[$i]['word_slug'],
                'word_data' => $array[$i]['word_data'],
                'word_status' => (int) $array[$i]['word_status'],
                'word_parent' => (int) $array[$i]['word_parent'],
                'word_samelevel' => $array[$i]['word_samelevel'],
                'word_level' => (int) $array[$i]['word_level'],
                'word_loop' => (int) $array[$i]['word_loop'],
                'word_volume' => (int) $array[$i]['word_volume'],
                'word_iscrawler' => (int) $array[$i]['word_iscrawler']
            ];
            $arrDocument[] = new \Elastica\Document($array[$i]['word_id'], $arrDataElastic);
        }
        echo General::getColoredString("Loop Level " . $level . " in total " . count($array), 'blue', 'cyan');

        if (!empty($arrDocument)) {
            $instanceSearchKeyword = new \My\Search\Keywords();
            $result = $instanceSearchKeyword->add($arrDocument);
            if ($result) {
                echo General::getColoredString("Migrated all " . count($arrDocument) . " document Successfully", 'blue', 'cyan');
                $this->flush();
            }
        }
        if ($noteLoop > count($array)) {
            return;
        } else {
            $this->insertElastic($array, $level + 1);
        }
    }

    public function migrateAction() {
        $params = $this->request->getParams();
        $intIsCreateIndex = (int) $params['createindex'];

        if (empty($params['type'])) {
            return General::getColoredString("Unknown type \n", 'light_cyan', 'red');
        }

        switch ($params['type']) {
            case 'logs':
                $this->__migrateLogs($intIsCreateIndex);
                break;

            default:
                echo General::getColoredString("Unknown type \n", 'light_cyan', 'red');
                break;
        }
    }

    public function __migrateLogs($intIsCreateIndex) {
        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
        $intLimit = 1000;
        $instanceSearchLogs = new \My\Search\Logs();

        for ($intPage = 1; $intPage < 10000; $intPage ++) {
            $arrLogsList = $serviceLogs->getListLimit([], $intPage, $intLimit, 'logs_user_id ASC');

            if (empty($arrLogsList)) {
                break;
            }

            if ($intPage == 1) {
                if ($intIsCreateIndex) {
                    $instanceSearchLogs->createIndex();
                } else {
                    $result = $instanceSearchLogs->removeAllDoc();
                    if (empty($result)) {
                        $this->flush();
                        return General::getColoredString("Cannot delete old search index \n", 'light_cyan', 'red');
                    }
                }
            }

            $arrDocument = [];
            foreach ($arrLogsList as $arrLogs) {
                $logsUserId = (int) $arrLogs['logs_user_id'];
                $arrData = array(
                    'logs_user_id' => $logsUserId,
                    'user_id' => (int) $arrLogs['user_id'],
                    'logs_controller' => $arrLogs['logs_controller'],
                    'logs_action' => $arrLogs['logs_action'],
                    'logs_time' => (int) $arrLogs['logs_time'],
                    'logs_detail' => $arrLogs['logs_detail']
                );

                $arrDocument[] = new \Elastica\Document($logsUserId, $arrData);
                echo General::getColoredString("Created new document with logs_user_id = " . $logsUserId . " Successfully", 'cyan');

                $this->flush();
            }

            unset($arrLogsList); //release memory
            echo General::getColoredString("Migrating " . count($arrDocument) . " documents, please wait...", 'yellow');
            $this->flush();

            $instanceSearchLogs->add($arrDocument);
            echo General::getColoredString("Migrated " . count($arrDocument) . " documents successfully", 'blue', 'cyan');

            unset($arrDocument);
            $this->flush();
        }

        die('done');
    }

    public function workerAction() {
        $params = $this->request->getParams();
        //stop job sendmail
        if ($params['stop'] === 'megavita-sendmail') {
            if ($params['type'] || $params['background']) {
                return General::getColoredString("Invalid params \n", 'light_cyan', 'red');
            }
            exec("ps -ef | grep -v grep | grep 'type=megavita-sendmail' | awk '{ print $2 }'", $PID);
            $PID = current($PID);
            if ($PID) {
                shell_exec("kill " . $PID);
                echo General::getColoredString("Job megavita-sendmail is stopped running in backgound \n", 'green');
                return;
            } else {
                echo General::getColoredString("Cannot found PID \n", 'light_cyan', 'red');
                return;
            }
        }

        $worker = General::getWorkerConfig();
        //  die($params['type']);
        switch ($params['type']) {
            case 'megavita-sendmail':
                //start job in background
                if ($params['background'] === 'true') {
                    $PID = shell_exec("nohup php " . PUBLIC_PATH . "/index.php worker --type=megavita-sendmail >/dev/null & echo 2>&1 & echo $!");
                    if (empty($PID)) {
                        echo General::getColoredString("Cannot deamon PHP process to run job megavita-sendmail in background. \n", 'light_cyan', 'red');
                        return;
                    }
                    echo General::getColoredString("Job megavita-sendmail is running in background ... \n", 'green');
                }
                //      echo "sss";
                $worker->addFunction(SEARCH_PREFIX . 'send_mail', '\My\Job\JobSendEmail::send', $this->serviceLocator);
                break;

            default:
                return General::getColoredString("Invalid or not found function \n", 'light_cyan', 'red');
        }

        if (empty($params['background'])) {
            echo General::getColoredString("Waiting for job...\n", 'green');
        } else {
            return;
        }
        $this->flush();
        while (@$worker->work() || ($worker->returnCode() == GEARMAN_IO_WAIT) || ($worker->returnCode() == GEARMAN_NO_JOBS)) {
            if ($worker->returnCode() != GEARMAN_SUCCESS) {
                echo "return_code: " . $worker->returnCode() . "\n";
                break;
            }
        }
    }

    public function checkWorkerRunningAction() {
        //check send-mail worker
        exec("ps -ef | grep -v grep | grep 'type=megavita-sendmail' | awk '{ print $2 }'", $PID);
        $PID = current($PID);

        if (empty($PID)) {
            $PID = shell_exec("nohup php " . PUBLIC_PATH . "/index.php worker --type=megavita-sendmail >/dev/null & echo 2>&1 & echo $!");
            if (empty($PID)) {
                echo General::getColoredString("Cannot deamon PHP process to run job megavita-sendmail in background. \n", 'light_cyan', 'red');
                return;
            }
        }
    }

    public function changeImagesAction() {
        // $params = $this->request->getParams();
        $params = $this->params()->fromQuery();
        $page = empty($params["page"]) ? 1 : $params["page"];
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrProductList = $serviceProduct->getImages(['prod_id_more' => 856], $page, 300, 'prod_id DESC');
        $arrData = [];
        foreach ($arrProductList as $key => $value) {
//            // prod_image
//            $arrProdImage = json_decode($value["prod_image"], true);
//            $arrProdImage["sourceImage"] = str_replace("/thumbs/100x100thumb_", "/", $arrProdImage['sourceImage']);
            // prod_sub
            $arrProdImageSub = json_decode($value["prod_image_sub"], true);
            $arrDataSub = [];
            if (!empty($arrProdImageSub)) {
                foreach ($arrProdImageSub as $k => $val) {
                    $arrSub = [];
                    $arrSub = json_decode($val, true);
                    $arrSub["sourceImage"] = str_replace("/thumbs/100x100thumb_", "/", $arrSub['sourceImage']);
                    $arrDataSub[] = json_encode($arrSub);
                }
//'prod_image' => json_encode($arrProdImage), 
                $serviceProduct->edit(['prod_image_sub' => json_encode($arrDataSub)], $value['prod_id']);
            }
        }

        $arrProductList = $serviceProduct->getImages(['prod_id_more' => 856], $page, 300, 'prod_id DESC');
        echo count($arrProductList);
        //print_r($arrProductList);
        die();
    }

}
