<?php

class JasperServerAPI
{

    private $username;
    private $password;
    private $base_url;

    public function __construct($username, $password, $base_url)
    {

        $this->username = $username;
        $this->password = $password;
        $this->base_url = $base_url;

        $this->login();

    }

    public function makeRequest($url, $method, $params = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->base_url . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => "{$this->username}:{$this->password}",
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function login()
    {

        $params = array(
            'j_username' => $this->username,
            'j_password' => $this->password,
        );

        return $this->makeRequest('/jasperserver/rest_v2/login$j_username&j_password', 'POST', json_encode($params));
    }

    public function getReport($report_path, $params = null)
    {
        $url = '/jasperserver/rest_v2/reports' . $report_path . '/output';

        if ($params != null) {
            $url .= '?' . http_build_query($params);
        }

        return $this->makeRequest($url, 'GET');
    }

    public function logout()
    {
        return $this->makeRequest('/jasperserver/rest_v2/logout', 'POST');
    }

}