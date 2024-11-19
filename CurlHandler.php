<?php

class CurlHandler
{
    private $ch; // cURL handle
    private $response;
    private $error;

    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        
        // curl_setopt($this->ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
        // curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);

    }

    public function setUrl(string $url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
    }

    public function setHeaders(array $headers)
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
    }

    public function setMethod(string $method)
    {
        $method = strtoupper($method);
        switch ($method) {
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default: // Default is GET
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        }
    }

    public function setBody(array $data)
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    public function execute()
    {
        $this->response = curl_exec($this->ch);
        $this->error = curl_errno($this->ch) ? curl_error($this->ch) : null;
        return $this->response;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getResponseDecoded()
    {
        return json_decode($this->response, true);
    }

    public function close()
    {
        curl_close($this->ch);
    }

    public function fetch($fetchie)
    {
        // string $url, array[] $headers, string $method, array(=>) $body,
        extract($fetchie);
        $this->setUrl($url);
        $this->setHeaders($header);
        $this->setMethod($method);
        if(!empty($body))
            $this->setBody($body);

        // $response = $this->execute();
        $this->execute();
        if ($error = $this->getError()) {
            echo "cURL Error: $error";
            return false;
        } else {
            // Decode and use the response
            $data = $this->getResponseDecoded();
        }
        // Close the connection
        $this->close();
        return $data;
    }
}

/* ERRORS

- https://stackoverflow.com/questions/29822686/curl-error-60-ssl-certificate-unable-to-get-local-issuer-certificate/34883260#34883260

*/

/* Example
<?php
require_once 'CurlHandler.php';

// Instantiate the CurlHandler class
$curl = new CurlHandler();

// Set API endpoint and headers
$curl->setUrl("https://api.example.com/data");
$curl->setHeaders([
    'Content-Type: application/json',
    'Authorization: Bearer YOUR_API_TOKEN'
]);

// Set HTTP method and data (for POST or PUT)
$curl->setMethod("POST");
$curl->setBody([
    "key1" => "value1",
    "key2" => "value2"
]);

// Execute the request
$response = $curl->execute();

// Check for errors
if ($error = $curl->getError()) {
    echo "cURL Error: $error";
} else {
    // Decode and use the response
    $data = $curl->getResponseDecoded();
    print_r($data);
}

// Close the connection
$curl->close();

*/

