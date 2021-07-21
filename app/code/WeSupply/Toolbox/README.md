# WeSupply_Toolbox


### Installation

With composer:

```sh
$ composer config repositories.wesupply-toolbox git git@github.com:rusdragos/WeSupply_Toolbox.git
$ composer require wesupply/toolbox:dev-master
```

Note: Composer installation only available for internal use for the moment as the repositos are not public. However, there is a work around that will allow you to install the product via composer, described in the article below: https://support.weltpixel.com/hc/en-us/articles/115000216654-How-to-use-composer-and-install-Pearl-Theme-or-other-WeltPixel-extensions

Manually:

Copy the zip into app/code/WeSupply/Toolbox directory


#### After installation by either means, enable the extension by running following commands:

```sh
$ php bin/magento module:enable WeSupply_Toolbox --clear-static-content
$ php bin/magento setup:upgrade
```

###Release Notes

Version 1.10.15, Jul 14th, 2021  

-Optimized database performance by adding a cron job that removes orders older than 6 months from WeSupply table  
-Enhanced compatibility with 3rd party ERP software by adding a cron job that automatically detects order updates  
-Fixed an error that was thrown we WeSupply tried to import an order containing a product that no longer exists  
-Fixed an issue that prevented pickup and curbside orders from being canceled via the frontend  

Version 1.10.14, Jun 8th, 2021  

-New Feature: Added a new option in the Toolbox configuration section that allows for excluding orders in the "Complete" status from being imported into WeSupply (this applies only to orders created directly with the "Complete" status)  
-Added XSS security enhancements  

Version 1.10.13, May 10th, 2021  

-Extended WeSupply import process to include Virtual and Downloadable products  
-Optimized the order export functionality to avoid duplicate items in case the shipment is created/updated from an external processor, via an API call  
-Fixed a bug related to Estimated Delivery Date range calculation and display  
  
Version 1.10.12, April 8th, 2021

-New Feature: Added a new option in the Toolbox configuration section that allows for excluding pending orders from being imported into WeSupply  
-Fixed a bug that prevented estimation ranges from being displayed on the frontend  
  
Version 1.10.11, March 11th, 2021  

-New Feature: Implemented the In store pickup functionality  
-New Feature: Added new admin configuration options for Estimated Delivery Date frontend display  
-Added Help Center Pages in the Magento Admin WeSupply section  
-Fixed a height issue related to the Shipment Tracking iFrame  
  
Version 1.10.10, February 1st, 2021  

-Fixed an issue specific to Magento 2.4.x which prevented estimates from working on configurable products  
-Fixed a height issue related to the store locator iframe  
-Added more detailed error logs in case of failed refunds  

Version 1.10.9, December 8th, 2020  

-Fixed an issue whereby online refunds would sometimes be processed offline  
-Fixed a small Shipping Method Title display issue on the Checkout Page  

Version 1.10.8, November 25th, 2020  

-Fixed an issue related to processing refunds when the Magento MSI functionality was disabled  
  
Version 1.10.7, November 23th, 2020  

-Added compatibility with Magento's In-Store pickup functionality (Magento 2.4.x)  
-Added more specific messages in case of errors during return process 
-Optimized iframe resizer for Open in Modal order view behavior  
-Updated product image path generation process  
  
Version 1.10.6, November 5th, 2020  

-Updated order export functionality to include compatibility with the Magento Multi Source Inventory  
  
Version 1.10.5, October 19th, 2020  

-Removed deprecated refund method  
  
Version 1.10.4, October 9th, 2020  

-Orders excluded through the import process are no longer saved in the DB. This avoids accidental duplicate orders  in WeSupply  
  
Version 1.10.3, September 29th, 2020  

-Optimized and improved FetchSingleOrder API functionality  
-Style adjustments for embedded store locator iframes  

Version 1.10.2, September 9th, 2020  

-Fixed an issue related to SMS Notification subscribe functionality on the Success Page  

Version 1.10.1, September 2nd, 2020  

-Adjusted deprecated callbacks of iframeResizer library  
-Added more specific targeting to WeSupply iFrames  
-Bypassed CDN for iframeResizer JS loading. JS files now load directly via the server, and not via a CDN  

Version 1.10.0, August 11th, 2020  

-Whitelisted WeSupply domain for Content Security Policies  
-Fixed and improved domain alias functionality  
-Changed the WeSupply links for Order View and Returns to be displayed as a dropdown  
-Confirmed compatibility with the newly released Magento 2.4.0 version  
-Confirmed compatibility with PHP7.4  

Version 1.9.12, July 1st, 2020  

-Removed an unnecessary hidden input that, in some cases, caused an error on the checkout if no estimates were available  
-Added new config fields to map and export to WeSupply the product attributes used to define weight and measurements  
-Rebuilt delivery estimation functionality based on the newly created config fields  

Version 1.9.11, June 26th, 2020  

-Added WeSupply Order View and Returns List as embedded iFrames under Magento's Admin > Sales > Order View page, which offers the possibility to directly interact with the orders and returns synced with the WeSupply platform  

Version 1.9.10, June 17th, 2020  

-Added new functionality that allows for choosing additional product attributes which can be used for setting up WeSupply Return Logic  
-Added the delivery date selected by the customer in the checkout process to the order export  

Version 1.9.9, April 22th, 2020  

-Optimized WeSupply connection steps  
-Small bug fixes and other minor optimizations  

Version 1.9.8, March 25th, 2020  

-Added new functionality that allows setting multiple refunds types on the same return request  
-Other minor optimizations / improvements  

Version 1.9.7, March 2nd, 2020  

-Added new functionality that allows for choosing which Magento orders are exported to WeSupply: All Orders, No Orders or Exclude Specific Orders based on shipping country  
-Added link to product in WeSupply confirmation email templates  
-Fixed a bug which caused the WeSupply view orders functionality in Magento to break on Safari (iOS) by adding a new option in WeSupply called "Domain Alias"  
-Added more customer details to exported orders from Magento to WeSupply  
-Added WeSupply Return comments in Magento Credit Memo history  
-Other minor optimizations / improvements  

Version 1.9.6, February 14th, 2020  

-Improved functionality of SMS Notification subscription  
-Added SMS Notification unsubscription functionality  

Version 1.9.5, February 6th, 2020

-WeSupply and Magento connection errors are now more specific. Before this version, a generic "Invalid API credentials" error was thrown.  
-Orders are now updated in the WeSupply dashboard based on tracking number modifications/updates via Magento.  
-A new "None" option was added in the WeSupply dashboard for the Refund Processor setting. Before this update, the only available option was "Magento", and could not be deselected after being saved once.  
-Upon issuing a refund via WeSupply, if there is no Refund Processor selected, the Refund button is now disabled and a notice is shown which prompts you to set a Refund Processor.  
