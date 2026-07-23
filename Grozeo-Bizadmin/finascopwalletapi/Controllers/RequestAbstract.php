<?php

namespace Controllers {

    use Views\Show;

    abstract class RequestAbstract {

        /**
         * Property: method
         * The HTTP method this request was made in, either GET, POST, PUT or DELETE
         */
        protected $method = '';

        /**
         * Property: endpoint
         * The Model requested in the URI. eg: /files
         */
        protected $endpoint = '';

        /**
         * Property: verb
         * An optional additional descriptor about the endpoint, used for things that can
         * not be handled by the basic methods. eg: /files/process
         */
        protected $verb = '';

        /**
         * Property: request	
         * Holds the header and request paramaters
         */
        protected $request = array();

        /**
         * Property: args
         * Any additional URI components after the endpoint and verb have been removed, in our
         * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
         * or /<endpoint>/<arg0>
         */
        protected $args = Array();

        /**
         * Property: file
         * Stores the input of the PUT request
         */
        protected $file = Null;

        /**
         * Property: appversion
         * Current status
         */
        protected $appversion = '1.0';

        /**
         * Property: versionmismatchtext
         * Version Mismatch text
         */
        protected $versionmismatchtext = '';

        /**
         * Property: currentstatus
         * Current status
         */
        protected $currentstatus = 200;

        /**
         * Property: currentresponse
         * Current response
         */
        protected $currentresponse = '';

        /**
         * Constructor: __construct
         * Allow for CORS, assemble and pre-process the data
         */
        public function __construct($request) {
            /* header("Access-Control-Allow-Orgin: *");
              //header("Access-Control-Allow-Methods: *");
              header("Access-Control-Allow-Headers: origin, x-requested-with, content-type, apikey");
              header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
              //header("Content-Type: application/json"); */
            // Allow from any origin
            if (isset($_SERVER['HTTP_ORIGIN']))
            {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: origin, x-requested-with, content-type, apikey");
                header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");

                //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
                //header('Access-Control-Allow-Credentials: true');
                //header('Access-Control-Max-Age: 86400');    // cache for 1 day
            }

            // Access-Control headers are received during OPTIONS requests
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
            {

                /* if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                  header("Access-Control-Allow-Methods: PUT, DELETE, GET, POST, OPTIONS");
                 */
                /* if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                  header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"); */
                header("No Response", false, 204);
                exit(0);
            }



            $this->args = explode('/', rtrim($request, '/'));
            $this->endpoint = strtolower(array_shift($this->args));

            if (array_key_exists(0, $this->args))
            {
                if (substr($this->args[0], 0, 1) !== ':')
                {
                    $this->verb = strtolower(array_shift($this->args));
                }
            }
            else
            {
                $this->verb = $this->endpoint;
            }

            $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
            if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
            {
                if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
                {
                    $this->method = 'DELETE';
                }
                else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
                {
                    $this->method = 'PUT';
                }
                else
                {
                    throw new \Exception("Unexpected Header");
                }
            }
            
            $this->header = apache_request_headers();

            switch ($this->method)
            {
                case 'DELETE':
                case 'POST':
                    header("Content-Type: application/json");
                    $this->request = $this->_cleanInputs($_POST);					
                    break;
                case 'GET':
                    header("Content-Type: application/json");
                    $this->request = $this->_cleanInputs($_GET);
                    break;
                case 'PUT':
                    header("Content-Type: application/json");                   
                    $this->request = $this->_cleanInputs($_GET);
                  	parse_str(file_get_contents('php://input'), $put);                
                    $this->request = array_merge($this->request,$put);
                    break;
                case 'OPTIONS':
                    $this->_response('Invalid Method', 204);
                    break;
                default:
                    $this->_response('Invalid Method', 405);
                    break;
            }
            $this->request = array_merge($this->request, $this->header);
        }

        public function processAPI() {

            if ($this->currentstatus == 401)
                return $this->_response($this->currentresponse, $this->currentstatus);
            if ($this->currentstatus == 426)
                return $this->_response($this->versionmismatchtext, $this->currentstatus);
            if ($this->verb == '')
                return $this->_response("No Verb given", 404);
            if (method_exists($this, $this->endpoint))
            {
                return $this->_response($this->{$this->endpoint}($this->args));
            }
            else
                return $this->_response("No Endpoint: $this->endpoint", 404);
        }

        private function _response($data, $status = 200) {
            $ViewShow = new Show();
            header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
            header("Pragma: no-cache"); // HTTP 1.0.
            header("Expires: 0"); // Proxies.
            $resp = $ViewShow->getJson($data, $status);
            $this->logrequest($resp);
            return $resp;
        }

        private function logrequest($resp) {
            try
            {
                $nodb = new \cgoDynamiteDB();
                $arrSession = array();
                $arrSession['Data'] = array();
                $valdate = date("Ymd");
                $valdatetime = date("YmdHis");
                $valdateminute = date("YmdHi");
                $valdatehour = date("YmdH");
                array_push($arrSession['Data'], array('col' => 'request_source', 'val' => 'WALLET'));
                array_push($arrSession['Data'], array('col' => 'ipadd', 'val' => (string) $_SERVER['REMOTE_ADDR']));
                array_push($arrSession['Data'], array('col' => 'mt', 'val' => (string) round(microtime(true) * 1000)));
                array_push($arrSession['Data'], array('col' => 'atdate', 'val' => (int) $valdate));
                array_push($arrSession['Data'], array('col' => 'athour', 'val' => (int) $valdatehour));
                array_push($arrSession['Data'], array('col' => 'atminute', 'val' => (int) $valdateminute));
                array_push($arrSession['Data'], array('col' => 'atsecond', 'val' => (int) $valdatetime));
                array_push($arrSession['Data'], array('col' => 'request', 'val' => $_REQUEST));
                array_push($arrSession['Data'], array('col' => 'request_headers', 'val' => getallheaders()));
                array_push($arrSession['Data'], array('col' => 'response', 'val' => (string) $resp));
                $LiveVehicles = $nodb->perform('HttpLogs', 'insert', $arrSession, $response);
            }
            catch (Exception $e)
            {
                file_put_contents('php://stderr', print_r($e, TRUE));
            }
        }

        private function _cleanInputs($data) {
            $clean_input = Array();
            if (is_array($data))
            {
                foreach ($data as $k => $v)
                {
                    $clean_input[$k] = $this->_cleanInputs($v);
                }
            }
            else
            {
                $clean_input = trim(strip_tags($data));
            }
            return $clean_input;
        }

        private function _requestStatus($code) {
            $status = array(
                200 => 'OK',
                204 => 'No Content',
                401 => 'Invalid API Key',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                426 => 'Upgrade Required',
                500 => 'Internal Server Error',
            );
            return ($status[$code]) ? $status[$code] : $status['500'];
        }

    }

}			








