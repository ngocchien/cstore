<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class TemplateController extends MyController {

    public function __construct() {
        $this->defaultCSS = [
            'backend:template:add' => 'gallery.css',
            'backend:template:edit' => 'sumoselect.css',
        ];
        $this->externalJS = [
            'backend:template:edit' => array(
                STATIC_URL . '/b/js/library/??dataTables.bootstrap-1.10.5.min.js,dataTables.bootstrap.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
                STATIC_URL . '/b/js/library/??Nileupload-min.js',
                STATIC_URL . '/b/js/library/??modernizr.custom.js',
                STATIC_URL . '/b/js/library/??jquery.sumoselect.min.js',
                STATIC_URL . '/b/js/my/template.js',
            ),
            'backend:template:index' => array(
                STATIC_URL . '/b/js/my/template.js',
            ),
            'backend:template:add' => array(
                STATIC_URL . '/b/js/library/??modernizr.custom.js',
            )
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $arrCondition = array('not_status' => -1);
        $serviceTemplate = $this->serviceLocator->get('My\Models\Template');
        $arrTemplate = $serviceTemplate->getList($arrCondition);
        return array(
            'arrTemplate' => $arrTemplate,
        );
    }

    public function addAction() {

        $params = $this->params()->fromRoute();
        $template = $this->params()->fromPost('template');
        if ($this->request->isPost()) {
            switch ($template) {
                case 1: 
                    break;
                case 2:
                    $arrData = array(
                        'title' => 'TOP SẢN PHẨM NGƯỜI DÙNG CHỌN LỰA',
                        'json_menu' => '{"left":[{"name":"T\u1ea9y \u0111\u1ed9c c\u01a1 th\u1ec3, \u0111\u00e0o th\u1ea3i \u0111\u1ed9c","url":"giaonhan247.vn","sort":"2"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c112","url":"proship.12","sort":"2"},{"name":"B\u1ed3i b\u1ed5","url":"muathuoctot.com","sort":"2"},{"name":"S\u1ee9c kh\u1ecfe l\u00e0 v\u00e0ng","url":"muathuoctot.com","sort":"3"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe2","url":"312","sort":"2"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe2","url":"3","sort":"3"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"}],"top":[{"name":"menu123","url":"dang test","sort":"2"},{"name":"menu1","url":"#","sort":"1"},{"name":"menu2","url":"#"}]}',
                        'json_image' => '[{"title":"add","url":"\u00e1d","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428551994.2054.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428551994.2054.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428551994.2054.jpg\"}}","sort":"1"},{"title":"proship","url":"proship.vn","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428552043.1572.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428552043.1572.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428552043.1572.png\"}}","sort":"2"},{"title":"kinh chu123","url":"megavita.vn","sort":"32","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428554224.0916.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428554224.0916.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428554224.0916.jpg\"}}"},{"title":"chuc m\u1eebng n\u0103m m\u1edbi","url":"proship.com","sort":"2","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428565898.1728.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428565898.1728.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428565898.1728.png\"}}"}]',
                        'json_product' => null,
                        'template' => $template,
                        'category_id' => null,
                        'html_form' => null,
                        'sort' => 6,
                        'is_mobile'=>0
                    );
                    break;
                case 3:
                    $arrData = array(
                        'title' => 'TOP SẢN PHẨM NGƯỜI DÙNG CHỌN LỰA',
                        'json_menu' => '{"left":[{"name":"T\u1ea9y \u0111\u1ed9c c\u01a1 th\u1ec3, \u0111\u00e0o th\u1ea3i \u0111\u1ed9c","url":"giaonhan247.vn","sort":"2"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c112","url":"proship.12","sort":"2"},{"name":"B\u1ed3i b\u1ed5","url":"muathuoctot.com","sort":"2"},{"name":"S\u1ee9c kh\u1ecfe l\u00e0 v\u00e0ng","url":"muathuoctot.com","sort":"3"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe2","url":"312","sort":"2"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe2","url":"3","sort":"3"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"}],"top":[{"name":"menu123","url":"dang test","sort":"2"},{"name":"menu1","url":"#","sort":"1"},{"name":"menu2","url":"#"}]}',
                        'json_image' => '[{"title":"add","url":"\u00e1d","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428551994.2054.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428551994.2054.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428551994.2054.jpg\"}}","sort":"1"},{"title":"proship","url":"proship.vn","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428552043.1572.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428552043.1572.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428552043.1572.png\"}}","sort":"2"},{"title":"kinh chu123","url":"megavita.vn","sort":"32","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428554224.0916.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428554224.0916.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428554224.0916.jpg\"}}"},{"title":"chuc m\u1eebng n\u0103m m\u1edbi","url":"proship.com","sort":"2","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428565898.1728.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428565898.1728.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428565898.1728.png\"}}"}]',
                        'json_product' => null,
                        'template' => $template,
                        'category_id' => null,
                        'html_form' => null,
                        'sort' => 6,
                        'is_mobile'=>0
                    );
                    break;
                case 4:
                    $arrData = array(
                        'title' => 'SẢN PHẨM GỢI Ý THEO MÙA',
                        'json_menu' => '{"left":[{"name":"T\u1ea9y \u0111\u1ed9c c\u01a1 th\u1ec3, \u0111\u00e0o th\u1ea3i \u0111\u1ed9c","url":"giaonhan247.vn","sort":"2"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c112","url":"proship.12","sort":"2"},{"name":"B\u1ed3i b\u1ed5","url":"muathuoctot.com","sort":"2"},{"name":"S\u1ee9c kh\u1ecfe l\u00e0 v\u00e0ng","url":"muathuoctot.com","sort":"3"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe2","url":"312","sort":"2"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe2","url":"3","sort":"3"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"},{"name":"B\u1ed3i b\u1ed5 s\u1ee9c kh\u1ecfe","url":"#","sort":"1"}],"top":[{"name":"menu123","url":"dang test","sort":"2"},{"name":"menu1","url":"#","sort":"1"},{"name":"menu2","url":"#"}]}',
                        'json_image' => '[{"title":"add","url":"\u00e1d","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428551994.2054.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428551994.2054.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428551994.2054.jpg\"}}","sort":"1"},{"title":"proship","url":"proship.vn","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428552043.1572.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428552043.1572.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428552043.1572.png\"}}","sort":"2"},{"title":"kinh chu123","url":"megavita.vn","sort":"32","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428554224.0916.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428554224.0916.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428554224.0916.jpg\"}}"},{"title":"chuc m\u1eebng n\u0103m m\u1edbi","url":"proship.com","sort":"2","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428565898.1728.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428565898.1728.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428565898.1728.png\"}}"}]',
                        'json_product' => '[{"title":"M\u1edbi v\u00e0 n\u1ed5i b\u1eadt","item":[5,6,8,9]},{"title":"b\u00e1n ch\u1ea1y","item":[5,6,8,9]},{"title":"Khuy\u1ebfn M\u00e3i","item":[5,6,8,9]}]',
                        'template' => $template,
                        'category_id' => '94,117,94,117',
                        'html_form' => '<p><img alt="" src="/uploads/Topic/2015/03/13/muathuoctot.com_142623819519195637.jpg" style="height:244px; width:278px" /></p>',
                        'sort' => 6,
                        'is_mobile'=>0
                    );
                    break;
                case 5:
                    $arrData = array(
                        'title' => 'vession mobite',
                        'json_image' => '[{"title":"add","url":"\u00e1d","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428551994.2054.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428551994.2054.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428551994.2054.jpg\"}}","sort":"1"},{"title":"proship","url":"proship.vn","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428552043.1572.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428552043.1572.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428552043.1572.png\"}}","sort":"2"},{"title":"kinh chu123","url":"megavita.vn","sort":"32","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428554224.0916.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428554224.0916.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428554224.0916.jpg\"}}"},{"title":"chuc m\u1eebng n\u0103m m\u1edbi","url":"proship.com","sort":"2","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428565898.1728.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428565898.1728.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428565898.1728.png\"}}"}]',
                        'template' => $template,
                        'sort' => 1,
                        'is_mobile'=>1
                    );
                    break;
                case 6:
                    $arrData = array(
                        'title' => 'TOP SẢN PHẨM NGƯỜI DÙNG CHỌN LỰA',
                        'json_image' => '[{"title":"add","url":"\u00e1d","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428551994.2054.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428551994.2054.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428551994.2054.jpg\"}}","sort":"1"},{"title":"proship","url":"proship.vn","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428552043.1572.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428552043.1572.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428552043.1572.png\"}}","sort":"2"},{"title":"kinh chu123","url":"megavita.vn","sort":"32","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428554224.0916.jpg\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428554224.0916.jpg\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428554224.0916.jpg\"}}"},{"title":"chuc m\u1eebng n\u0103m m\u1edbi","url":"proship.com","sort":"2","img":"{\"sourceImage\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/1428565898.1728.png\",\"thumbImage\":{\"150x100\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/150x100_1428565898.1728.png\",\"50x40\":\"http:\/\/staging.st.megavita.vn\/uploads\/banners\/2015\/04\/09\/thumbs\/50x40_1428565898.1728.png\"}}"}]',
                        'template' => $template,
                        'sort' => 2,
                        'is_mobile'=>1
                    );
                    break;
            }
            $serviceTemplate = $this->serviceLocator->get('My\Models\Template');

            $add = $serviceTemplate->add($arrData);
            if ($add) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                            'user_id'=>UID,
                            'logs_controller'=>'Template',
                            'logs_action'=>'add',
                            'logs_time'=>time(),
                            'logs_detail'=>'Thêm template có id = '.$add,
                        );
                $serviceLogs -> add($arrLogs);
//                return $this->getResponse()->setContent('');
                $this->redirect()->toRoute('backend', array('controller' => 'template', 'action' => 'edit', 'id' => $add));
            }
        }
        return array(
        );
    }

    public function editAction() {
        $serviceTemplate = $this->serviceLocator->get('My\Models\Template');
        if ($this->request->isPost()) {
            $arrayReturn = array('error' => 1, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại');
            $post = $this->params()->fromPost();
            $template = $serviceTemplate->getDetail($post['tem_id']);
            switch ($template['template']) {
                case 1:

                    if ($post['status'] == 'title') { // Sửa title
                        $template['title'] = $post['title'];
                        $template['sort'] = $post['sort'];
                        $template['status'] = $post['sta'];
                        $update = $serviceTemplate->edit($template, $post['tem_id']);
                        if ($update) {
                            $arrayReturn = array('error' => 0, 'message' => 'Cập nhật tiêu đề thành công');
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'menutop') {
                        $json_menu = json_decode($template['json_menu'], TRUE);
                        if ($post['action'] == 'edit') {
                            $json_menu['top'][$post['key']]['title'] = $post['title'];
                            $json_menu['top'][$post['key']]['url'] = $post['url'];
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            unset($json_menu['top'][$post['key']]);
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Xóa menu thành công');
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                            );
                            $count = count($json_menu['top']);
                            $json_menu['top'] = array_values($json_menu['top']);
                            $json_menu['top'][$count] = $data;
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm menu thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'default') { // them xoa sua banner de
                        $json_menu = json_decode($template['json_menu'], TRUE);

                        if ($post['action'] == 'edit') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                                'summary' => $post['summary'],
                                'img' => $post['img' . $post['key']],
                            );
                            if ($data['img'] == '' || !isset($data['img']) || !$data['img']) {
                                $data['img'] = $json_menu['default'][$post['key']]['img'];
                            }
                            $json_menu['default'][$post['key']] = $data;
//                            p( $json_menu['default']);
//                            $json_menu = array_values($json_menu);
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            if (isset($post['key'])) {
                                unset($json_image[$post['key']]);
                                $json_image = array_values($json_image);
                                $template['json_image'] = json_encode($json_image);
                                $update = $serviceTemplate->edit($template, $post['tem_id']);
                                if ($update) {
                                    $arrayReturn = array('error' => 0, 'message' => 'Xóa banner thành công');
                                }
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'img' => $post['img'],
                                'sort' => $post['sort'],
                            );
                            $count = count($json_image);
                            $json_image[$count] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm banner thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'menuleft_tem1') {
                        $json_menu = json_decode($template['json_menu'], TRUE);
                        if ($post['action'] == 'title') {
                            $json_menu['left'][$post['_keyL']]['title'] = $post['title'];
                            $json_menu['left'][$post['_keyL']]['url'] = $post['url'];
                            $json_menu['left'][$post['_keyL']]['classicon'] = $post['classicon'];
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                            return $this->getResponse()->setContent(json_encode($arrayReturn));
                        } elseif ($post['action'] == 'edit') {
                            if ($post['parent'] == 'null') { // update menu cha
                                $json_menu['left'][$post['_keyL']]['category'][$post['key']]['title'] = $post['title'];
                                $json_menu['left'][$post['_keyL']]['category'][$post['key']]['url'] = $post['url'];
                            } else {
                                $json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'][$post['key']]['title'] = $post['title'];
                                $json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'][$post['key']]['url'] = $post['url'];
                            }
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            if ($post['parent'] == 'null') { // update menu cha
                                if (isset($json_menu['left'][$post['_keyL']]['category'][$post['key']]) && !empty($json_menu['left'][$post['_keyL']]['category'][$post['key']])) {
                                    unset($json_menu['left'][$post['_keyL']]['category'][$post['key']]);
                                } else {
                                    $arrayReturn = array('error' => 1, 'status' => 1, 'message' => 'Dữ liệu đã được xóa trước đó, xin vui long refresh lại trang để được cập nhật');
                                    return $this->getResponse()->setContent(json_encode($arrayReturn));
                                }
                            } else {
                                if (isset($json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'][$post['key']]) && !empty($json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'][$post['key']])) {
                                    unset($json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'][$post['key']]);
                                } else {
                                    $arrayReturn = array('error' => 1, 'status' => 1, 'message' => 'Dữ liệu đã được xóa trước đó, xin vui long refresh lại trang để được cập nhật');
                                    return $this->getResponse()->setContent(json_encode($arrayReturn));
                                }
                            }
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'add') {
                            if ($post['parent'] == 'null') {
                                $json_menu['left'][$post['_keyL']]['category'] = array_values($json_menu['left'][$post['_keyL']]['category']);
                                $count = count($json_menu['left'][$post['_keyL']]['category']);
                                $data = array(
                                    'title' => $post['title'],
                                    'url' => $post['url'],
                                    'sub' => array(),
                                );
                                $json_menu['left'][$post['_keyL']]['category'][$count] = $data;
                            } else {
                                $json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'] = array_values($json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub']);
                                $count = count($json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub']);
                                $data = array(
                                    'title' => $post['title'],
                                    'url' => $post['url'],
                                );
                                $json_menu['left'][$post['_keyL']]['category'][$post['parent']]['sub'][$count] = $data;
                            }
//                            p($json_menu['left'][$post['_keyL']]['category'][$post['parent']]);die();
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Thêm danh mục thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'banner_tem1') {
                        $json_menu = json_decode($template['json_menu'], TRUE);
                        if ($post['action'] == 'edit') {
                            $json_menu['left'][$post['_keyL']]['banner'][$post['key']]['title'] = $post['title'];
                            $json_menu['left'][$post['_keyL']]['banner'][$post['key']]['url'] = $post['url'];
                            if (isset($post['img' . $post['_keyL'] . '_' . $post['key']]) && !empty($post['img' . $post['_keyL'] . '_' . $post['key']])) {
                                $img = $post['img' . $post['_keyL'] . '_' . $post['key']];
                            } else {
                                $img = $json_menu['left'][$post['_keyL']]['banner'][$post['key']]['img'];
                            }
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'img' => $img,
                                'sort' => $post['sort']
                            );
                            $json_menu['left'][$post['_keyL']]['banner'][$post['key']] = $data;
//                            p($json_menu['left'][$post['_keyL']]['banner'][$post['key']]);die();
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            if (isset($json_menu['left'][$post['_keyL']]['banner'][$post['key']]) && !empty($json_menu['left'][$post['_keyL']]['banner'][$post['key']])) {
                                unset($json_menu['left'][$post['_keyL']]['banner'][$post['key']]);
                            } else {
                                $arrayReturn = array('error' => 1, 'message' => 'Dữ liệu không tồn tại hoặc đã được xóa trước đó');
                                return $this->getResponse()->setContent(json_encode($arrayReturn));
                            }

                            $json_menu['left'][$post['_keyL']]['banner'] = array_values($json_menu['left'][$post['_keyL']]['banner']);
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Xóa dữ liệu thành công');
                            }
                        } elseif ($post['action'] == 'add') {
                            $json_menu['left'][$post['_keyL']]['banner'] = array_values($json_menu['left'][$post['_keyL']]['banner']);
                            $count = count($json_menu['left'][$post['_keyL']]['banner']);
                            if (isset($post['img']) && !empty($post['img'])) {
                                $img = $post['img'];
                            } else {
                                $img = $json_menu['left'][$post['_keyL']]['banner'][$post['key']]['img'];
                            }
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                                'img' => $img,
                            );
                            $json_menu['left'][$post['_keyL']]['banner'][$count] = $data;
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Thêm danh mục thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    }
                    break;
                case 2:
                case 3:
                    if ($post['status'] == 'title') { // Sửa title
                        $template['title'] = $post['title'];
                        $template['sort'] = $post['sort'];
                        $template['status'] = $post['sta'];
                        $update = $serviceTemplate->edit($template, $post['tem_id']);
                        if ($update) {
                            $arrayReturn = array('error' => 0, 'message' => 'Cập nhật tiêu đề thành công');
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'image') { // them xoa sua banner
                        $json_image = json_decode($template['json_image'], TRUE);

                        if ($post['action'] == 'edit') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                                'img' => $post['img' . $post['key']],
                            );
                            if ($data['img'] == '' || !isset($data['img']) || !$data['img']) {
                                $data['img'] = $json_image[$post['key']]['img'];
                            }
                            $json_image[$post['key']] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            if (isset($post['key'])) {
                                unset($json_image[$post['key']]);
                                $json_image = array_values($json_image);
                                $template['json_image'] = json_encode($json_image);
                                $update = $serviceTemplate->edit($template, $post['tem_id']);
                                if ($update) {
                                    $arrayReturn = array('error' => 0, 'message' => 'Xóa banner thành công');
                                }
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'img' => $post['img'],
                                'sort' => $post['sort'],
                            );
                            $count = count($json_image);
                            $json_image[$count] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm banner thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } else { // them xoa sua menu
                        $json_menu = json_decode($template['json_menu'], TRUE);
                        if ($post['action'] == 'edit') {
                            $json_menu[$post['loca']][$post['key']]['name'] = $post['name'];
                            $json_menu[$post['loca']][$post['key']]['url'] = $post['url'];
                            $json_menu[$post['loca']][$post['key']]['sort'] = $post['sort'];
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            unset($json_menu[$post['loca']][$post['key']]);
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Xóa menu thành công');
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'name' => $post['name'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                            );
                            $count = count($json_menu[$post['loca']]);
                            $json_menu[$post['loca']][$count + 1000] = $data;
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm menu thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    }
                    break;

                case 4:
                    if ($post['status'] == 'product') {
                        $json_product = json_decode($template['json_product'], TRUE);
                        if ($post['action'] == 'delete') {
                            $k = array_search($post['_id'], $json_product[$post['key']]['item']);
                            if (false !== $k) {
                                unset($json_product[$post['key']]['item'][$k]);
                            }
                            $json_product[$post['key']]['item'] = array_values($json_product[$post['key']]['item']);
                            $template['json_product'] = json_encode($json_product);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                            return $this->getResponse()->setContent(json_encode($arrayReturn));
                        } elseif ($post['action'] == 'add') {
                            $json_product[$post['key']]['item'] = array_values($json_product[$post['key']]['item']);
                            $count = count($json_product[$post['key']]['item']);
                            $json_product[$post['key']]['item'][$count] = $post['_id'];
                            $template['json_product'] = json_encode($json_product);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                            return $this->getResponse()->setContent(json_encode($arrayReturn));
                        }
                    } elseif ($post['status'] == 'title') { // Sửa title
                        if (isset($post['category_id'])) {
                            $category['item'] = implode(',', $post['category_id']['item']);
                            $category['title'] = $post['category_id']['title'];
                            $template['category_id'] = json_encode($category);
                        }
                        $template['title'] = $post['title'];
                        $template['sort'] = $post['sort'];
                        $template['status'] = $post['sta'];
                        $template['html_form'] = $post['html_form'];
                        $update = $serviceTemplate->edit($template, $post['tem_id']);
                        if ($update) {
                            $arrayReturn = array('error' => 0, 'message' => 'Cập nhật tiêu đề thành công');
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'image') { // them xoa sua banner
                        $json_image = json_decode($template['json_image'], TRUE);

                        if ($post['action'] == 'edit') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                                'img' => $post['img' . $post['key']],
                            );
                            if ($data['img'] == '' || !isset($data['img']) || !$data['img']) {
                                $data['img'] = $json_image[$post['key']]['img'];
                            }
                            $json_image[$post['key']] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            if (isset($post['key'])) {
                                unset($json_image[$post['key']]);
                                $json_image = array_values($json_image);
                                $template['json_image'] = json_encode($json_image);
                                $update = $serviceTemplate->edit($template, $post['tem_id']);
                                if ($update) {
                                    $arrayReturn = array('error' => 0, 'message' => 'Xóa banner thành công');
                                }
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'img' => $post['img'],
                                'sort' => $post['sort'],
                            );
                            $count = count($json_image);
                            $json_image[$count] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm banner thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } else { // them xoa sua menu
                        $json_menu = json_decode($template['json_menu'], TRUE);
                        if ($post['action'] == 'edit') {
                            $json_menu[$post['loca']][$post['key']]['name'] = $post['name'];
                            $json_menu[$post['loca']][$post['key']]['url'] = $post['url'];
                            $json_menu[$post['loca']][$post['key']]['sort'] = $post['sort'];
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            unset($json_menu[$post['loca']][$post['key']]);
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Xóa menu thành công');
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'name' => $post['name'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                            );
                            $count = count($json_menu[$post['loca']]);
                            $json_menu[$post['loca']][$count + 1000] = $data;
                            $template['json_menu'] = json_encode($json_menu);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm menu thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    }
                    break;
                    
                    
                    case 5:    
                case 6:
                     if ($post['status'] == 'title') { // Sửa title
                        $template['title'] = $post['title'];
                        $template['sort'] = $post['sort'];
                        $template['status'] = $post['sta'];
                        $update = $serviceTemplate->edit($template, $post['tem_id']);
                        if ($update) {
                            $arrayReturn = array('error' => 0, 'message' => 'Cập nhật tiêu đề thành công');
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    } elseif ($post['status'] == 'image') { // them xoa sua banner
                        $json_image = json_decode($template['json_image'], TRUE);

                        if ($post['action'] == 'edit') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'sort' => $post['sort'],
                                'img' => $post['img' . $post['key']],
                            );
                            if ($data['img'] == '' || !isset($data['img']) || !$data['img']) {
                                $data['img'] = $json_image[$post['key']]['img'];
                            }
                            $json_image[$post['key']] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'message' => 'Cập nhật thành công');
                            }
                        } elseif ($post['action'] == 'delete') {
                            if (isset($post['key'])) {
                                unset($json_image[$post['key']]);
                                $json_image = array_values($json_image);
                                $template['json_image'] = json_encode($json_image);
                                $update = $serviceTemplate->edit($template, $post['tem_id']);
                                if ($update) {
                                    $arrayReturn = array('error' => 0, 'message' => 'Xóa banner thành công');
                                }
                            }
                        } elseif ($post['action'] == 'add') {
                            $data = array(
                                'title' => $post['title'],
                                'url' => $post['url'],
                                'img' => $post['img'],
                                'sort' => $post['sort'],
                            );
                            $count = count($json_image);
                            $json_image[$count] = $data;
                            $json_image = array_values($json_image);
                            $template['json_image'] = json_encode($json_image);
                            $update = $serviceTemplate->edit($template, $post['tem_id']);
                            if ($update) {
                                $arrayReturn = array('error' => 0, 'keys' => $count, 'message' => 'Thêm banner thành công');
                            }
                        }
                        return $this->getResponse()->setContent(json_encode($arrayReturn));
                    }
                    break;
                    
                    
                    
                    
                    
            }
        }
        $params = $this->params()->fromRoute();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCategoryList = $serviceCategory->getList(array('not_cate_status' => -1, 'or_cate_type' => array(0)));
        $template = $serviceTemplate->getDetail($params['id']);
        if (isset($template['json_product']) && $template['json_product'] != '') {
            $json_product = json_decode($template['json_product'], TRUE);
            $strIdProduct = array();
            $count = count($json_product);
            foreach ($json_product as $k => $json) {
                if (count($json['item']) > 0) {
                    $strIdProduct[] = implode(',', $json['item']);
                }
            }
            $strIdProduct = count($strIdProduct) > 0 ? implode(',', $strIdProduct) : '';
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $listProduct = $serviceProduct->getList(array('listProductID' => $strIdProduct, 'not_prod_status' => -1));
            $arrProduct = array();
            foreach ($listProduct as $product) {
                $arrProduct[$product['prod_id']] = $product;
            }
        }
        return array(
            'errors' => $errors,
            'template' => $template,
            'listCategory' => $arrCategoryList,
            'listProduct' => $arrProduct
        );
    }

    public function loadProductAction() {
        $this->layout('layout/empty');  //disable layout
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());
        $intPage = $this->params()->fromPost('page', 1);
        $intLimit = 15;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrConditions = array('not_id_product' => $params['not_id_product'], 'not_prod_status' => -1); // not find_in_set
        if (!empty($params['keyword']))
            $arrConditions['prod_name_like'] = $params['keyword'];
        $intTotal = $serviceProduct->getTotal($arrConditions);
        $arrProductList = $serviceProduct->getListLimit($arrConditions, $intPage, $intLimit, 'prod_updated DESC');
        $intTotal > 0 && $intLimit > 0 ? $intTotalPage = ceil($intTotal / $intLimit) : $intTotalPage = 0;
//        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
//        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'template', 'action' => 'load_product', 'page' => $intPage));

        return array(
            'params' => $params,
            'paging' => array('pageTotal' => $intTotalPage, 'curentP' => $intPage),
            'arrProductList' => $arrProductList,
        );
    }

    public function deleteAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if (empty($params['tem_id'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }
            $serviceTemplate = $this->serviceLocator->get('My\Models\Template');
            $arrData = array('status' => -1);
            if (is_array($params['tem_id'])) {
                if (count($params['tem_id']) > 0) {
                    foreach ($params['tem_id'] as $key => $value) {
                        $intResult = $serviceTemplate->edit($arrData, $value);
                    }
                }
            } else {
                $intResult = $serviceTemplate->edit($arrData, $params['tem_id']);
            }
            if ($intResult) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                            'user_id'=>UID,
                            'logs_controller'=>'Template',
                            'logs_action'=>'delete',
                            'logs_time'=>time(),
                            'logs_detail'=>'Xóa template có id = '.$params['tem_id'],
                        );
                $serviceLogs -> add($arrLogs);
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa template hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

}
