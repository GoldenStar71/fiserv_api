<?php
   	// Takes raw data from the request
	$json = file_get_contents('php://input');

	// Converts it into a PHP object
	$data = json_decode($json);
	$cardNumber = $data->cardNumber;
	$expireMonth = $data->expireMonth;
	$expireYear = $data->expireYear;
	$code =  $data->code;
	$total = $data->amount;
	$email = $data->email;
	$phoneNumber = $data->phoneNumber;
	$currency =  $data->currency;

    $curl = curl_init();
    // or use https://httpbin.org/ for testing purposes
    curl_setopt($curl, CURLOPT_URL, "https://test.ipg-online.com/ipgapi/services");
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $postData = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header/>
    <SOAP-ENV:Body>
        <ns4:IPGApiOrderRequest
            xmlns:ns4="http://ipg-online.com/ipgapi/schemas/ipgapi"
            xmlns:ns2="http://ipg-online.com/ipgapi/schemas/v1"
            xmlns:ns3="http://ipg-online.com/ipgapi/schemas/a1">
            <ns2:Transaction>
                <ns2:CreditCardTxType>
                    <ns2:StoreId>811733854027</ns2:StoreId>
                    <ns2:Type>sale</ns2:Type>
                </ns2:CreditCardTxType>
                <ns2:CreditCardData>
                    <ns2:CardNumber>' . $cardNumber . '</ns2:CardNumber>
                    <ns2:ExpMonth>' . $expireMonth . '</ns2:ExpMonth>
                    <ns2:ExpYear>' . $expireYear . '</ns2:ExpYear>
                    <ns2:CardCodeValue>' . $code . '</ns2:CardCodeValue>
                </ns2:CreditCardData>
                <ns2:Payment>
                    <ns2:ChargeTotal>' . $total . '</ns2:ChargeTotal>
                    <ns2:Currency>' . $currency . '</ns2:Currency>
                </ns2:Payment>
            </ns2:Transaction>
        </ns4:IPGApiOrderRequest>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
    curl_setopt($curl, CURLOPT_POST, true);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, ($postData));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData );
    //// Set headers to send JSON to target and expect JSON as answer
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:text/xml'));
    // As said above, the target script needs to read `php://input`, not `$_POST`!
    //setting the authorization method to BASIC:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //supplying your credentials:
    curl_setopt($curl, CURLOPT_USERPWD, "WS811733854027._.2:y/4%F6WtWB");
    //telling cURL to verify the server certificate:
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    // setting the path where cURL can find the certificate to verify the
    // received server certificate against:
    curl_setopt($curl, CURLOPT_CAINFO, "WS811733854027._.2.pem");
    // setting the path where cURL can find the client certificate:
    curl_setopt($curl, CURLOPT_SSLCERT, "WS811733854027._.2.pem");
    // setting the path where cURL can find the client certificateÃ¢â‚¬â„¢s
    // private key:
    curl_setopt($curl, CURLOPT_SSLKEY, "WS811733854027._.2.key");
    // setting the key password:
    curl_setopt($curl, CURLOPT_SSLKEYPASSWD, "V24?KQ;tEA");
    //curl_setopt($curl, CURLOPT_VERBOSE, true);
	
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_HTTP200ALIASES, (array)400);
	
    $output = curl_exec($curl);

    $xml = simplexml_load_string($output);
    $xml->registerXPathNamespace('ipgapi', 'http://ipg-online.com/ipgapi/schemas/ipgapi');
    $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');  
    $string = $xml->xpath('//ipgapi:TransactionResult');  
    $array = json_decode(json_encode((array)$string), TRUE);
    $data = array(
	'amount' => $total,
	'currency' => $currency,
	'phoneNumber' => $phoneNumber,
	'email' => $email
    ); 
    $response = array(
          'status' =>$array[0][0],
          'data' => $data,
        );
      echo json_encode($response);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = '';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = '';                     //SMTP username
    $mail->Password   = '';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('support@api.epic.dm', 'Mailer');
    $mail->addAddress('info@epic.dm');     //Add a recipient
    //$mail->addAddress('apollon71star@gmail.com');               //Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Payment info for Api transaction';
    $mail->Body    = 'payment status: ' . $array[0][0] . "<br>";
    $mail->Body    .= 'amount: ' . $total . "<br>";
    $mail->Body    .= 'email: ' . $email . "<br>";
    $mail->Body    .= 'description: ' . "" . "<br>";
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    //echo 'Message has been sent';
} catch (Exception $e) {
    //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
