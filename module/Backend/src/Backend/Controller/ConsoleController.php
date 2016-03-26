<?php

namespace Backend\Controller;

use My\General,
    My\Controller\MyController;

class ConsoleController extends MyController {

    public function __construct() {
        if (PHP_SAPI !== 'cli') {
            die('Only use this controller from command line!');
        }
        ini_set('default_socket_timeout', -1);
        ini_set('max_execution_time', -1);
        ini_set('mysql.connect_timeout', -1);
        ini_set('memory_limit', -1);
        ini_set('output_buffering', 0);
        ini_set('zlib.output_compression', 0);
        ini_set('implicit_flush', 1);
    }

    public function indexAction() {
        die();
    }

    private function flush() {
        ob_end_flush();
        ob_flush();
        flush();
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

                $arrDocument[] = new \Elastica\Document($logsUserId, $arrLogs);
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
        
        //stop all job
        if ($params['stop'] === 'all') {
            if ($params['type'] || $params['background']) {
                return General::getColoredString("Invalid params \n", 'light_cyan', 'red');
            }
            exec("ps -ef | grep -v grep | grep 'type=proship-*' | awk '{ print $2 }'", $PID);

            if (empty($PID)) {
                return General::getColoredString("Cannot found PID \n", 'light_cyan', 'red');
            }

            foreach ($PID as $worker) {
                shell_exec("kill " . $worker);
                echo General::getColoredString("Kill worker with PID = {$worker} stopped running in backgound \n", 'green');
            }

            return true;
        }

        //stop job sendmail
        if ($params['stop'] === 'cstore-logs') {
            if ($params['type'] || $params['background']) {
                return General::getColoredString("Invalid params \n", 'light_cyan', 'red');
            }
            exec("ps -ef | grep -v grep | grep 'type=cstore-logs' | awk '{ print $2 }'", $PID);
            $PID = current($PID);
            if ($PID) {
                shell_exec("kill " . $PID);
                echo General::getColoredString("Job cstore-logs is stopped running in backgound \n", 'green');
                return;
            } else {
                echo General::getColoredString("Cannot found PID \n", 'light_cyan', 'red');
                return;
            }
        }

        $worker = General::getWorkerConfig();
        //  die($params['type']);
        switch ($params['type']) {
            case 'cstore-logs':
                //start job in background
                if ($params['background'] === 'true') {
                    $PID = shell_exec("nohup php " . PUBLIC_PATH . "/index.php worker --type=cstore-logs >/dev/null & echo 2>&1 & echo $!");
                    if (empty($PID)) {
                        echo General::getColoredString("Cannot deamon PHP process to run job cstore-logs in background. \n", 'light_cyan', 'red');
                        return;
                    }
                    echo General::getColoredString("Job cstore-logs is running in background ... \n", 'green');
                }
                
                $funcName1 = SEARCH_PREFIX . 'writeLog';
                $methodHandler1 = '\My\Job\JobLog::writeLog';
                $worker->addFunction($funcName1, $methodHandler1, $this->serviceLocator);

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
        exec("ps -ef | grep -v grep | grep 'type=cstore-logs' | awk '{ print $2 }'", $PID);
        $PID = current($PID);

        if (empty($PID)) {
            $PID = shell_exec("nohup php " . PUBLIC_PATH . "/index.php worker --type=cstore-logs >/dev/null & echo 2>&1 & echo $!");
            if (empty($PID)) {
                echo General::getColoredString("Cannot deamon PHP process to run job cstore-logs in background. \n", 'light_cyan', 'red');
                return;
            }
        }
    }
}
