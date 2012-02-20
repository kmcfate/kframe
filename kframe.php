<?php

class KFrame {

    protected $action_prefix = 'action_';
    protected $debug = false;
    protected $secure = false;
    protected $auth = false;
    protected $default_action = 'main';
    protected $auth_action = 'auth';
    protected $login_action = 'login';

    function __construct() {
        if (isset($_SERVER['SHELL'])) {
            echo "KFrame does not run from command line\n";
            die();
        }
        if ($this->debug) {
            ini_set('display_errors', E_ALL);
        }
        if ($this->auth) {
            $this->secure=true;
        }
        if ($this->secure) {
            if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") {
                $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                header("Location: $url");
                exit;
            }
        }
        if (!method_exists($this, $this->action_prefix . $this->default_action)) {
            die("Default action $this->default_action does not exist");
        }
        if (!method_exists($this, $this->action_prefix . $this->auth_action)) {
            die("Default action $this->auth_action does not exist");
        }
        if (!method_exists($this, $this->action_prefix . $this->login_action)) {
            die("Default action $this->login_action does not exist");
        }
    }

    protected function reload($params="") {
        header('Location: ' . $_SERVER['PHP_SELF'] . $params);
    }

    protected function action_main() {
        echo "Welcome to KFrame!";
    }

    protected function init() {

    }

    public function run() {
        $this->init();
        if (($this->auth) && (!call_user_func(array($this, $this->action_prefix . $this->auth_action)))) {
            call_user_func(array($this, $this->action_prefix . $this->login_action));
        } else {
            if (isset($_REQUEST['action']) && method_exists($this, $this->action_prefix . $_REQUEST['action'])) {
                call_user_func(array($this, $this->action_prefix . $_REQUEST['action']));
            } else {
                call_user_func(array($this, $this->action_prefix . $this->default_action));
            }
        }
    }

    protected function action_auth() {
        die("Please define your auth mechanism or disable auth!");
    }

    protected function action_login() {
        die("Please define your login mechanism or disable auth!");
    }

}
?>
