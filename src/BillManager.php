<?php

namespace Wasksofts\Mpesa;

date_default_timezone_set("Africa/Nairobi");

/**------------------------------------------------------------------------------------------------------------------------
| The bill manager payment feature enables your customers to receive e-receipts for payments made to your paybill account.
|--------------------------------------------------------------------------------------------------------------------------
| *
| * @package     BillerManager class
| * @author      steven kamanu
| * @email       mukamanusteven at gmail dot com
| * @website     htps://wasksofts.com
| * @version     1.0
| * @license     MIT License Copyright (c) 2022 Wasksofts technology
| *-------------------------------------------------------------------------------------------------------------------------
| *-------------------------------------------------------------------------------------------------------------------------
 */

class BillManager
{
    private  $msg = '';
    private  $consumer_key;
    private  $consumer_secret;
    private  $shortcode;
    private  $official_contact;
    private  $logo;
    private  $callback_url;
    private  $live_endpoint;
    private  $sandbox_endpoint;
    private  $env;

    public function __construct()
    {
        $this->live_endpoint      = 'https://api.safaricom.co.ke/';
        $this->sandbox_endpoint   = 'https://sandbox.safaricom.co.ke/';
    }
    /**
     * Mpesa configuration function
     * 
     * @param $key
     * @param $value
     * 
     * @return object
     */
    public function config($key, $value)
    {
        switch ($key) {
            case 'consumer_key':
                $this->consumer_key = $value;
                break;
            case 'consumer_secret':
                $this->consumer_secret = $value;
                break;
            case 'shortcode':
                $this->shortcode = $value;
                break;
            case 'official_contact':
                $this->official_contact = $value;
                break;
            case 'logo':
                $this->logo = $value;
                break;
            case 'callback_url':
                $this->callback_url = $value;
                break;
            case 'env':
                $this->env = $value;
                break;
            default:
                echo 'Invalid config key :' . $key;
                die;
        }
    }

    /** To authenticate your app and get an Oauth access token
     * An access token expires in 3600 seconds or 1 hour
     *
     * @access   private
     * @return   array object
     */
    public function oauth_token()
    {
        $url = $this->env('oauth/v1/generate?grant_type=client_credentials');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        //setting a custom header      
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $curl_response = curl_exec($curl);
        if ($curl_response == true) {
            return json_decode($curl_response)->access_token;
        } else {
            return curl_error($curl);
        }
    }

    /**
     * Business to optin to biller manager
     * 
     * This is the first API used to opt you as a biller to our bill manager features. 
     * Once you integrate to this API and send a request with a success response, 
     * your shortcode is whitelisted and you are able to integrate with all the other remaining bill manager APIs.
     * 
     * @param $email ,$reminder
     * @return array
     */
    public function optin_biller($email, $reminders = 1)
    {
        $url =  $this->env('v1/billmanager-invoice/change-optin-details');

        //Fill in the request parameters 
        $curl_post_data = array(
            "shortcode" => $this->shortcode,
            "logo" => $this->logo,
            "email" => $email,
            "officialContact" => $this->official_contact,
            "sendReminders" => $reminders,
            "callbackUrl" =>  $this->callback_url
        );

        $this->query($url, $curl_post_data);
    }

    /** 
     * Modify Onboarding Details
     * This API allows you to update the Onboarding fields. 
     * These are the fields you can update
     * 
     */
    public function optin_update($email, $reminders = 1)
    {
        $url =  $this->env('v1/billmanager-invoice/optin');

        //Fill in the request parameters 
        $curl_post_data = array(
            "shortcode" => $this->shortcode,
            "logo" => $this->logo,
            "email" => $email,
            "officialContact" => $this->official_contact,
            "sendReminders" => $reminders,
            "callbackUrl" =>  $this->callback_url
        );

        $this->query($url, $curl_post_data);
    }


    /**
     * Bill Manager invoicing service enables you to create and send e-invoices to your customers.
     * Single invoicing functionality will allow you to send out customized individual e-invoices 
     * Your customers will receive this notification(s) via an SMS to the Safaricom phone number specified while creating the invoice.
     */
    public function single_invoice($reference, $billedfullname, $billedphoneNumber, $billedperiod, $invoiceName, $dueDate, $accountRef, $amount)
    {
        $url =  $this->env('v1/billmanager-invoice/single-invoicing');

        //Fill in the request parameters 
        $curl_post_data = array(
            "externalReference" => $reference,
            "billedFullName" => $billedfullname,
            "billedPhoneNumber" => $billedphoneNumber,
            "billedPeriod" => $billedperiod,
            "invoiceName" => $invoiceName,
            "dueDate" => $dueDate,
            "accountReference" => $accountRef,
            "amount" => $amount
        );

        $this->query($url, $curl_post_data);
    }

    /**   
     * Bulk invoicing
     *  while bulk invoicing allows you to send multiple invoices.
     * 
     * @param array
     */
    public function bulk_invoicing($invoiceArray)
    {
        $url =  $this->env('v1/billmanager-invoice/bulk-invoicing');
        $this->query($url, $invoiceArray);
    }

    /**
     * Reconciliation
     * 
     * @param  string
     * @return array
     */
    public function reconciliation($payment_date, $paidAmmount, $actReference, $transactionId, $phoneNumber, $fullName, $invoiceName, $reference)
    {
        $url =  $this->env('v1/billmanager-invoice/reconciliation');

        //Fill in the request parameters 
        $curl_post_data = array(
            "paymentDate" => $payment_date,
            "paidAmount" => $paidAmmount,
            "accountReference" => $actReference,
            "transactionId" => $transactionId,
            "phoneNumber" => $phoneNumber,
            "fullName" => $fullName,
            "invoiceName" => $invoiceName,
            "externalReference" => $reference
        );

        $this->query($url, $curl_post_data);
    }

    /**
     * 
     *  Update invoice API allows you to alter invoice items by using the external reference previously used to create the invoice you want to update.
     *  Any other update on the invoice can be done by using the Cancel Invoice API which will recall the invoice,
     *  then a new invoice can be created. The following changes can be done using the Update Invoice API
     * 
     */

    public function update_invoice_data()
    {
        $url =  $this->env('v1/billmanager-invoice/change-invoice');

        //Fill in the request parameters 
        $curl_post_data = array(
            "paymentDate" => "2021-10-01",
            "paidAmount" => "800",
            "accountReference" => "Balboa95s",
            "transactionId" => "PL141KEBZS",
            "phoneNumber" => "0722000000",
            "fullName" => "John Doe",
            "invoiceName" => "Parking Fee",
            "externalReference" => "955"
        );

        $this->query($url, $curl_post_data);
    }

    public function cancel_single_invoice($reference)
    {
        $url =  $this->env('v1/billmanager-invoice/cancel-single-invoice');

        //Fill in the request parameters 
        $curl_post_data = array(
            "externalReference" => $reference
        );

        $this->query($url, $curl_post_data);
    }

    public function cancel_bulk_invoice($array)
    {
        $url =  $this->env('v1/billmanager-invoice/cancel-single-invoice');
        $this->query($url, $array);
    }

    /** query function
     * 
     * @param  $url
     * @param  $curl_post_data
     * @return  null
     */
    public function query($url, $curl_post_data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //setting custom header
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'charset=utf8', 'Authorization:Bearer ' . $this->oauth_token()));

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $curl_response = curl_exec($curl);
        if ($curl_response == true) {
            $this->msg = $curl_response;
        } else {
            $this->msg = curl_error($curl);
        }
    }

    /** get environment url
     *
     * @access public
     * @param  string $request_url
     * @return string
     */
    public function env($request_url = null)
    {
        if (!is_null($request_url)) {
            if ($this->env === "sandbox") {
                return $this->sandbox_endpoint . $request_url;
            } elseif ($this->env === "live") {
                return $this->live_endpoint . $request_url;
            }
        }
    }

    /**
     *  response on api call
     * 
     *  @return data array or json
     */
    public function getResponseData($array = NULL)
    {
        if ($array == TRUE) {
            return $this->msg;
        }
        return json_decode($this->msg);
    }
}
