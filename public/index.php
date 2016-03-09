<?php

chdir(dirname(__DIR__));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

switch (APPLICATION_ENV) {
    case 'production':
        $baseURL = 'http://cstore.com';
        $staticURL = 'http://static.cstore.com';
        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', '10.5.75.12:11211');
        ini_set('session.gc_maxlifetime', 604800);
        $isMobile = false;
        break;
    case 'staging':
        $baseURL = 'http://staging.cstore.com';
        $staticURL = 'http://staging.st.cstore.com';
        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', '10.5.75.12:11211');
        ini_set('session.gc_maxlifetime', 604800);
        break;
    case 'dev':
        $baseURL = 'http://' . APPLICATION_ENV . '.cstore.com';
        $staticURL = 'http://' . APPLICATION_ENV . '.st.cstore.com';
        ini_set('session.save_handler', 'redis');
        ini_set('session.gc_maxlifetime', 604800);
        ini_set('session.save_path', 'tcp://127.0.0.1:6379?weight=1&timeout=1&database=0&prefix=megavita&timeout=604800&persistent=1');
        break;
    default:
        $baseURL = 'http://cstore.com';
        $staticURL = 'http://static.cstore.com';
        break;
}
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR);

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

//define variable
defined('WEB_ROOT') || define('WEB_ROOT', realpath(dirname(dirname(__FILE__))));

define('FRONTEND_TEMPLATE', 'v1');
define('STATIC_URL', $staticURL);
define('BASE_URL', $baseURL);
define('PUBLIC_PATH', WEB_ROOT . '/public');
define('STATIC_PATH', WEB_ROOT . '/static');
define('UPLOAD_PATH', STATIC_PATH . '/uploads/');
define('UPLOAD_URL', STATIC_URL . '/uploads/');
define('FRONTEND_FONT_PATH', STATIC_PATH . '/f/fonts/');
define('VENDOR_DIR', WEB_ROOT . '/vendor/');
define('CONFIG_CACHE_DIR', WEB_ROOT . '/config/config-cache');
define('SES_EXPIRED', 7776000);
define('CLI_DEBUG', 0);
define('SEARCH_PREFIX', 'cstore_');

define('CAPTCHA_PATH', STATIC_PATH . '/f/captcha/');
define('CAPTCHA_URL', STATIC_URL . '/f/captcha/');

// Setup autoloading
require 'init_autoloader.php';
//require_once 'vendor/zendframework/library/Zend/Loader/StandardAutoloader.php';
// Run the application!
try {
    \Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch (\Exception $exc) {
    die($exc->getMessage());
}

function p($arr) {
    echo'<pre>';
    print_r($arr);
    echo'</pre>';
}

function detectMobile($useragent) {
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
        return true;
    } else {
        return false;
    }
}

function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
