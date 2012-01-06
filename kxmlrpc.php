<?
class KXmlRpc {

    private $host = '';
    private $uri = '';
    private $port = 80;
    private $debug = false;
    private $user = '';
    private $pass = '';
    private $secure = false;
    private $output = array('version' => 'xmlrpc');
    private $timeout = 10;

    public function __construct($params) {
        extract($params, EXTR_PREFIX_ALL, 'param');
        $this->uri = isset($param_uri) ? $param_uri : $this->uri;
        $this->host = isset($param_host) ? $param_host : $this->host;
        $this->port = isset($param_port) ? $param_port : $this->port;
        $this->user = isset($param_user) ? $param_user : $this->user;
        $this->pass = isset($param_pass) ? $param_pass : $this->pass;
        $this->debug = isset($param_debug) ? $param_debug : $this->debug;
        $this->secure = isset($param_secure) ? $param_secure : $this->secure;
        $this->output = isset($param_output) ? $param_output : $this->output;
        $this->timeout = isset($param_timeout) ? $param_timeout : $this->timeout;
    }

    public function request($method, $args=array()) {
        if ($this->host && $this->uri && $this->port && $method && $args) {
            $request = xmlrpc_encode_request($method, $args, $this->output);
            $content_len = strlen($request);
            $ssl = $this->secure ? "ssl://" : "";
            $query_fd = fsockopen($ssl.$this->host, $this->port, $errno, $errstr, $this->timeout);
            if ($query_fd) {
                $auth = "";
                if ($this->user) {
                    $auth = "Authorization: Basic " .
                        base64_encode($this->user . ":" . $this->pass) . "\r\n";
                }
                $http_request =
                    "POST $this->uri HTTP/1.0\r\n" .
                    "User-Agent: xmlrpc-php/0.2 (PHP)\r\n" .
                    "Host: $this->host:$this->port\r\n" .
                    $auth .
                    "Content-Type: text/xml\r\n" .
                    "Content-Length: $content_len\r\n" .
                    "\r\n" .
                    $request;
                if ($this->debug) {
                    error_log("Sending query: " . $http_request);
                }
                fputs($query_fd, $http_request, strlen($http_request));
                $header_parsed = false;
                $line = fgets($query_fd, 4096);
                $response_buf = "";
                while ($line) {
                    if (!$header_parsed) {
                        if ($line === "\r\n" || $line === "\n") {
                            $header_parsed = 1;
                        }
                    } else {
                        $response_buf .= $line;
                    }
                    $line = fgets($query_fd, 4096);
                }
                fclose($query_fd);
            } else {
                error_log("socket open failed");
            }
        } else {
            error_log("missing param(s)");
        }
        if ($this->debug) {
            error_log("got response:</h3>. <xmp>\n$response_buf\n</xmp>\n");
        }
        if (strlen($response_buf)) {
            $xml_begin = substr($response_buf, strpos($response_buf, "<?xml"));
            if (strlen($xml_begin)) {
                $retval = xmlrpc_decode($xml_begin);
            } else {
                error_log("xml start token not found");
            }
        } else {
            error_log("no data");
        }
        if (is_array($retval) && xmlrpc_is_fault($retval)) {
            return false;
        } else {
            return $retval;
        }
    }
}
?>
