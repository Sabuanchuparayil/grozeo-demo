<?php


namespace Controllers {

    use Models\Auth;
    use Models\User;
    use Models\Ledger;
    use Models\ThirdPartyProducts;
    use Models\Utils;

    class RequestHandler extends RequestAbstract {

        private $sapi_type;
        protected $User;
        protected $exceptionstring;
        public $authstatus;

        public function __construct($request, $origin) {
            $this->sapi_type = php_sapi_name();

            if ($this->sapi_type == 'cli')
            {
                return;
            }
            parent::__construct($request);
            if ($this->endpoint == 'thirdpartyproducts')
            {
                
            }
            elseif ($this->endpoint != 'auth' || $this->verb != 'auth' || $this->method != 'GET')
            {
                $auth = new Auth('GET_Auth');
                $User = new User();
                if (!array_key_exists('apikey', $this->request))
                {
                    //print_r($_REQUEST);
                    throw new \Exception('No API Key provided ' . json_encode($this->request) . $this->endpoint . ' ' . $this->verb . ' ' . $this->method);
                }
                elseif (!$auth->verifyKey($this->request['apikey'], $this->request['branchkey'], $origin, $response))
                {
                    //echo "API - " .$this->request['apikey'];
                    $this->currentresponse = $response;
                    $this->currentstatus = 401;
                    //throw new \Exception('Invalid API Key');
                    return;
                }
                elseif (array_key_exists('token', $this->request) && !$User->get('token', $this->request['token']))
                {
                    throw new \Exception('Invalid User Token');
                }
                //$this->User = $User;
            }

            if ($this->endpoint == 'collectconsignment' && $this->verb == 'createbookingsession' && $this->method == 'GET')
            {
                $utils = new Utils();
                if ($utils->IsCurrentVersion($this->request, $vertext) == false)
                {
                    $this->versionmismatchtext = $vertext;
                    $this->currentstatus = 426;
                    return;
                }
            }
        }

        //Authentication
        protected function auth() {
            $auth = new Auth($this->method . '_' . $this->verb);
            return $auth->{$this->method . '_' . $this->verb}($this->args, $this->request);
        }

        //Ledger details
        protected function ledger() {
            $ledger = new Ledger($this->method . '_' . $this->verb);
            return $ledger->{$this->method . '_' . $this->verb}($this->args, $this->request);
        }

        //ThirdPartyProducts details
        protected function thirdpartyproducts() {
            $thirdpartyproducts = new ThirdPartyProducts($this->method . '_' . $this->verb);
            return $thirdpartyproducts->{$this->method . '_' . $this->verb}($this->args, $this->request);
        }

    }

}		






