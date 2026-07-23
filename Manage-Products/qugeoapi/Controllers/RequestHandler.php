<?php
//require(API_ROOT . '/caregoapi-autoloader.php');
namespace Controllers{
	use Models\Auth;
	use Models\User;
	use Models\Customer;
	use Models\Pincode;
	use Models\Consignment;
	use Models\ContentType;
	use Models\PackingType;
	use Models\Charges;
	use Models\QugeoRegistration;
	use Models\Carting;
	use Models\Dashboard;
	use Models\QugeoScheduler;
	use Models\QugeoOrderPoller;
	use Models\QugeoOrderHandler;
	use Models\CrossBooking;

	class RequestHandler extends RequestAbstract{
		private $sapi_type;
		protected $User;
		protected $exceptionstring;		
		public $authstatus;
		public function __construct($request, $origin) {
			$this->sapi_type = php_sapi_name();
			if ( $this->sapi_type == 'cli' ){
				return;
			}
			parent::__construct($request);			
			if($this->endpoint=='qugeoregistration'){

			}elseif ($this->endpoint!='auth' || $this->verb!='auth' || $this->method!='GET'){ 
				$auth = new Auth('GET_Auth');
				$User = new User();
				if (!array_key_exists('apikey', $this->request)) {
                                        //print_r($this->request);
					throw new \Exception('No API Key provided ' . json_encode($this->request) . $this->endpoint . ' ' . $this->verb . ' ' .  $this->method );
				} elseif (!$auth->verifyKey($this->request['apikey'], $origin)) {
					//echo "API - " .$this->request['apikey'];
					$this->currentstatus = 401;
					//throw new \Exception('Invalid API Key');
					return;
				} elseif (array_key_exists('token', $this->request) &&
					 !$User->get('token', $this->request['token'])) {
					throw new \Exception('Invalid User Token');
				}
				//$this->User = $User;
			}
		}

		 //Authentication
		 protected function auth() {
			$auth = new Auth( $this->method . '_' . $this->verb);
			return $auth->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //Customer details
		 protected function customer() {
			$customer = new Customer( $this->method . '_' . $this->verb);
			return $customer->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //Pincode master
		 protected function pincode() {
			$pincode = new Pincode( $this->method . '_' . $this->verb);
			return $pincode->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //PackingTypeMaster
		 protected function packingtype() {
			$packingtype = new PackingType( $this->method . '_' . $this->verb);
			return $packingtype->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //ContentTypeMaster
		 protected function contenttype() {
			$contenttype = new ContentType( $this->method . '_' . $this->verb);
			return $contenttype->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //Add or update a booking
		  protected function consignment() {
			$consignment = new Consignment( $this->method . '_' . $this->verb);
			return $consignment->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //Get charges for consignment
		  protected function charges() {
			$charges = new Charges( $this->method . '_' . $this->verb);
			return $charges->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //Qugeo - order handling.
		  protected function carting() {
			$carting = new Carting( $this->method . '_' . $this->verb);
			return $carting->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }

		 //Qugeo Registration .
		  protected function qugeoregistration() {
			$qugeoregistration = new QugeoRegistration( $this->method . '_' . $this->verb);
			return $qugeoregistration->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }
		 
		 //Schedluer
		 public function qugeoscheduler($method) {
			if($this->sapi_type != 'cli'){
				throw new \Exception('Endpoint not available for external access');
			}				
			$qugeoscheduler = new QugeoScheduler();
			return $qugeoscheduler->{ $method}();
		 }
		
		//Dashboard
		 public function dashboard() {
			
			$dashboard = new Dashboard($this->method . '_' . $this->verb);
			return $dashboard->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }
		 
		//CrossBooking
		 public function crossbooking() {
			
			$crossbooking = new CrossBooking($this->method . '_' . $this->verb);
			return $crossbooking->{$this->method . '_' . $this->verb}($this->args,$this->request);
		 }		 
	 }
 }