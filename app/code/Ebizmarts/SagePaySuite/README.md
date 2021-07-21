# Sage Pay Suite integration for Magento 2

[CHANGELOG](https://github.com/ebizmarts/magento2-sage-pay-suite/blob/master/CHANGELOG.md)

## Installation Instructions

## Composer installation (preferred method)
1. Run this command on Magento's root dir.

`composer config repositories.ebizmarts composer https://packages.ebizmarts.com`

2. Run this command on Magento's root dir. Contact us to get your access token.

`composer config http-basic.sagepaysuite.gitlab.ebizmarts.com token your_token`

3. Run this command to get the module.

`composer require ebizmarts/sagepaysuite`

4. Install the module.

`bin/magento setup:upgrade`

##Token expired and it's causing problems.
If your token expired and it's causing problems when you run composer update. 
You can renew your support to get a new token or simply run `composer config --unset repositories.ebizmarts`

## Manual installation

__Requirements__

  - The ZIP file named **Ebizmarts_SagePaySuiteM2-1.3.0.zip**
  - Access to the Magento 2 server via SSH
  - The unzip command should be available, check by running `which unzip`
  - [Composer](https://getcomposer.org/) needs to be installed in the server

__Installation__
 
  1. Upload the ZIP file to the Magento 2 server.

  2. Get access to the Magento 2 server.

  3. Go to the Magento2 modules folder.

    $ cd $MAGENTO_FOLDER$/app/code

  4. Create the directory (if it does not exist) that will hold the module contents
    `$ mkdir -p Ebizmarts/SagePaySuite`
   
  5. Go to the SagePaySuite folder
  
    $ cd $MAGENTO_FOLDER$/app/code/Ebizmarts/SagePaySuite
   
  6. Uncompress Sage Pay Suite package
  
    $ unzip /PATH/TO/PACKAGE/Ebizmarts_SagePaySuiteM2-1.3.0.zip

  7. This will create the following content in $MAGENTO_FOLDER$/app/code
    <pre>
    └── Ebizmarts
        └── SagePaySuite
            ├── Api
            ├── Block
            ├── Controller
            ├── Helper
            ├── Model
            ├── Observer
            ├── Setup
            ├── Test
            ├── etc
            ├── i18n
            └── view
    </pre>
  8. Go to the magento root folder (where composer.json is located)

    $ cd $MAGENTO_FOLDER$

  9. Execute Magento setup upgrade

    $ bin/magento setup:upgrade

  10. Clean cache and generated code

    $ bin/magento cache:clean
    
    $ rm -rf var/generation/*

  11. Run magento compiler to generate auto-generated classes

    $ bin/magento setup:di:compile

   (this will take some time ...)

__Test__

  You can check if the module was properly installed testing some features introduced by Sage Pay Suite:
  
  1. Get access to the Magento 2 backoffice.

  2. Menu > Stores > Configuration > SALES > Payment Methods
  You should see Sage Pay Suite on the payment methods list.
  3. Enter your Sage Pay vendorname and Ebizmarts license key on the configuration settings.
  4. Enable the integration of your preference.

[![Build Status](https://circleci.com/gh/ebizmarts/magento2-sage-pay-suite.svg?style=shield&circle-token=9d950c73b76af8868862caf8400c549439838d47)](https://circleci.com/gh/ebizmarts/magento2-sage-pay-suite)
