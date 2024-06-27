  <?php

header("Content-Type: application/json");

//0781917066  justin   
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$interswicth = new Interswitch();

if (true) {
    $interswicth->HTTP_METHOD = "GET";
    $transactionId = "ITH1718973238165";
    echo $interswicth->transactionInquiry($transactionId);
}


class Interswitch {
    const CLIENT_ID = "";
    const CLIENT_SECRET = "";
    const TERMINAL_ID = "XXXXX";
    const BANK_CBN_CODE = "100";
    const REQUEST_REFERENCE_PREFIX = "AUT";
    const QUICKTELLER_BASE_URL = "XX/";
    const SVA_BASE_URL = "XX";
    const PAYMENT_NOTIFICATION_URL = "sendAdviceRequest";
    const INQUIRY_URL = "transactions/";
    const REALTIME_TRANSACTION_STATUS_URL = "transactions/";
    const PAYMENT_ADVISE_URL = "sendAdviceRequest";
    const PAYMENT_CODE = "151492";
    const PAYMENT_CODE2 = "151492";
    const BILLER_CATEGORIES = "categorys";
    const BILLERS = "billers";
    const VALIDATECUSTOMER = "validateCustomer";
    const PAYMENT = "validateCustomer";
    const PAYMENTITEMS = "paymentitems";
    public $HTTP_METHOD = "GET";
    const SIGNATURE_METHOD = "sha256";
    const CUSTOMER_ID = 25679537749;

    private function getAuth ($resourceUrl = "", $additionalParameters = "")
    {
        return InterswitchAuth::generateInterswitchAuth($this->HTTP_METHOD, $resourceUrl, self::CLIENT_ID, self::CLIENT_SECRET,
            $additionalParameters, self::SIGNATURE_METHOD, self::TERMINAL_ID);
    }

    public function transactionInquiry ($requestReference)
    {
        $inquiryUrl = self::SVA_BASE_URL . self::INQUIRY_URL . $requestReference;
        $additionalParameters = "";
        $headers = $this->getAuth($inquiryUrl, $additionalParameters);
        $data = [];
        $response =  postHTTP($inquiryUrl, $headers, $data, $this->HTTP_METHOD);
        return $response;
    }



}

function postHTTP ($url = '', $headers = [], $data = [], $httpMethod = "GET")
{
    // echo $url; die;
    $data = json_encode($data);
    $headerToSend = ["Content-Type:application/json"];

    foreach ($headers as $key => $value) {
        $headerToSend[] = "$key:$value";
    }

    echo "\n";
    print_r($data);
    echo "\n";
    print_r($url);
    echo "\n";

    $curl = curl_init();


    $curlArray = array(
            CURLOPT_URL => $url,
            CURLE_TOO_MANY_REDIRECTS => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_CUSTOMREQUEST => "$httpMethod",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headerToSend,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

    curl_setopt_array($curl, $curlArray);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}


class InterswitchAuth {
    const NONCE = 'Nonce'; 
    const SIGNATURE_METHOD = 'SignatureMethod';
    const TIMESTAMP = 'Timestamp';
    const SIGNATURE = 'Signature';
    const AUTHORIZATION = 'Authorization';
    const AUTHORIZATION_REALM = "InterswitchAuth";
    const TERMINAL_ID = "TerminalId";
    const ISO_8859_1 = "ISO-8859-1";

    public static function generateInterswitchAuth ($httpMethod,$resourceUrl,$clientId,$clientSecretKey,
        $additionalParameters,$signatureMethod,$terminalId)
    {
        $timestamp = InterswitchAuth::generateTimestamp();
        $nonce = InterswitchAuth::generateNonce();
        $clientIdBase64 = base64_encode($clientId);
        $authorization = InterswitchAuth::AUTHORIZATION_REALM ." " . $clientIdBase64;


        $signature = InterswitchAuth::generateSignature($clientId,$clientSecretKey,
            $resourceUrl,$httpMethod,$timestamp,$nonce,$additionalParameters);


            
        $interswitchAuth = [
            InterswitchAuth::AUTHORIZATION => $authorization,
            InterswitchAuth::TIMESTAMP => $timestamp,
            InterswitchAuth::NONCE => $nonce,
            InterswitchAuth::SIGNATURE => $signature,
            InterswitchAuth::SIGNATURE_METHOD => $signatureMethod,
            InterswitchAuth::TERMINAL_ID => $terminalId
        ];
        return $interswitchAuth;
    }

    static function generateSignature ($clientId, $clientSecretKey, $resourceUrl, $httpMethod, $timestamp,
        $nonce, $transactionParams)
    {
        $encodedUrl = urlencode($resourceUrl);
        $signatureCipher = $httpMethod . '&' . $encodedUrl . '&' . $timestamp . '&' . $nonce . '&' .
            $clientId . '&' . $clientSecretKey;

        if (!empty($transactionParams) || $transactionParams != "") {
            $signatureCipher = $signatureCipher . '&'.$transactionParams;
        }

        // echo "\n";
        // echo "\n";
        // echo $signatureCipher;
        // echo "\n";
        // echo "\n";
        
        $signature = base64_encode(hash("sha256",$signatureCipher, true));
        return $signature;
    }
    

    static function generateNonce ()
    {
        return sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535),
        mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535),
        mt_rand(0, 65535), mt_rand(0, 65535));
    }
    
    static function generateTimestamp ()
    {
        // return time();
        $date = new \DateTime(null, new \DateTimeZone("Africa/Lagos"));
        return $date->getTimestamp();
    }
}
