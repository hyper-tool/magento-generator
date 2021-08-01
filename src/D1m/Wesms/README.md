# Summary
- Module Name: D1m_Wesms
- Based on the wemediacn SMS web service to send SMS


## Installation

You can add this module by php bin/magento:

    php bin/magento module:enable D1m_Wesms
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento cache:flush


## Configuration

- After install, i can add the SMS Web Service and Web Service Validation Token Paramters in the admin. if you don't add the paramters, it can not work rightly.
- Configuration position: STORES -> Settings -> Configuration -> D1m -> SMS Setting.
- SMS part: SMS Methods \ Url \ Token
- In china, this type SMS message template need to register to the SMS service provider. so we add a function to let you add SMS templates in the admin, it's at: SYSTEM -> Wemediacn SMS -> SMS Template


## The Version Of History

### v1.0.0

- INIT

### V1.0.1

- add order payment notify SMS template
- add order shipped notify SMS template
- add order RMA notify SMS template

### v1.0.3
- add edit mobile sms check 



