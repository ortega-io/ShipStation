<?php

/**
 * ShipStation API Wrapper
 *
 * Provides an OOP interface to ShipStation API
 *
 * @package     Public
 * @subpackage  ShipStation
 * @author      Otoniel Ortega <ortega.x3@gmail.com>
 * @copyright   2014 Otoniel Ortega (c)
 * @version     1.0
 * @licence     The MIT License (MIT)
 *
 */

class ShipStation
{

	// ShipStation credentials //
	private $ssApiKey;
	private $ssApiSecret;
	private $authorization;
    private $defaultAuthorization;


	// Shipstation endpoint & methods //
	private $endpoint;
	private $methodsPaths;


    // Error Handling //
    private $lastError;


    // Requests cap handling //

    private $remainingRequests;
    private $resetTime;
    private $lastRequestTime;



    /**
    * ----------------------------------------------------
    *   _construct()
    * ----------------------------------------------------
    * 
    * Instantiate ShipStation Class.
    * 
    * @return  Object ShipStation   
    */

    public function __construct()
    {

    	// Define Endpoint //
    	
    	$this->endpoint 		= 'https://ssapi.shipstation.com/';


    	// Define Default Credentials //
    	// Authorization Token = The String: "{SS API Key}:{SS API Secret}" encoded with base64 //
    	// 
		$this->ssApiKey			= null;
		$this->ssApiSecret		= null;
		$this->authorization 	= 'Basic {Your Authorization Token Here}';


        // Requests cap handling //

        $this->remainingRequests    = 40; // Current SS per-minute limit //
        $this->resetTime            = 0;
        $this->lastRequestTime      = null;


		// Define Methods Paths //

		$this->methodsPaths 	= array
		(
            
            // Order related methods //

            'getOrders'         => 'Orders',
            'getOrder'          => 'Orders/{id}',
	    'addTagToOrder'	=> 'Orders/addtag',
            'addOrder'          => 'Orders/CreateOrder',
            'deleteOrder'       => 'Orders/{id}',


            // Shipment related methods //

            'getShipments'      => 'Shipments/List',
            'getRates'      	=> 'shipments/getrates',
            'createLabel'      	=> 'shipments/createLabel',


            // Warehouse related methods //

			'getWarehouses' 	=> 'warehouses',

            
			// Stores related methods //

            'getStores'         => 'Stores',
            
            
            // Carriers related methods //

            'getCarriers'         => 'carriers',
            'getCarrier'          => 'carriers/getcarrier',
            'getPackages'         => 'carriers/listpackages',
            'getServices'         => 'carriers/listservices'
		);


    }



    // Orders Related Methods [START] ============================ //
    // =========================================================== //


    /**
    * ----------------------------------------------------
    *  getOrders($filters)
    * ----------------------------------------------------
    * 
    * Get a list of all orders on ShipStation matching the filter.
    * 
    * @param    Array $filters
    *
    * @return   Array $orders
    */

    public function getOrders($filters)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // The API can't handle empty or null values on filters... (¬¬)
        // Validation of types would be useful.

        foreach($filters as $key=>$value)
        {
            if(empty($value))
            {
                unset($filters[$key]);
            }
        }


        // Build the Query String and get the orders.

        $filter     = http_build_query($filters);
        $response   = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getOrders'].'?'.$filter,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }

    /**
     * ----------------------------------------------------
     *  getAllOrders($filters)
     * ----------------------------------------------------
     *
     * Get a list of all orders on ShipStation matching the filter of all the pages.
     *
     * @param    Array $filters
     *
     * @return   Array $allOrders
     */
    public function getAllOrders($filters){

        $allOrders = array();
        $searchResult 	= $this->getOrders($filters);
        if(!empty($searchResult)){
            $orders = $searchResult->orders;
            foreach ($orders as $or) {
                array_push($allOrders,$or);
            }

            $currentPage = $searchResult->page;
            $totalPages = $searchResult->pages;

            if($currentPage < $totalPages){
                for($i=2;$i<=$totalPages;$i++){
                    $filters['page'] = $i;
                    $searchResult 	= $this->getOrders($filters);
                    $orders = $searchResult->orders;
                    foreach ($orders as $or) {
                        array_push($allOrders,$or);
                    }
                }


            }
        }
        return $allOrders;
    }


    /**
    * ----------------------------------------------------
    *  getOrder($orderId)
    * ----------------------------------------------------
    * 
    * Get an specific order on ShipStation by its ID.
    * 
    * @param    int         $orderId
    *
    * @return   stdClass    $order
    */

    public function getOrder($orderId)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        $methodPath = str_replace('{id}', $orderId, $this->methodsPaths['getOrder']);
        $response   = Unirest::get
        (
            $this->endpoint.$methodPath,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }

    /**
     * --------------------------------------------------
     * addTagToOrder($orderId, $tagId)
     * --------------------------------------------------
     * 
     * Add a Tag to a Shipstation Order.
     * 
     * @param    int         $orderId
     * @param    int         $tagId
     * 
     * @return   stdClass    $order
     */
     
     public function addTagToOrder($orderId, $tagId)
     {
     	
     	// Enforce API requests cap //

        $this->enforceApiRateLimit();
        
        $response = Unirest::post
        (
            $this->endpoint.$this->methodsPaths['addTagToOrder'],
            array
            (
                "Authorization" => $this->authorization,
                "Content-type" => "application/json"
            ),
            json_encode(array
            (
            	"orderId" => $orderId,
            	"tagId" => $tagId
            ) )
        );

        return $this->processReply($response);
     	
     }

    /**
    * ----------------------------------------------------
    *  addOrder($order)
    * ----------------------------------------------------
    * 
    * Add a new order to ShipStation.
    * 
    * @return stdClass $order
    */

    public function addOrder($order)
    {
        
        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // The API can't handle empty or null values on filters... (¬¬)
        // Validation of types would be useful.
        
        foreach($order as $property => $value)
        {
            if(empty($value))
            {
                unset($order->{"$property"});
            }
        }

        $response = Unirest::post
        (
            $this->endpoint.$this->methodsPaths['addOrder'],
            array
            (
                "Authorization" => $this->authorization,
                "content-type" => "application/json"
            ),
            json_encode($order)
        );

        return $this->processReply($response);

    }



    /**
    * ----------------------------------------------------
    *  deleteOrder($orderId)
    * ----------------------------------------------------
    * 
    * Delete an order on ShipStation by its ID.
    * 
    * @param    int         $orderId
    *
    * @return   void
    */

    public function deleteOrder($orderId)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        $methodPath = str_replace('{id}', $orderId, $this->methodsPaths['deleteOrder']);
        $response   = Unirest::delete
        (
            $this->endpoint.$methodPath,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }


    // Orders Related Methods [END] ============================== //
    // =========================================================== //

    

    // Shipment Related Methods [START] ========================== //
    // =========================================================== //


    /**
    * ----------------------------------------------------
    *  getShipments($filters)
    * ----------------------------------------------------
    * 
    * Get a list of all shipments on ShipStation matching the filter.
    * 
    * @param    Array $filters
    *
    * @return   Array $shipments
    */

    public function getShipments($filters)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // The API can't handle empty or null values on filters... (¬¬)
        // Validation of types would be useful.

        foreach($filters as $key=>$value)
        {
            if(empty($value))
            {
                unset($filters[$key]);
            }
        }


        // Build the Query String and get the shipments.

        $filter     = http_build_query($filters);
        $response   = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getShipments'].'?'.$filter,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }


    /**
     * ----------------------------------------------------
     *  getAllShipments($filters)
     * ----------------------------------------------------
     *
     * Get a list of all shipments on ShipStation matching the filter on all pages.
     *
     * @param    Array $filters
     *
     * @return   Array $allShipments
     */

    public function getAllShipments($filters)
    {
        $allShipments = array();

        $searchResult 	= $this->getShipments($filters);

        $shipments 		= $searchResult->shipments;
        if(!empty($shipments)){
            foreach($shipments as $sh){
                array_push($allShipments,$sh);
            }

            $currentPage 	= $searchResult->page;
            $totalPages 	= $searchResult->pages;

            if($currentPage < $totalPages){
                for($i=2;$i<=$totalPages;$i++){
                    $filters['page'] = $i;
                    $searchResult 	= $this->getShipments($filters);
                    $shipments 		= $searchResult->shipments;

                    foreach($shipments as $sh){
                        array_push($allShipments,$sh);
                    }
                }
            }

        }

        return $allShipments;


    }

	/**
    * ----------------------------------------------------
    *  getRates($filters)
    * ----------------------------------------------------
    * 
    * Retrieves shipping rates for the specified shipping details. 
    * 
    * @param    Array $filters
    *
    * @return   Array $rates
    */

    public function getRates($filters)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // The API can't handle empty or null values on filters... (¬¬)
        // Validation of types would be useful.

        foreach($filters as $key=>$value)
        {
            if(empty($value))
            {
                unset($filters[$key]);
            }
        }

        // Build the Query String and get the shipments.

        $filter     = http_build_query($filters);
        $response   = Unirest::post
        (
        	$this->endpoint.$this->methodsPaths['getRates'],
            array
            (
                "Authorization" => $this->authorization,
                "Content-type" => "application/json"
            ),
            json_encode( $filters )      
        );
        
        return $this->processReply($response);

    }
    
    /**
    * ----------------------------------------------------
    *  createLabel($filters)
    * ----------------------------------------------------
    * 
    * Creates a shipping label. The labelData field returned in the response is a base64 encoded PDF value.
    * Simply decode and save the output as a PDF file to retrieve a printable label.
    * 
    * @param    Array $filters
    *
    * @return   Array $label
    */

    public function createLabel($filters)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // The API can't handle empty or null values on filters... (¬¬)
        // Validation of types would be useful.

        foreach($filters as $key=>$value)
        {
            if(empty($value))
            {
                unset($filters[$key]);
            }
        }

        // Build the Query String and get the shipments.

        $filter     = http_build_query($filters);
        $response   = Unirest::post
        (
        	$this->endpoint.$this->methodsPaths['createLabel'],
            array
            (
                "Authorization" => $this->authorization,
                "Content-type" => "application/json"
            ),
            json_encode( $filters )      
        );
        return $this->processReply($response);

    }
    
    // Shipment Related Methods [END] ============================ //
    // =========================================================== //



    // Warehouses Related Methods [START] ======================== //
    // =========================================================== //

    /**
    * ----------------------------------------------------
    *  getWarehouses()
    * ----------------------------------------------------
    * 
    * Get list of warehouses availables.
    * 
    * @return Array $warehouses
    */

    public function getWarehouses()
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

		$response = Unirest::get
		(
			$this->endpoint.$this->methodsPaths['getWarehouses'],
			array
			(
				"Authorization" => $this->authorization
			)
		);

        return $this->processReply($response);

    }

    // Warehouses Related Methods [END] ========================== //
    // =========================================================== //



    // Stores Related Methods [START] ============================ //
    // =========================================================== //

    /**
    * ----------------------------------------------------
    *  getStores()
    * ----------------------------------------------------
    * 
    * Get list of stores available.
    * 
    * @return Array $stores
    */

    public function getStores()
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        $response = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getStores'],
            array
            (
                "Authorization" => $this->authorization
            )
        );

        return $this->processReply($response);

    }


    // Stores Related Methods [END] ============================== //
    // =========================================================== //

    
    // Carriers Related Methods [START] ============================ //
    // =========================================================== //

    /**
    * ----------------------------------------------------
    *  getCarriers()
    * ----------------------------------------------------
    * 
    * Get list of carriers available.
    * 
    * @return Array $carriers
    */

    public function getCarriers()
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        $response = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getCarriers'],
            array
            (
                "Authorization" => $this->authorization
            )
        );

        return $this->processReply($response);

    }
    
     /**
    * ----------------------------------------------------
    *  getCarrier($carrierCode)
    * ----------------------------------------------------
    * 
    * Get attributes of Carrier matching provided carrierCode
    * 
    * @param    String $carrierCode
    *
    * @return   Object $carrier
    */

    public function getCarrier($carrierCode)
    {
        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // Build the Query String and get the orders.

        $response   = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getCarrier'].'?carrierCode='.$carrierCode,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }
    
    /**
    * ----------------------------------------------------
    *  getPackages($carrierCode)
    * ----------------------------------------------------
    * 
    * Get a list of all Packages offered by the supplied carrierCode
    * 
    * @param    String $carrierCode
    *
    * @return   Array $packages
    */

    public function getPackages($carrierCode)
    {
        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // Build the Query String and get the orders.

        $response   = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getPackages'].'?carrierCode='.$carrierCode,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }
    
    /**
    * ----------------------------------------------------
    *  getServices($carrierCode)
    * ----------------------------------------------------
    * 
    * Get a list of all Services offered by the supplied carrierCode
    * 
    * @param    String $carrierCode
    *
    * @return   Array $services
    */

    public function getServices($carrierCode)
    {

        // Enforce API requests cap //

        $this->enforceApiRateLimit();

        // Build the Query String and get the orders.

        $response   = Unirest::get
        (
            $this->endpoint.$this->methodsPaths['getServices'].'?carrierCode='.$carrierCode,
            array
            (
                "Authorization" => $this->authorization
            )
        );
        
        return $this->processReply($response);

    }
    // Carriers Related Methods [END] ============================== //
    // =========================================================== //



    // Error Handling Methods [START] ============================ //
    // =========================================================== //

    /**
    * ----------------------------------------------------
    *  setLastError()
    * ----------------------------------------------------
    * 
    * Sets the error object for last failed request.
    * 
    * @return void
    */

    public function setLastError($response)
    {

        $error    = new stdClass();

        $error->code        = $response->code;
        $error->headers     = $response->headers;
        $error->message     = $response->raw_body;

        $this->lastError    = $error;

    }



    /**
    * ----------------------------------------------------
    *  getLastError()
    * ----------------------------------------------------
    * 
    * Returns the last response from server, reported as error.
    * 
    * @return stdClass $error
    */

    public function getLastError()
    {
        
        return $this->lastError;

    }


    // Error Handling Methods [END] ============================== //
    // =========================================================== //



    // Internal Methods [START] ================================== //
    // =========================================================== //

    /**
    * ----------------------------------------------------
    *  processReply($response)
    * ----------------------------------------------------
    * 
    * Process reply from server, intended to add further validation/handling.
    * 
    * @return stdClass $object
    */

    private function processReply($response)
    {
     

        // API cap handling + error handling //
        if(is_object($response))
        {

            $this->remainingRequests    = $response->headers['X-Rate-Limit-Remaining'];
            $this->resetTime            = $response->headers['X-Rate-Limit-Reset'];
            $this->lastRequestTime      = time();

        }
        else
        {
            // Something went really wrong...
            return false;
        }


        if($response->code == 200)
        {
            return $response->body;
        }
        else
        {
            $this->setLastError($response);

            return false;
        }

    }

    // Internal Methods [END] ==================================== //
    // =========================================================== //



    // Authorization and Request Cap Methods [START] ============= //
    // =========================================================== //



    /**
    * ----------------------------------------------------
    *  setSsApiKey($ssApiKey)
    * ----------------------------------------------------
    * 
    * Sets ShipStation Api Key.
    * 
    * @return void
    */

    public function setSsApiKey($ssApiKey)
    {
        
        $this->ssApiKey = $ssApiKey;

        if(!empty($this->ssApiSecret))
        {
        	$this->authorization = 'Basic '.base64_encode($this->ssApiKey.':'.$this->ssApiSecret);
        }

    }



    /**
    * ----------------------------------------------------
    *  setSsApiSecret($ssApiSecret)
    * ----------------------------------------------------
    * 
    * Sets ShipStation Api Secret.
    * 
    * @return void
    */

    public function setSsApiSecret($ssApiSecret)
    {
        
        $this->ssApiSecret = $ssApiSecret;

        if(!empty($this->ssApiKey))
        {
        	$this->authorization = 'Basic '.base64_encode($this->ssApiKey.':'.$this->ssApiSecret);
        }

    }



    /**
    * ----------------------------------------------------
    *  setAuthorization($authorization)
    * ----------------------------------------------------
    * 
    * Sets the authorization token to use directly
    * allowing to switch between multiple ShipStation Accounts faster.
    * 
    * @return void
    */

    public function setAuthorization($authorization)
    {
        
        $this->authorization    = $authorization;

    }


    /**
    * ----------------------------------------------------
    *  resetAuthorization()
    * ----------------------------------------------------
    * 
    * Resets the authorization token to default.
    * 
    * @return void
    */

    public function resetAuthorization()
    {
        
        $this->authorization    =  $this->defaultAuthorization;

    }



    /**
    * ----------------------------------------------------
    *  enforceApiRateLimit()
    * ----------------------------------------------------
    * 
    * Enforces ShipStation API
    * 
    * @return stdClass $object
    */

    /* WARNING:
    /* Currently the request cap as defined by ShipStation is not being honored
    /* so the request throughput never reaches 40 requests per minute
    /* but this code should handle it if the cap is ever to be honored...
	*/

    private function enforceApiRateLimit()
    {
        
        if($this->remainingRequests>0)
        {
            return;
        }
        else
        {
            
            if( !empty($this->lastRequestTime) )
            {
                
                $elapsedTime        = ( time() - $this->lastRequestTime );

                if( $elapsedTime > $this->resetTime )
                {
                    return;
                }
                else
                {
                    $waitingTime    = ( $this->resetTime - $elapsedTime );
                    
                    sleep($waitingTime);
                }

            }
            else
            {
                return; // We never should get here...
            }

            
        }

    }

    // Authorization and Request Cap Methods [END] =============== //
    // =========================================================== //

}


?>
