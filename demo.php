<?php

/**
 * ShipStation API Wrapper Demo Script
 *
 * Provides a demo of the use of the ShipStation API Wrapper.
 *
 * @package     Public
 * @subpackage  ShipStation
 * @author      Otoniel Ortega <ortega.x3@gmail.com>
 * @copyright   2014 Otoniel Ortega (c)
 * @version     1.0
 * @licence     The MIT License (MIT)
 *
 */

// Load required libraries //
require_once('libraries/unirest/Unirest.php');
require_once('libraries/shipstation/Shipstation.class.php');


// Disables Unirest SSL validation //
Unirest::verifyPeer(false); 



// Initialize ShipStation ==================================== //

$shipStation 	= new Shipstation();

$shipStation->setSsApiKey('{Your ShipStation API Key Here}');
$shipStation->setSsApiSecret('{Your ShipStation API Secret Here}');



// Define API methods to test ================================ //
// Note: Set to true the methods you want to test //


// Warehouses related methods //

$testGetWarehouses	= true;

// Stores related methods //

$testGetStores		= false;

// Order related methods //

$testGetOrders 		= false;
$testGetOrder 		= false;
$testAddOrder 		= false;
$testDeleteOrder 	= false;

// Shipment related methods //

$testGetShipments 	= false;



// Store Related Methods [START] ============================= //
// =========================================================== //
// =========================================================== //


// Get Stores [START] ======================================== //
// =========================================================== //

if($testGetStores)
{

	echo ">> [Get Stores] ==================================== \n\n";

	$stores	= $shipStation->getStores();

	print_r($stores);

}

// =========================================================== //
// Get Stores [END] ========================================== //


// Store Related Methods [END] =============================== //
// =========================================================== //
// =========================================================== //



// Warehouse Related Methods [START] ========================= //
// =========================================================== //
// =========================================================== //


// Get Warehouses [START] ==================================== //
// =========================================================== //

if($testGetWarehouses)
{

	echo ">> [Get Warehouses] ================================ \n\n";

	$warehouses	= $shipStation->getWarehouses();

	print_r($warehouses);

}

// =========================================================== //
// Get Warehouses [END] ====================================== //


// Warehouse Related Methods [END] =========================== //
// =========================================================== //
// =========================================================== //



// Orders Related Methods [START] ============================ //
// =========================================================== //
// =========================================================== //


// Getting Orders [START] ==================================== //
// =========================================================== //

if($testGetOrders)
{

	echo ">> [Get Orders] ==================================== \n\n";

	$filters 	= array
	(
		'orderNumber'		=> "",
		'orderStatus' 		=> "", // {awaiting_shipment, on_hold, shipped, cancelled}
		'storeid' 			=> "",
		'customerName' 		=> "",
		'itemKeyword'  		=> "", // Searchs on Sku, Description, and Options 
		'paymentDateStart' 	=> "", // e.g. 2014-01-01
		'paymentdateend' 	=> "", // e.g. 2014-01-04 (there is no typo, camel case isn't applied)
		'orderDateStart' 	=> "", // e.g. 2014-01-01
		'orderDateEnd' 		=> "", // e.g. 2014-01-04
		'modifyDateStart' 	=> "", // e.g. 2014-01-01
		'modifyDateEnd' 	=> "", // e.g. 2014-01-04
		'page' 				=> "",
		'pageSize' 			=> "", // Max: 500, Default: 100
	);

	$searchResult 	= $shipStation->getOrders($filters);

	$orders 		= $searchResult->orders;
	$totalResults 	= $searchResult->total;
	$currentPage 	= $searchResult->page;

	// WARNING: if there is only 1 page this value returns 0...

	$totalPages 	= $searchResult->pages;

	print_r($orders);

}

// =========================================================== //
// Getting Orders [END] ====================================== //



// Get an Order [START] ====================================== //
// =========================================================== //

if($testGetOrder)
{

	echo ">> [Get Order] ===================================== \n\n";

	$orderId 	= '{Your Order ID Here}';
	$order 		= $shipStation->getOrder($orderId);

	print_r($order);

}

// =========================================================== //
// Get an Order [END] ======================================== //



// Add an Order [START] ====================================== //
// =========================================================== //

if($testAddOrder)
{

	echo ">> [Add Order] ===================================== \n\n";


	// Defining ShipStation Order [START] ========================= //
	// ============================================================ //

	// Define base order elements //

	$order    = new stdClass();


	$order->orderId           = null;
	$order->orderNumber       = "TEST001";
	$order->orderKey          = null; // if specified, the method becomes idempotent and the existing Order with that key will be updated
	$order->orderDate         = date('Y-m-d').'T'.date('H:i:s').'.0000000';
	$order->paymentDate       = date('Y-m-d').'T'.date('H:i:s').'.0000000';
	$order->orderStatus       = "awaiting_shipment"; // {awaiting_shipment, on_hold, shipped, cancelled}
	$order->customerUsername  = "Otoniel Ortega";
	$order->customerEmail     = "ortega.x3@gmail.com";
	$order->amountPaid        = 150.00;
	$order->taxAmount         = 25.00;
	$order->shippingAmount    = 25.00;
	$order->customerNotes     = null;
	$order->internalNotes     = "Express Shipping Please";
	$order->gift              = null;
	$order->giftMessage       = null;
	$order->requestedShippingService     = "Priority Mail";
	$order->paymentMethod     = null;
	$order->carrierCode       = "fedex";
	$order->serviceCode       = "fedex_2day";
	$order->packageCode       = "package";
	$order->confirmation      = null;
	$order->shipDate          = null;


	// Define billing address //

	$billing    = new stdClass();

	$billing->name          = "Otoniel Ortega"; // This has to be a String... If you put NULL the API cries...
	$billing->company       = null;
	$billing->street1       = null;
	$billing->street2       = null;
	$billing->street3       = null;
	$billing->city          = null;
	$billing->state         = null;
	$billing->postalCode    = null;
	$billing->country       = null;
	$billing->phone         = null;
	$billing->residential   = null;

	$order->billTo          = $billing;


	// Define shipping address //

	$shipping    = new stdClass();

	$shipping->name         = "Otoniel Ortega";
	$shipping->company      = "Go-Parts";
	$shipping->street1      = "Santa Clarita #1234";
	$shipping->street2      = null;
	$shipping->street3      = null;
	$shipping->city         = "Los Angeles";
	$shipping->state        = "CA";
	$shipping->postalCode   = "90002";
	$shipping->country      = "US";
	$shipping->phone        = "555-555-5555";
	$shipping->residential  = true;

	$order->shipTo          = $shipping;


	// Order weight //

	$weight      = new stdClass();

	$weight->value          = 16;
	$weight->units          = "ounces";

	$order->weight          = $weight;


	// Extra order data //

	$dimensions   = new stdClass();

	$dimensions->units      = "inches";
	$dimensions->length     = 5;
	$dimensions->width      = 10;
	$dimensions->height     = 15;

	$order->dimensions      = $dimensions;


	// Insurance options //

	$insuranceOptions   = new stdClass();

	$insuranceOptions->provider         = null;
	$insuranceOptions->insureShipment   = false;
	$insuranceOptions->insuredValue     = 0;

	$order->insuranceOptions            = $insuranceOptions;


	// International options //

	$internationalOptions   = new stdClass();

	$internationalOptions->contents     = null;
	$internationalOptions->customsItems = null;

	$order->internationalOptions        = $internationalOptions;


	// International options //

	$advancedOptions    = new stdClass();

	$advancedOptions->warehouseId       = "{Your Warehouse ID Here}";
	$advancedOptions->nonMachinable     = false;
	$advancedOptions->saturdayDelivery  = false;
	$advancedOptions->containsAlcohol   = false;
	$advancedOptions->storeId           = "{Your Store ID Here}";
	$advancedOptions->customField1      = "";
	$advancedOptions->customField2      = "";
	$advancedOptions->customField3      = "";
	$advancedOptions->source            = null;

	$order->advancedOptions             = $advancedOptions;


	// Add items to order [START] ================================= //
	
	// Loop the order products here...

    // Item //

    $item   = new stdClass();

    $item->lineItemKey          = null;
    $item->sku                  = "TEST-0001-000";
    $item->name                 = "Quad Core Android PC";
    $item->imageUrl             = "http://img.dxcdn.com/productimages/sku_214428_1.jpg";
    $item->weight               = null;
    $item->quantity             = 1;
    $item->unitPrice            = 0;
    $item->warehouseLocation    = null;
    $item->options              = array();

        // Product weight //

        $weight        = new stdClass();

        $weight->value = 16;
        $weight->units = "ounces";


    $item->weight      = $weight;


    // Add to items array //
    $items[] 	= $item;


	$order->items                   = $items;

	// Add items to order [END] =================================== //


	// Defining ShipStation Order [END] =========================== //
	// ============================================================ //


	$order 		= $shipStation->addOrder($order);

	print_r($order);

}

// =========================================================== //
// Add an Order [END] ======================================== //



// Delete and Order [START] ================================== //
// =========================================================== //

if($testDeleteOrder)
{

	echo ">> [Delete Order] ================================== \n\n";

	$orderId 	= "{Your Order ID Here}";
	$result 	= $shipStation->deleteOrder($orderId);

	if($result->success==1)
	{
		// The order was deleted successfully 
		echo "> ".$result->message."\n";
	}
	else
	{
		echo "> There was an error while deleting the order\n";
	}

}

// =========================================================== //
// Delete and Order [END] ==================================== //


// Orders Related Methods [END] ============================== //
// =========================================================== //
// =========================================================== //




// Shipment Related Methods [START] ========================== //
// =========================================================== //
// =========================================================== //


// Getting Shipments [START] ================================= //
// =========================================================== //

if($testGetShipments)
{

	echo ">> [Get Shipments] ================================= \n\n";

	$filters 	= array
	(
		'carrierCode'			=> "", // e.g. fedex
		'orderId' 				=> "",
		'orderNumber' 			=> "",
		'recipientCountryCode' 	=> "",
		'recipientName'  		=> "",
		'serviceCode' 			=> "", // e.g. fedex_ground
		'shipdatestart' 		=> "2014-12-01", // e.g. 2014-01-04
		'shipdateend' 			=> "2014-12-31", // e.g. 2014-01-01
		'trackingNumber' 		=> "",
		'voiddatestart' 		=> "", // e.g. 2014-01-01
		'voiddateend' 			=> "", // e.g. 2014-01-04
		'page' 					=> "",
		'pageSize' 				=> "", // Max: 500, Default: 100
	);

	$searchResult 	= $shipStation->getShipments($filters);

	$shipments 		= $searchResult->shipments;
	$totalResults 	= $searchResult->total;
	$currentPage 	= $searchResult->page;

	// WARNING: if there is only 1 page this value returns 0...

	$totalPages 	= $searchResult->pages;

	print_r($shipments);

}


// =========================================================== //
// Getting Shipments [END] =================================== //


// Shipment Related Methods [END] ============================ //
// =========================================================== //
// =========================================================== //

?>