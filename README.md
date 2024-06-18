# Introduction
Mpesa library which you can use with various framework like laravel ,codeigniter ,cakephp and many more
This package seeks to help php developers implement the various Mpesa APIs without much hustle. It is based on the REST API whose documentation is available on https://developer.safaricom.co.ke.

#  Installation using composer
``` bash
composer require wasksofts-technology/mpesa
```

#  Usage example

     require_once('vendor/autoload.php')
  use the above statement if it procedural app else if its codeigniter 3.x go config enable $config['composer_autoload'] = 'vendor/autoload.php'; 
  if vendor is root folder if vendor file are on application it should be $config['composer_autoload'] = true ,
  for laravel and other framework they have no problem
     <?php
     require_once('vendor/autoload.php');
     
     use Wasksofts\Mpesa\Mpesa;

    $mpesa  = new Mpesa();
    $mpesa->config('consumer_key', '');
    $mpesa->config('consumer_secret', '');
    $mpesa->config('pass_key', '');
    $mpesa->config('store_number', '174379');
    $mpesa->config('business_shortcode', '');
    $mpesa->config('callback_url', 'https://example.com/callback_url/');
    $mpesa->config('confirmation_url', 'https://example.com/confirmation_url/');
    $mpesa->config('validation_url', 'https://example.com/validation_url/');
    $mpesa->config('initiator_name', '');
    $mpesa->config('initiator_pass', '');
    $mpesa->config('b2c_shortcode', '');
    $mpesa->config('security_credential','');
    $mpesa->config('result_url', 'https://example.com/result_url/'); 
    $mpesa->config('timeout_url', 'https://example.com/timeout_url/');
    $mpesa->config('env', 'sandbox');
    
    echo " Token : " . $mpesa->oauth_token();
    $mpesa->STKPushQuery('ws_CO_DMZ_297481201_09042019174418021');
    $mpesa->STKPushSimulation('1','254708374149','pay now','test');
    $mpesa->register_url(); 
    $mpesa->c2b_simulation('1000', '254708374149', 'account');
    $mpesa->b2c('200', 'BusinessPayment', '254708374149', 'payment','b2c_timeout','b2c_result'); // last two parameter define callback https://example.com/result_url.php/b2c_timeout/ or https://example.com/result_url/b2c_result/
    $mpesa->b2b('10000','BusinessPayBill','60000','4','4','paytest','cool','b2b_timeout','b2b_result');
    $mpesa->accountbalance('600443','4','remarks','acc_timeout','acc_result');
    $mpesa->reversal('2','254708374149','1','NCR7S1UXBT','PAY NOW VIA WASKSOFT');
    $mpesa->transaction_status('NCR7S1UXBT','254708374149','4','apitest');
    
 # get responses
    echo $mpesa->getResponseData();
    
# Callback json data received from safaricom
    for call back you can use you own implementation 
    this is for testing.
    
    use Wasksofts\Mpesa\Callback;

    $callback = new Callback;
    $callback::processSTKPushRequestCallback();
    $callback::processC2BRequestConfirmation();
    $callback::processC2BRequestValidation();
    $callback::processB2CRequestCallback();
    $callback::processB2BRequestCallback();
    $callback::processAccountBalanceRequestCallback();
    $callback::processReversalRequestCallBack();
    $callback::processTransactionStatusRequestCallback();

    
  ## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mukamanusteven@gmail.com instead of using the issue tracker.

## Credits

- [wasksofts](https://github.com/wasksofts)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
