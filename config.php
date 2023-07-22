<?php
    $json = file_get_contents('config.json');
    $config = json_decode($json);
    $client_id = $config->client_id;
    $client_secret = $config->clientSecret;
    $access_token = $config->access_token;
    $config_path = './config.json';
    
    function updateJsonFile($filePath, $fieldToUpdate, $newValue) {
    
        $jsonString = file_get_contents($filePath);
        $data = json_decode($jsonString, true);
    
        $data[$fieldToUpdate] = $newValue;
    
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);
    
        file_put_contents($filePath, $newJsonString);
    }
    
    function getTwitchAccessToken() {
        global $client_id, $client_secret;
    
        $postFields = http_build_query(array(
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "grant_type" => "client_credentials"
        ));
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://id.twitch.tv/oauth2/token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
    
        if(curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            $responseArray = json_decode($response, true);
            $accessToken = $responseArray["access_token"];
            global $access_token;
            $access_token = $accessToken;
            return $accessToken;
        }
    
        curl_close($ch);
    }
    
    function request($url) {
        global $access_token, $client_id, $config_path;
    
        $response = "";
        $err = "";
        $resultJSON = array();
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $access_token",
                "Client-ID: $client_id"
                )
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $resultJSON = json_decode($response, true);
            curl_close($curl);
    
            if (array_key_exists("error", $resultJSON)) {
                if ($resultJSON["message"] == "Invalid OAuth token") {
                    updateJsonFile($config_path, "access_token", getTwitchAccessToken());
                    return request($url);
                }
            }
            return $resultJSON;
    }
    
?>