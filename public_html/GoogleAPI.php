<?php

class GoogleAPI
{
    public $client_id;
    public $client_secret;
    public $redirect_AUTH_uri;
    public $redirect_EXCHANGE_uri;

    function __construct($client_id,$client_secret,$redirect_AUTH_uri,$redirect_EXCHANGE_uri)
    {
        $this->client_id=$client_id;
        $this->client_secret=$client_secret;
        $this->redirect_AUTH_uri=$redirect_AUTH_uri;
        $this->redirect_EXCHANGE_uri=$redirect_EXCHANGE_uri;
    }


    /**
     * Richiede l'autenticazione all'applicazione
     * @param array Scopes Array di scope
     */
    function Auth($Scopes)
    {
        $ScopesString=implode(" ",$Scopes);
        $var="https://accounts.google.com/o/oauth2/v2/auth?redirect_uri=".urlencode($this->redirect_AUTH_uri)."&prompt=consent&response_type=code&client_id=$this->client_id&scope=".urlencode($ScopesString)."&access_type=offline";
        header("location: $var");
    }

    /**
     * Permette di ottenere l'Access Token dall'Autorization Code 
     * @param string code Autorization Code
     */
    function ExchangeAccessToken($AuthCode)
    {
        //$code= "4/xAExXoMFyAykvI-j7XFUyI6NLzJFUQTWXTRyZC6Mo39U3nBGD7Utb8CrdrMvoLTtJu8sOLWO5rh77pQUYkaCz18";

        $url="https://oauth2.googleapis.com/token";
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, 1);
    

        $data="code=".urlencode($AuthCode)."&redirect_uri=".urlencode($this->redirect_EXCHANGE_uri)."&client_id=$this->client_id&client_secret=$this->client_secret&scope=&grant_type=authorization_code";
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    
        try{
            $json=curl_exec($handle);
            $json_arr = json_decode($json, true);
            
            if(isset($json_arr['error']))
            {
                //TODO: implementare un custom Error? ritornare altro???
                return false;
            }         
        }
        catch(Exception $e)
        {
            //TODO: implementare un custom Error? ritornare altro???
            return false;
        }
        
        return $json_arr;  
    }

    function RefreshAccessToken($RefreshToken)
    { 
        $url="https://oauth2.googleapis.com/token";
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, 1);
    
    
        $data="refresh_token=".urlencode($RefreshToken)."&client_id=$this->client_id&client_secret=$this->client_secret&scope=&grant_type=refresh_token";
        

        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    
        
       
        try{
            $json=curl_exec($handle);
            $json_arr = json_decode($json, true);
            
            if(isset($json_arr['error']))
            {
                //TODO: implementare un custom Error? ritornare altro???
                return false;
            }             
        }
        catch(Exception $e)
        {
            //TODO: implementare un custom Error? ritornare altro???
            return false;
        }

           
        return $json_arr;  
    }


    function RefreshTokenWithControl($RefreshToken,$NumberOfIteration=3)
    {
        for($i=0;$i<$NumberOfIteration;$i++)
        {
            //echo "<br>Test $i: <br>";
            $json=$this->RefreshAccessToken($RefreshToken);
            if( $json!==false &&  isset($json["access_token"]))
            {
                if($this->TestToken($json["access_token"]))
                {
                    return $json;
                }
            }
            continue;
        }
        return false;

    }
    function TestToken($AccessToken)
    {
        $url="https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate";
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$AccessToken));
        
        $RequestString='{
            "aggregateBy": [{
              "dataTypeName": "com.google.step_count.delta"
            }],
            "bucketByTime": { "durationMillis": 86400000}, 
            "startTimeMillis": 1586530800000,
            "endTimeMillis": 1586534400000
          }';

        /*$RequestString=' {
            "aggregateBy": [{
                "dataTypeName": "com.google.step_count.delta",
            }],
            "bucketByTime": { "durationMillis": 86400000 },
            "startTimeMillis": 1586530800000,
            "endTimeMillis": 1586534400000
            }';
    */


        curl_setopt($handle, CURLOPT_POSTFIELDS, $RequestString);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);



        
        try{
            $json=curl_exec($handle);
            //print_r( $json);
            $json_arr = json_decode($json, true);
            
            if(isset($json_arr['error']))
            {
                return false;
            }             
        }
        catch(Exception $e)
        {
            //TODO: implementare un custom Error? ritornare altro???
            return false;
        }

           
        return true;  

    }


    function RequestData($RequestString,$AccessToken)
    {

        $url="https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate";
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.$AccessToken));
        
        
        /*
        {
        "aggregateBy": [{
            "dataTypeName": "com.google.step_count.delta",
            "dataSourceId": "derived:com.google.step_count.delta:com.google.android.gms:estimated_steps"
        }],
        "bucketByTime": { "durationMillis": 86400000 },
        "startTimeMillis": 1438705622000,
        "endTimeMillis": 1439310422000
        }

        */

        curl_setopt($handle, CURLOPT_POSTFIELDS, $RequestString);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);



      
        try{
            $json=curl_exec($handle);
            //print_r( $json);
            $json_arr = json_decode($json, true);
            
            if(isset($json_arr['error']))
            {
                //TODO: implementare un custom Error? ritornare altro???
                return false;
            }             
        }
        catch(Exception $e)
        {
            //TODO: implementare un custom Error? ritornare altro???
            return false;
        }

           
        return $json_arr;  
    }


}


?>