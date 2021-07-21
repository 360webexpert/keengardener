## [1.3.17] - 2020-11-24
### Fixed
- 3Dv1 not working with Protocol 4.00 for PI
- PI refund problem with Multi-Store sites
- Duplicated Callbacks received for FORM

## [1.3.16] - 2020-10-27
### Changed
- Server cancel payment redirection to checkout shipping method
- Added new Order Details fields names in block

### Fixed
- Fix duplicate 3D callback and duplicate response for threeDSubmit
- CSP Whitelisting file
- Add routes to webRestrctions.xml to avoid payment failures when Magento's EE restrictions is enabled

## [1.3.15] - 2020-07-08
### Changed
- Sage Pay text and logo changed to Opayo

### Fixed
- Adapt 3Dv2 to latest updates
- Duplicated address problem
- 3D, Address, Postcode and CV2 flags not showing up on the order grid
- Recover Cart problem when multiple items with same configurable parent
- Order cancelled when same increment id on different store views
- Duplicated PI Callbacks received cancel the order
- Server not recovering cart when cancel the transaction
- Add form validation in PI WITHOUT Form

## [1.3.14] - 2020-04-13
### Fixed
- Fix PI not working with virtual product and guest checkout
- Problem with js calls not being sequential and causing errors on checkout with PI
- Amount is not an integer when trying to make a refund

## [1.3.13] - 2020-03-18
### Changed
- Store SecurityKey in Database when SyncFromApi
- Enhance cart recovery to avoid orders cancelling when customer goes to checkout/cart
- Round amount when populating amount and currency to avoid 0.01 difference

### Fixed
- Orders cancelled when same IncrementId on different Store Views
- Order not available error with FORM

## [1.3.12] - 2020-02-12
## Added
- Compatibility with Magento 2.3.4
- Show verification results in payment layout at order details

### Changed
- Look transaction by vendorTxCode if not VPSTxId when SyncFromApi

### Fixed
- Problem with basket format when using Sage50
- Error while trying to cancel SERVER Authenticate order

## [1.3.11] - 2019-12-19
### Fixed
- Items being canceled when order take more than 15 minutes
- Guest order being created with "Guest" as customer name
- Pi not loading when there are terms and conditions

## [1.3.10] - 2019-11-26
### Added
- Show Fraud information on order grid (3D, Post Code, Address, CV2)

### Changed
- New PI endpoint

### Fixed
- Order failing if using special characters on order id prefix
- Fraud flag showing no flag when 3rd Man and there's no Fraud Rule

### Security
- Encrypt PI callback URL

## [1.3.9] - 2019-10-28
### Added
- Compatibility with Magento 2.3.3 and 2.3.2-p2
- Setting to open 3D verification in new window for PI

### Changed
- Sanitize Post Code on PI
- Remove spaces from paRes
- Remove "Load secure credit card form" PI button

### Fixed
- Frontend using Default Config values instead of Store values on Frontend
- Multiple 3D responses problem

## [1.3.8] - 2019-10-01
### Added
- PI support for PSD2 and SCA
- Payment Failed Emails impelentation for PI

### Fixed
- Fix DropIn not working with Minify js
- Stop the order for try to being captured if txstateid empty
- 0.00 cost products breaks PayPal
- Fix Multi Currency Authenticate invoice using Base Currency amount

## [1.3.7] - 2019-08-08
### Added
- Setting to set max tokens per customer

### Changed
- Hide Add New Card when reached max tokens
- Change excluding minification strategy

### Fixed
- Label and Checkbox from first token being shown when press add new card
- Send 000 post code when field is left empty for Ireland or Hong Kong (SERVER and FORM)
- PI always sending 000 post code for Ireland and Hong Kong even if the customer entered a post code
- Module breaks Sales -> Order when the payment additional information is serialized
- Multi Currency refunds using Base Currency amount (FORM, SERVER, PayPal)

## [1.3.6] - 2019-06-24
### Added
- SERVER and FORM support for PSD2 and SCA
- PI DropIn compatibility with OneStepCheckout

### Fixed
- Module breaks Sales -> Order
- Server defer orders not being cancelled on SagePay
- Problem with submit payment button PI
- PI always selected as default payment method on the checkout

## [1.3.5] - 2019-05-08
### Added
- Explanation message to order view
- Add waiting for score and test fraud flags
- Add CardHolder Name field to PI without DropIn

### Changed
- Update README.md to use url sagepaysuite.gitlab.ebizmarts.com for composer config.

### Fixed
- PI DropIn MOTO problem with multiple storeviews
- Invoice and Refund problem with multi currency site and base currency
- Basket Sage50 doesn't send space character

### Removed
- PHP restrictions on module for M2.1
- Remove cc images from the Pi form

## [1.3.4] - 2019-03-27
### Added
- Compatibility with Magento 2.3.1

## [1.3.3] - 2019-03-26
### Changed
- On Hold status stop auto-invoice

### Fixed
- Conflict problems on db_schema
- Redirect to empty cart fix
- Multi-Currency invoice use base currency amount
- Defer invoice problem with Multi-Store setup
- Repeat problem with Multi-Store setup

## [1.3.2] - 2019-02-05
### Changed
- 3D secure iframe alignment on mobile devices

### Fixed
- last_trans_id field on table sales_order_payment truncated to 32, causing error on callbacks

### Security
- Encrypt callback URL

## [1.3.1] - 2019-01-07
### Added
- Invoice confirmation email for Authorise and capture

### Changed
- Server low profile smaller modal window

### Fixed
- Cancel or Void a Defer order without invoice
- Refund problem on multi-currency sites
- PI without DropIn problem when you enter a wrong CVN
- Problem with refunds on multi-sites using two vendors
- Exception thrown when open Fraud report
- Basket XML constraint fix
- Magento's sign appearing when click fraud cell

## [1.3.0] - 2018-12-04
### Fixed
- Magento not running schema updates. Switching to Schema patches
- New CSRF checks rejecting callbacks

[1.3.17]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.17
[1.3.16]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.16
[1.3.15]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.15
[1.3.14]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.14
[1.3.13]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.13
[1.3.12]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.12
[1.3.11]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.11
[1.3.10]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.10
[1.3.9]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.9
[1.3.8]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.8
[1.3.7]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.7
[1.3.6]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.6
[1.3.5]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.5
[1.3.4]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.4
[1.3.3]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.3
[1.3.2]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.2
[1.3.1]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.1
[1.3.0]: https://github.com/ebizmarts/magento2-sage-pay-suite/releases/tag/1.3.0