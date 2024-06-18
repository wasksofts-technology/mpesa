<?php

namespace Wasksofts\Mpesa;

date_default_timezone_set("Africa/Nairobi");

/**----------------------------------------------------------------------------------------
| Mpesa Api library
|------------------------------------------------------------------------------------------
| *
| * @package     mpesa class
| * @author      steven kamanu
| * @email       mukamanusteven at gmail dot com
| * @website     htps://wasksofts.com
| * @version     1.0
| * @license     MIT License Copyright (c) 2017 Wasksofts technology
| *--------------------------------------------------------------------------------------- 
| *---------------------------------------------------------------------------------------
 */
class Mpesa
{
  private  $msg = '';
  private  $security_credential;
  private  $consumer_key;
  private  $consumer_secret;
  private  $store_number;
  private  $business_shortcode;
  private  $pass_key;
  private  $initiator_name;
  private  $initiator_pass;
  private  $callback_url;
  private  $confirmation_url;
  private  $validation_url;
  private  $b2c_shortcode;
  private  $b2b_shortcode;
  private  $result_url;
  private  $timeout_url;
  private  $live_endpoint;
  private  $sandbox_endpoint;
  private  $env;

  function __construct()
  {
    //$this->config = Config::getInstance();
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
        $this->consumer_key = trim($value);
        break;
      case 'consumer_secret':
        $this->consumer_secret = trim($value);
        break;
      case 'store_number':
        $this->store_number = $value;
        break;
      case 'business_shortcode':
        $this->business_shortcode = $value;
        break;
      case 'b2c_shortcode':
        $this->b2c_shortcode = $value;
        break;

      case 'b2b_shortcode':
        $this->b2b_shortcode = $value;
        break;
      case 'initiator_name':
        $this->initiator_name = trim($value);
        break;
      case 'initiator_pass':
        $this->initiator_pass = trim($value);
        break;
      case 'pass_key':
        $this->pass_key = trim($value);
        break;
      case 'security_credential':
        $this->security_credential = $value;
        break;
      case 'callback_url':
        $this->callback_url = $value;
        break;
      case 'confirmation_url':
        $this->confirmation_url = $value;
        break;
      case 'validation_url':
        $this->validation_url = $value;
        break;
      case 'result_url':
        $this->result_url = $value;
        break;
      case 'timeout_url':
        $this->timeout_url = $value;
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


  /** C2B enable Paybill and buy goods merchants to integrate to mpesa and receive real time payment notification
   *  C2B register URL API register the 3rd party's confirmation and validation url to mpesa
   *  which then maps these URLs to the 3rd party shortcode whenever mpesa receives atransaction on the shortcode
   *  Mpesa triggers avalidation request against the validation URL and the 3rd party system responds to mpesa 
   *  with a validation response (eithera success or an error code)
   *
   *  @param  $status Completed/Cancelled
   *  @return json
   */
  public function register_url($status = 'Cancelled')
  {
    $url =  $this->env('mpesa/c2b/v1/registerurl');

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'ShortCode' => $this->store_number,
      'ResponseType' => $status,
      'ConfirmationURL' => $this->confirmation_url,
      'ValidationURL' => $this->validation_url
    );

    $this->query($url, $curl_post_data);
  }

  /** C2B simulate transaction for sandbox only
   * note this is run only in sandbox mode for simulation
   * @param  int   $Amount | The amount been transacted.
   * @param  int   $Msisdn | MSISDN (phone number) sending the transaction, start with country code without the plus(+) sign.
   * @param  string  $BillRefNumber | Bill Reference Number (Optional).
   * @param  string  $commandId CustomerPayBillOnline or CustomerBuyGoodsOnline
   * @return null
   */
  public function c2b_simulation($Amount, $Msisdn, $BillRefNumber = NULL, $commandId = 'CustomerBuyGoodsOnline')
  {
    $url =  $this->env('mpesa/c2b/v1/simulate');

    //Fill in the request parameters with valid values        
    $curl_post_data = array(
      'ShortCode' => $this->business_shortcode,
      'CommandID' => $commandId,
      'Amount' => $Amount,
      'Msisdn' => $Msisdn,
      'BillRefNumber' => $BillRefNumber  // '00000' //optional
    );

    $this->query($url, $curl_post_data);
  }

  /** STK Push Simulation lipa na M-pesa Online payment API is used to initiate a M-pesa transaction on behalf of a customer using STK push
   * This is the same technique mySafaricom app uses whenever the app is used to make payments
   *  
   * @param  int  $amount
   * @param  int  $PartyA | The MSISDN sending the funds.
   * @param  int  $AccountReference  | (order id) Used with M-Pesa PayBills
   * @param  string  $TransactionDesc | A description of the transaction.
   * @param  string  $transactionType 'CustomerPayBillOnline' or CustomerBuyGoodsOnline
   * @return  null
   */
  public function STKPushSimulation($Amount, $phoneNumberSendingFund, $AccountReference, $TransactionDesc, $transactionType = 'CustomerBuyGoodsOnline')
  {
    $url =  $this->env('mpesa/stkpush/v1/processrequest');

    //Fill in the request parameters with valid values     
    $curl_post_data = array(
      'BusinessShortCode' => $this->store_number,
      'Password' => $this->password(),
      'Timestamp' => $this->timestamp(),
      'TransactionType' => $transactionType,
      'Amount' => $Amount,
      'PhoneNumber' => $phoneNumberSendingFund,
      'PartyA' => $phoneNumberSendingFund,
      'PartyB' => $this->business_shortcode,
      'CallBackURL' => $this->callback_url,
      'AccountReference' => $AccountReference,
      'TransactionDesc' => $TransactionDesc
    );

    $this->query($url, $curl_post_data);
  }



  /** STK Push Status Query
   * This is used to check the status of a Lipa Na M-Pesa Online Payment.
   *
   * @param   string  $checkoutRequestID | Checkout RequestID
   * @return  array object
   */
  public function STKPushQuery($checkoutRequestID)
  {
    $url =  $this->env('mpesa/stkpushquery/v1/query');

    //Fill in the request parameters with valid values        
    $curl_post_data = array(
      'BusinessShortCode' => $this->store_number,
      'Password'  => $this->password(),
      'Timestamp' => $this->timestamp(),
      'CheckoutRequestID' => $checkoutRequestID
    );

    $this->query($url, $curl_post_data);
  }


  /**  
   * B2C Payment Request transactions betwwen a company and customers 
   * who are the enduser of its products ir services
   * command id SalaryPayment,BussinessPayment ,PromotionPayment
   *
   * @param   int       $amount
   * @param   string    $commandId | Unique command for each transaction type e.g. SalaryPayment, BusinessPayment, PromotionPayment
   * @param   string    $receiver  | Phone number receiving the transaction
   * @param   string    $remark    | Comments that are sent along with the transaction.
   * @param   string    $ocassion  | optional
   * @return  null
   */
  public function b2c($amount, $commandId, $receiver, $remark,  $result_url, $timeout_url, $occassion = null)
  {
    $url = $this->env('mpesa/b2c/v1/paymentrequest');

    //Fill in the request parameters with valid values           
    $curl_post_data = array(
      'InitiatorName' => $this->initiator_name,
      'SecurityCredential' => $this->security_credential(),
      'CommandID' => $commandId,
      'Amount' => $amount,
      'PartyA' => $this->b2c_shortcode,
      'PartyB' => $receiver,
      'Remarks' => $remark,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'ResultURL' => $this->result_url . $result_url,
      'Occasion' => $occassion
    );

    $this->query($url, $curl_post_data);
  }

  /** B2B Payment Request transactions between a business and another business
   * Api requires a valid and verifiedB2B Mpesa shortcode for the business initiating the transaction 
   * andthe bothbusiness involved in the transaction
   * Command ID : BussinessPayBill ,MerchantToMerchantTransfer,MerchantTransferFromMerchantToWorking,MerchantServucesMMFAccountTransfer,AgencyFloatAdvance
   *
   * @param  int      $Amount
   * @param  string   $commandId
   * @param  int      $PartyB | Organization’s short code receiving the funds being transacted.
   * @param  int      $SenderIdentifierType | Type of organization sending the transaction. 1,2,4
   * @param  int      $RecieverIdentifierType | Type of organization receiving the funds being transacted. 1,2,4
   * @param  string   $AccountReference | Account Reference mandatory for “BusinessPaybill” CommandID.
   * @param  string   $remarks
   * @return  null 
   */
  public function b2b($Amount, $PartyB, $commandId, $AccountReference, $Remarks, $result_url, $timeout_url)
  {
    $url =  $this->env('/mpesa/b2b/v1/paymentrequest');

    $curl_post_data = array(
      //Fill in the request parameters with valid values
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->security_credential(),
      'CommandID' => $commandId,
      'SenderIdentifierType' => 4,
      'RecieverIdentifierType' => 4,
      'Amount' => $Amount,
      'PartyA' => $this->b2b_shortcode,
      'PartyB' => $PartyB,
      'AccountReference' => $AccountReference,
      'Remarks' => $Remarks,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'ResultURL' => $this->result_url . $result_url
    );

    $this->query($url, $curl_post_data);
  }

  /** Account Balance API request for account balance of a shortcode
   * 
   * @access  public
   * @param   int     $PartyA | Type of organization receiving the transaction
   * @param   int     $IdentifierType |Type of organization receiving the transaction
   * @param   string  $Remarks | Comments that are sent along with the transaction.
   * @return  null
   */
  public function accountbalance($IdentifierType, $Remarks, $result_url, $timeout_url)
  {
    $url =  $this->env('mpesa/accountbalance/v1/query');

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->security_credential(),
      'CommandID' => 'AccountBalance',
      'PartyA' => $this->store_number,
      'IdentifierType' => $IdentifierType,
      'Remarks' => $Remarks,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'ResultURL' => $this->result_url . $result_url
    );

    $this->query($url, $curl_post_data);
  }

  /** reverses a B2B ,B2C or C2B Mpesa,transaction
   *
   * @access  public
   * @param   int      $amount
   * @param   int      $ReceiverParty
   * @param   int      $TransactionID
   * @param   int      $RecieverIdentifierType
   * @param   string   $Remarks
   * @param   string   $Ocassion
   * @return  null
   */
  public function reversal($Amount, $TransactionID, $Remarks, $result_url, $timeout_url, $Occasion = NULL)
  {
    $url =  $this->env('mpesa/reversal/v1/request');

    //Fill in the request parameters with valid values      
    $curl_post_data = array(
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->security_credential(),
      'CommandID' => 'TransactionReversal',
      'TransactionID' => $TransactionID,
      'Amount' => $Amount,
      'ReceiverParty' => $this->store_number,
      'RecieverIdentifierType' => 11,
      'ResultURL' => $this->result_url . $result_url,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'Remarks' => $Remarks,
      'Occasion' => $Occasion
    );

    $this->query($url, $curl_post_data);
  }


  /** Transaction Status Request API checks the status of B2B ,B2C and C2B APIs transactions
   *
   * @access  public
   * @param   string  $TransactionID | Organization Receiving the funds.
   * @param   int     $PartyA | Organization/MSISDN sending the transaction
   * @param   int     $IdentifierType | Type of organization receiving the transaction 1 – MSISDN 2 – Till Number 4 – Organization short code
   * @param   string  $Remarks
   * @param   string  $Ocassion
   * @return  null
   */
  public function transaction_status($TransactionID,  $Remarks, $result_url, $timeout_url, $indentifier = 2, $Occassion = NULL)
  {
    $url =  $this->env('mpesa/transactionstatus/v1/query');

    //Fill in the request parameters with valid values
    $curl_post_data = array(
      'Initiator' => $this->initiator_name,
      'SecurityCredential' => $this->security_credential(),
      'CommandID' => 'TransactionStatusQuery',
      'TransactionID' => $TransactionID,
      'PartyA' => $this->store_number,
      'IdentifierType' => $indentifier,
      'ResultURL' => $this->result_url . $result_url,
      'QueueTimeOutURL' => $this->timeout_url . $timeout_url,
      'Remarks' => $Remarks,
      'Occasion' => $Occassion
    );

    $this->query($url, $curl_post_data);
  }

  /**
   * QR Code Generate
   * Format of QR output:"1": Image Format."2": QR String Format "3": Binary Data Format."4": PDF Format.
   * Transaction Type. The supported types are: BG: Pay Merchant (Buy Goods).WA: Withdraw Cash at Agent Till.PB: Paybill or Business number.SM: Send Money(Mobile number).SB: Sent to Business. Business number CPI in MSISDN format.
   * 	Credit Party Identifier. Can be a Mobile Number, Business Number, Agent Till, Paybill or Business number, Merchant Buy Goods.
   * 
   * @param QRFormat Format of QR output "1": Image Format. "2": QR String Format "3": Binary Data Format. "4": PDF Format.
   * @param TrxCodeBG BG Pay Merchant (Buy Goods).WA: Withdraw Cash at Agent Till. PB: Paybill or Business number.SM: Send Money(Mobile number).SB: Sent to Business. Business number CPI in MSISDN format.
   * @param Amount  
   * @return Qrformart
   */
  public function generate_qrcode($amount, $reference, $MerchantName = 'SERVICE', $qrformat = 1, $trxcode = 'BG')
  {
    $url = $this->env('mpesa/qrcode/v1/generate');

    //Fill in the request parameters 
    $curl_post_data = array(
      "QRVersion" => "01",
      "QRFormat" => $qrformat,
      "QRType" => "D",
      "MerchantName" => $MerchantName,
      "RefNo" => $reference,
      "Amount" => $amount,
      "TrxCode" => $trxcode,
      "CPI" => $this->store_number
    );

    $this->query($url, $curl_post_data);
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
      } elseif ($this->env === "production") {
        return $this->live_endpoint . $request_url;
      }
    }
  }

  /** Password for encrypting the request.
   *  This is generated by base64 encoding Bussiness shorgcode passkey and timestamp
   *
   * @access  private
   * @return  string
   */
  public function password()
  {
    $Merchant_id =  trim($this->store_number);
    $passkey     =  trim($this->pass_key);
    $password    =  base64_encode($Merchant_id . $passkey . $this->timestamp());

    return $password;
  }

  /**
   * timestamp for the time of transaction
   */
  public function timestamp()
  {
    return date('YmdHis');
  }

  /**
   * Mpesa authenticate a transaction by decrypting the security credential 
   * Security credentials are generated by encrypting the Base64 encoded string of the M-Pesa short code 
   * and password, which is encrypted using M-Pesa public key and validates the transaction on M-Pesa Core system.
   * 
   * @access  private
   * @return  String
   */
  public function security_credential()
  {
    $publicKey =  $this->env === "sandbox" ? file_get_contents(__DIR__ . '/SandboxCertificate.cer') : file_get_contents(__DIR__ . '/ProductionCertificate.cer');
    openssl_public_encrypt($this->initiator_pass, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
    return is_null($this->security_credential) ? base64_encode($encrypted) : $this->security_credential;
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
