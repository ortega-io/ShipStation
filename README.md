# ShipStation Php API Wrapper

Thanks to the good guys at **Go-Parts** for allowing me to release this code under MIT License, if you ever need headlights, tail lights or fog lights for your car [Go-Parts](www.go-parts.com) is your place to go.

This is a Php Wrapper Class for the ShipStation API, it is a work in progress but it is enough to get started those looking to push orders to ShipStation, retrieve them and get the respective tracking numbers for shipments. 

The Wrapper currently supports the following operations:

* Get the list of warehouses available
* Get the list of stores available
* Get the list of orders on the system (filtered or unfiltered)
* Get the details for an specific order (like the ShipStation order ID)
* Send a new order to ShipStation
* Delete an order on ShipStation
* Get the list of shipments on the system (filtered or unfiltered)


## Using the demo file

To get your feet wet with the code just look for the places where you have to put your own credentials and keys ( marked with brackets **{}** ) on the **demo.php** and **Shipstation.class.php** files.


## About ShipStation API Request Cap

The Wrapper class is designed to support the API Request Cap as defined by ShipStation, however on my experience such Request Cap *is NOT being honored* and has never been... so be aware that you won't ever reach the 40 requests per second throughput advertised.


## Extending the Wrapper Class

You are more than welcome to add the missing methods on the Wrapper Class, just follow the coding style and the naming conventions, you can find more information about the API at:

```
http://www.shipstation.com/developer-api/
http://docs.shipstation.apiary.io/
```

If you happen to do that, just send me a pull request and I will be more than happy to pull, just be aware that ShipStation's documentation is... well... let's say is not very good, so be sure to test thoroughly.


**DISCLAIMERS:** 

* "ShipStation" and "Go-Parts" are registered trademarks property of their respective owners and are used here and on the code just for reference purposes.

* If you have troubles with the indentation, this code was edited using sublime text 3 and a tab size of 4.


