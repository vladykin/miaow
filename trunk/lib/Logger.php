<?php

// $Id$

class Logger {

    private static $instance;

    private $log;

    /**
     * Singleton pattern.
     */
    private function __construct() {
        $this->log = array();
    }

    /**
     * Singleton pattern.
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    /**
     * Appends a message to the log. Like in syslog(),
     * the following message priorities can be are used:
     * LOG_EMERG
     * LOG_ALERT
     * LOG_CRIT
     * LOG_ERR
     * LOG_WARNING
     * LOG_NOTICE
     * LOG_INFO
     * LOG_DEBUG
     */
    protected function log($priority, $message) {
        array_push($this->log, array(date('r'), $priority, $message));
    }

    public function debug($message) {
        $this->log(LOG_DEBUG, $message);
    }

    public function info($message) {
        $this->log(LOG_INFO, $message);
    }

    public function warning($message) {
        $this->log(LOG_WARNING, $message);
    }

    public function error($message) {
        $this->log(LOG_ERR, $message);
    }

    public function critical($message) {
        $this->log(LOG_CRIT, $message);
    }

    public function getLog() {
        $dump = '';
        foreach ($this->log as $row) {
            list($timestamp, $priority, $message) = $row;
            $dump .= "[$timestamp] $priority $message\n";
        }
        return $dump;
    }

    public function dumpLog() {
        echo($this->getLog());
    }

    public function sendLog($email) {
        mail($email, $this->getLog());
    }

}

/*
$logger = Logger::getInstance();
$logger->info('Starting');
$logger->info('Testing');
$logger->info('Finishing');
$logger->dumpLog();
*/

?>
