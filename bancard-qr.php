<?php

class BancardQR
{
    private $private_key;
    private $public_key;
    private $service_url;
    private $commerce_code;
    private $commerce_branch;

    public function __construct($production = true)
    {
        $this->private_key = $production ? '' : '';
        $this->public_key =  $production ? '' : '';
        $this->service_url = $production ? 'https://comercios.bancard.com.py/external-commerce/api/0.1/' : 'https://desa.infonet.com.py:8035/external-commerce/api/0.1/';
        $this->commerce_code = $production ? '' : '';
        $this->commerce_branch = $production ? '' : '';
    }

    public function generateQR($transaction_id, $amount, $pedido_id)
    {
        $url = "commerces/{$this->commerce_code}/branches/{$this->commerce_branch}/selling/generate-qr-express";
        // $descrip
        $params = array(
            "amount"      => number_format($amount, 2, '.', ''),
            "description" => "Pedido #{$pedido_id}" //tx
        );

        $result = $this->request(
            $url,
            $params
        );

        $result = @json_decode($result);

        return $result;
    }

    private function generateToken()
    {
        $token = base64_encode("apps/{$this->public_key}:{$this->private_key}");
        return "Basic {$token}";
    }

    private function request($action, $data, $method = "POST")
    {
        $data = @json_encode($data);
        $headers = array(
            "Authorization: {$this->generateToken()}",
            'Content-Type: application/json'
        );

        $session = curl_init($this->service_url . $action);

        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        if ($method !== 'POST')
            curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
        else
            curl_setopt($session, CURLOPT_POST, true);


        curl_setopt($session, CURLOPT_POSTFIELDS, $data);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($session);
        $error = curl_error($session);
        curl_close($session);

        if ($response === false) {
            throw new Exception("No se pudo enviar la petición {$action}. {$error}");
        } else {
            return $response;
        }
    }

    /*
	* Lee la respuesta enviada por bancard a la url especificada en el panel de comercios de bancard
	*/
    public function get_response()
    {
        $response = file_get_contents("php://input");
        return $response;
    }
}
