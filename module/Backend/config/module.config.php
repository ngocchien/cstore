<?php

return array(
    'router' => array(
        'routes' => array(
            'backend' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend[/:controller][/:action][/id/:id][/code/:code][/from/:from][/ship/:ship][/to/:to][/page/:page][/gid/:gid][/pid/:pid][/type/:type][/tab/:tab][/limit/:limit][/][?s=:s][&ban_cate_id=:ban_cate_id][&ban_location=:ban_location]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'code' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'pid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'limit' => '[0-9]*',
                        'ship' => '[0-9]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'index',
                        'action' => 'index',
                        'id' => 0,
                        'pid' => 0,
                        'page' => 1,
                        'limit' => 15
                    ),
                ),
            ),
            'backend-user-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?fullname=:fullname][&email=:email][&phoneNumber=:phoneNumber][&page=:page]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'user',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-bought-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?fullname=:fullname][&email=:email][&phoneNumber=:phoneNumber][&page=:page][&limit=:limit]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'user',
                        'action' => 'bought',
                        'page' => 1,
                        'limit' => 15
                    ),
                ),
            ),
            'backend-quotation-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?quotationStatus=:quotationStatus][&email=:email][&page=:page]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'order',
                        'action' => 'quotation',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-product-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&cate_id=:cate_id][&brand_id=:brand_id][&order_sort=:order_sort]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'product?',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-productorder-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&status=:status][&date_from=:date_from][&date_to=:date_to]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'order?',
                        'action' => 'product',
                        'page' => 1,
                    ),
                ),
            ),
      
            'backend-advisory-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&type=:type]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'advisory?',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-user-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&group=:group]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'user?',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-order-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&user_fullname=:user_fullname][&user_email=:user_email][&user_phone=:user_phone][&date_from=:date_from][&date_to=:date_to][&Sales=:Sales][&Status=:Status][&Type=:Type]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'order',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-note-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&date_from=:date_from][&date_to=:date_to][&note_type=:note_type]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'order',
                        'action' => 'noteorder',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-meeting-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'meeting?',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-message-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[id=:id]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'message?',
                        'action' => 'readMessage',
                    ),
                ),
            ),
            'backend-messageall-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'message?',
                        'action' => 'messAll',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-meta-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[page=:page][&limit=:limit][&s=:s][&cate_id=:cate_id][&brand_id=:brand_id]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'linkCategoryBrand?',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-content-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?[&page=:page][&limit=:limit][&s=:s][&cate_id=:cate_id]]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'content?',
                        'action' => 'index',
                        'page' => 1,
                    ),
                ),
            ),
            'backend-comment-search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/backend/:controller/:action[?isFilter=:isFilter][&status=:status][&catalogID=:catalogID][&page=:page]',
                    'constraints' => array(
                        'module' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'status' => '[0-9]+',
                        'catalogID' => '[0-9]+',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Backend\Controller',
                        'module' => 'backend',
                        'controller' => 'order',
                        'action' => 'quotation',
                        'status' => -1,
                        'catalogID' => 0,
                        'page' => 1,
                    ),
                ),
            ),
        ),
    ), 'console' => array(
        'router' => array(
            'routes' => array(
                'migrate' => array(
                    'options' => array(
                        'route' => 'migrate [--type=] [--createindex=] [--page=] [--limit=] [isdev]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'migrate'
                        ),
                    ),
                ), 'crawler-keyword' => array(
                    'options' => array(
                        'route' => 'crawler-keyword  [isdev]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'crawlerKeywordMulti'
                        ),
                    ),
                ), 'generate-sitemap' => array(
                    'options' => array(
                        'route' => 'generate-sitemap  [isdev]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'sitemap'
                        ),
                    ),
                ), 'format-images' => array(
                    'options' => array(
                        'route' => 'format-images [--dataimages=] ',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'formatImages'
                        ),
                    ),
                ), 'sitemap' => array(
                    'options' => array(
                        'route' => 'keyword-sitemap  [isdev]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'keyword'
                        ),
                    ),
                ), 'sitemap' => array(
                    'options' => array(
                        'route' => 'update-is-crawler  [isdev]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'updateIsCrawler'
                        ),
                    ),
                ), 'check-worker-running' => array(
                    'options' => array(
                        'route' => 'check-worker-running',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'checkWorkerRunning'
                        ),
                    ),
                ), 'worker' => array(
                    'options' => array(
                        'route' => 'worker [--type=] [--stop=]',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Backend\Controller',
                            'controller' => 'console',
                            'action' => 'worker'
                        ),
                    ),
                )
            )
        )
    ),
    'module_layouts' => array(
        'Backend' => 'backend/layout',
    ),
    'view_helpers' => array(
        'invokables' => array(
            'paging' => 'My\View\Helper\Paging',
            'pagingajax' => 'My\View\Helper\Pagingajax'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'json_exceptions' => array(
            'display' => true,
            'ajax_only' => true,
            'show_trace' => true
        ),
        'doctype' => 'HTML5',
        'not_found_template' => 'backend/error/404',
        'exception_template' => 'backend/error/index',
        'template_map' => array(
            'backend/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'backend/header' => __DIR__ . '/../view/layout/header.phtml',
            'backend/sidebar' => __DIR__ . '/../view/layout/sidebar.phtml',
            'backend/auth' => __DIR__ . '/../view/layout/auth.phtml',
            'backend/error/404' => __DIR__ . '/../view/error/404.phtml',
            'backend/error/index' => __DIR__ . '/../view/error/index.phtml',
            'backend/error/accessDeny' => __DIR__ . '/../view/error/access-deny.phtml',
            'backend/template_1' => __DIR__ . '/../view/backend/template/template_1.phtml',
            'backend/template_2' => __DIR__ . '/../view/backend/template/template_2.phtml',
            'backend/template_3' => __DIR__ . '/../view/backend/template/template_3.phtml',
            'backend/template_4' => __DIR__ . '/../view/backend/template/template_4.phtml',
            'backend/template_5' => __DIR__ . '/../view/backend/template/template_5.phtml',
            'backend/template_6' => __DIR__ . '/../view/backend/template/template_6.phtml',
            'backend/edit/template' => __DIR__ . '/../view/backend/template/edit_template1.phtml',
            'sitemap/html' => __DIR__ . '/../view/backend/console/sitemap-html.phtml',
            'mail_cencal/html' => __DIR__ . '/../view/backend/order/mail_cencal.phtml',
            'mail_success/html' => __DIR__ . '/../view/backend/order/mail_success.phtml',
            'payment_chart/html' => __DIR__ . '/../view/backend/statistic/payment_chart.phtml',
        ),
        'template_path_stack' => array(
            'backend' => __DIR__ . '/../view',
            'email' => __DIR__ . '/../view/email',
            'modal' => __DIR__ . '/../view/modal',
        ),
    ),
);
