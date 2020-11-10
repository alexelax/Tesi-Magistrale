<?php
require_once "LoginFramework/login.php";
require_once "GoogleAPI.php";
require_once "_dati.php";

date_default_timezone_set("Europe/Rome");
class HospitalBand
{
    private $lm=null; 
    function InitLM()
    {
        if($this->lm==null)
        {   
            $this->lm=new LoginManager();
            if(! $this->lm-> isLogged())
            {
                //TODO: errore???
            }
        }
    }


    private $ga=null; 
    function InitGA()
    {
        if($this->ga==null)
        {   
            global $client_id,$client_secret,$redirect_AUTH_uri,$redirect_EXCHANGE_uri;
            $this->ga=new GoogleAPI($client_id,$client_secret,$redirect_AUTH_uri,$redirect_EXCHANGE_uri);
           
        }
    }

    function GetAllPatients()
    {
        $this->InitLM();
        $Users= $this->lm->GetUserSelectWhere("login_user.ID, Name, Surname, LastUpdate","TypeOfAccount = 2");
       
        if($Users==null || count($Users)==0)
            return null;

        for($i=0;$i<count($Users);$i++)
        {
            $date = new DateTime($Users[$i]["LastUpdate"], new DateTimeZone("UTC"));
            $date->setTimezone(new DateTimeZone("Europe/Rome"));
            $Users[$i]["LastUpdate"]=$date->format('Y-m-d H:i:s');
        }


        return $Users;

    }
    function GetPatient($ID)
    {
        $this->InitLM();
        $User= $this->lm->GetUserSelectWhere("login_user.ID, Name, Surname, LastUpdate","TypeOfAccount = 2 and login_user.ID=$ID");
        if( count($User)==1 )
        {
            $date = new DateTime($User[0]["LastUpdate"], new DateTimeZone("UTC"));
            $date->setTimezone(new DateTimeZone("Europe/Rome"));
            $User[0]["LastUpdate"]=$date->format('Y-m-d H:i:s');
            return $User[0];
        }
           
        return null;
    }
    function GetAllUsers()
    {
        $this->InitLM();
        return $this->lm->GetAllUsers();        
    }
    function GetUser($ID)
    {
        $this->InitLM();
        return $this->lm->GetUser($ID);
    }
    


   /* function GetUserData($UserID,$StartDate,$EndDate)
    {
        $this->UpdateData($UserID,$StartDate);
        
        //controllo se la endDate è fattibile
        //controllo se la startDate è fattibile....
        //recupera i dati dal db...
    }*/

    function UpdateData($UserID)
    {

    
        //Prendo l'ultimo aggiornamento
        $LastUpdate=$this->GetLastUpdate($UserID);
        if( $LastUpdate==null)      //se non è stato mai fatto -> prendo la data di registrazione (consegna orologio)
        {
            $LastUpdate=$this->lm->GetUser($UserID)["RegistrationDate"];
        }

        //Converto la data ( da stringa ) in Secondi ( timestamp ) e gli tolgo ore/min/sec per avere il GIORNO alle 00:00:00
        $LastUpdate=strtotime ($LastUpdate);       
        $LastUpdate = $LastUpdate- $LastUpdate%86400;      //86400 = secondi in un giorno

        $Now=time();                                
        $Now = $Now- $Now%86400; 



        //Calcoliamo la differenza tra Oggi e l'ultimo aggiornamento
        $TimeDifference=$Now-$LastUpdate;           
         
        $PeriodoSelezioneDati=2592000;                                      //  2592000 = numero secondi in un mese ( 30 gg )          
        $DateSpans=array();                                                //FORSE:  Google taglia la finestra dei dati recuperati... scendo a 10 gg --> 864000

        //Se la differenza è 0 = l'ho già fatto oggi!! -> non faccio niente e ritorno
        if($TimeDifference==0)
        {
            $this->SetLastUpdate($UserID);
            return array("success"=>true);    
        }



        //Splitto il tempo tra l'ultimo aggiornamento e oggi in sezioni da 30 giorni ( se si utilizzano più giorni c'è il problema che le GoogleAPI non danno i valori)
        while($LastUpdate<$Now)
        {
            $Inizio=$LastUpdate;
            $Fine=$LastUpdate+$PeriodoSelezioneDati;
            if($Fine>$Now)
                $Fine=$Now;
            $DateSpans[]=array("Inizio"=>$Inizio*1000,"Fine"=>$Fine*1000);
            $LastUpdate+=$PeriodoSelezioneDati;
        }
        //recupero l'accessToken dell'utente ( serve per fare la richiesta a GoogleAPI)
        $AccessToken=$this->lm->GetUser($UserID)["AccessToken"];
        
        //print_r($DateSpans);
        //Per ogni spezzone di tempo, faccio richiesta di Passi,BPM e Sonno
        $i=0;
        $n=0;
        while($i<count($DateSpans))
        {
            $Span=$DateSpans[$i];
            try
            {   //Faccio la richiesta per ottenere i dati                
                $Steps=$this->GetSteps($AccessToken,$Span["Inizio"],$Span["Fine"] );
                $BPMs=$this->GetBPM($AccessToken,$Span["Inizio"],$Span["Fine"] );
                $Sleeps=$this->GetSleep($AccessToken,$Span["Inizio"],$Span["Fine"] );

                 //Salvo i dati nel DB
                $this->SaveSteps($UserID,$Steps);
                $this->SaveBPM($UserID,$BPMs);
                $this->SaveSleep($UserID,$Sleeps);

            }
            catch (Exception $e)
            {            
                // se non a buon fine provo a RefreshToken 
                if($e->getMessage() ==  "Token Scaduto")
                {
                    $this->InitGA();
                    $json= $this->ga->RefreshTokenWithControl($this->lm->GetUser($UserID)["RefreshToken"]);
                    if($json===false)
                    {
                        //ERRORE INCONTROVERTIBILE!!! 
                        //non so xke cazzo google non mi da il refresh token
                        //questa è una features! riprova un altro giorno....
                        return array("success"=>false,"message"=>"Probabilmente token mai sincronizzato");  //Sa il cazzo xke google non aggiorna il token
                        //TODO: prova a rifare la login????
                    }

                    //salvo sul DB l'AccessToken;
                    $AccessToken=$json["access_token"];
                    $this->lm->SetAdditionalData($UserID,"AccessToken",$json["access_token"]);
                    continue;
                    
                }
            }

            



            // insert to user_steps (passi, data) value (startTimeMillis, intVal)
            $i++;
        }



        $this->SetLastUpdate($UserID);
        
        //dico che è andato tutto bene
        return array("success"=>true); 

    }

    /**
     * Aggiorna la data dell'ultimo aggiornamento nel DB
     * se $date==null, aggiorno alla data corrente
     */
    function SetLastUpdate($ID,$date=null)
    {
        //se $date è null, mettere data corrente
        //USO ORARIO Greenwich
        if($date===null)
        {
            $date = new DateTime(date("Y-m-d H:i:s"), new DateTimeZone("Europe/Rome"));
            $date->setTimezone(new DateTimeZone("UTC"));
            $this->lm->SetAdditionalData($ID,"LastUpdate", $date->format('Y-m-d H:i:s'));
        }
        else
        {
            $this->lm->SetAdditionalData($ID,"LastUpdate", date("Y-m-d H:i:s",$date));
        }
      


    }
    
    function GetLastUpdate($ID)
    {
        $this->InitLM();
        //recupero dal DB la data dell'ultimo aggiornamento
        if($ID==null)       //User loggato corrente
        {            
            return $this->lm->getSessionUserData()["LastUpdate"];
        }
        else
        {
            return $this->lm->GetUser($ID)["LastUpdate"];
        }

    }

    function GetLocalLastUpdate($ID)
    {
        $LastUpdate=$this->GetLastUpdate($ID);
        $date = new DateTime($LastUpdate, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone("Europe/Rome"));
        return $date->format('Y-m-d H:i:s'); 
    }
    

    /**
     * Ritorna il numero di passi dalla data passata fino alla data corrente
     */
    function GetSteps($AccessToken,$StartDate,$EndDate=null)
    {
        $this->InitGA();
        //$EndDate==null fino ad oggi

        //uso $StartDate - 1 giorno ( per evitare problemi di sovrapposizione di date... TODO specificare meglio!! )
        $StartDate= $StartDate - 86400000; //un giorno in milli secondi
        
        $richiesta=@'{
            "aggregateBy": [{
              "dataTypeName": "com.google.step_count.delta"
            }],
            "_comment": "86400000 millisecondi corrispondono a 24 ore, inoltre il bucketByTime è il raggruppamento",
            "bucketByTime": { "durationMillis": 86400000}, 
            "startTimeMillis": '.$StartDate.',
            "endTimeMillis": '.$EndDate.'
          }';       
          
          //Recupera i dati dalla GOOGLE API

        $result=$this->ga->RequestData($richiesta,$AccessToken);
        if($result===false)
        {
            
        //solleva un eccezione nel caso di Token "errato" ( scaduto ) 
            throw new Exception('Token Scaduto');   //TODO: bho altri errori???
        }
        
        //li formatta in un array e li ritorna
        $ReturnArray=array();

        foreach ( $result["bucket"] as $v )
        {
            $point=$v["dataset"][0]["point"];
            if(isset($point[0]))
            {
                $dato=$point[0];
                $ReturnArray[]=array("startTime"=>$dato["startTimeNanos"]/1000000000,"endTime"=>$dato["endTimeNanos"]/1000000000,"intVal"=>$dato["value"][0]["intVal"]);
            }
        }

        //rimuovo ultimo elemento se l'endTime è uguale all'EndDate passato alla funzione ( problema descritto su OneNote)
        $NElement=count($ReturnArray);
        if( $NElement!=0)
        {
            $LastElement=$ReturnArray[$NElement-1];
            if($LastElement["endTime"] == $EndDate)
            {
                unset($ReturnArray[$NElement-1]);
            }
              //rimuovo il primo elemento se lo startime è uguale all'Startime passato alla funzione ( problema descritto su OneNote)
            if( $ReturnArray[0]["startTime"] == $StartDate)
            {
                unset($ReturnArray[0]);
            }
        }
          
        return $ReturnArray;
    }

    function SaveSteps($UserID,$Steps)
    {
        $conn=MysqliConn();
        $rows=array();
        //$UserID;

         
        foreach($Steps as $v )
        {
            $StartTime=$this->MillisToDate($v["startTime"]);            //TODO: trovare un metodo più veloce per la modifica del fuso orario 
            $EndTime=$this->MillisToDate($v["endTime"]);                //TOOD: trovare se esiste una funzione MYSQL 

            //$rows[]="($UserID,FROM_UNIXTIME($v[startTime]),FROM_UNIXTIME($v[endTime]),$v[intVal])";   //OLD! senza correzione fuso orario
            $rows[]="($UserID,'$StartTime','$EndTime',$v[intVal])";

        }
        if ( count($rows) > 0)
        {     
            $AllRows= implode(",",$rows);
            $q= "insert into user_steps_details value $AllRows ON DUPLICATE KEY UPDATE Count=Count";
            //echo $q;
            $r=$conn->query($q);
            if(!$r)
            {
                printf("Error ".$conn->errno.": %s\n", $conn->error);
                return null;
            }
        }

    }

    /**
     * Ritorna un array contenente i battiti campionati
     */
    function GetBPM($AccessToken,$StartDate,$EndDate=null)
    {
        $this->InitGA();
        //$EndDate==null fino ad oggi
        $StartDate= $StartDate - 86400000; //un giorno in milli secondi
        $richiesta=@'{
            "aggregateBy": [
              {
                "dataTypeName": "com.google.heart_rate.bpm"
              }
            ],
            "startTimeMillis": '.$StartDate.',
            "endTimeMillis": '.$EndDate.'
          }'; 
        
          //Recupera i dati dalla GOOGLE API

        $result=$this->ga->RequestData($richiesta,$AccessToken);
        if($result===false)
        {
            
        //solleva un eccezione nel caso di Token "errato" ( scaduto ) 
            throw new Exception('Token Scaduto');   //TODO: bho altri errori???
        }
        
        //li formatta in un array e li ritorna
        $ReturnArray=array();


        foreach ( $result["bucket"][0]["dataset"][0]["point"] as $point )
        {
            $ReturnArray[]=array("startTime"=>$point["startTimeNanos"]/1000000000,"fpVal"=>$point["value"][0]["fpVal"]);           
        }

        return $ReturnArray;

    }

    function SaveBPM($UserID,$BPMs)
    {
        $conn=MysqliConn();
        $rows=array();
        //$UserID;
        foreach($BPMs as $v )
        {
            $StartTime=$this->MillisToDate($v["startTime"]);         


            //$rows[]="($UserID,FROM_UNIXTIME($v[startTime]),$v[fpVal])";
            $rows[]="($UserID,'$StartTime',$v[fpVal])";

        }
        if ( count($rows) > 0)
        {
            
          
            $AllRows= implode(",",$rows);
            $q= "insert into user_cardio_rate value $AllRows ON DUPLICATE KEY UPDATE Bpm=Bpm";
            //echo $q;
            $r=$conn->query($q);
            if(!$r)
            {
                printf("Error ".$conn->errno.": %s\n", $conn->error);
                return null;
            }
        }
       
    }

    /**
     * Ritorna un array contenente i dati del sonno di ogni sessione nell'intervallo dalla data di partenza fino a NOW
     */
    function GetSleep($AccessToken,$StartDate,$EndDate=null)
    {
        $this->InitGA();
        //$EndDate==null fino ad oggi

        //uso $StartDate - 1 giorno ( per evitare problemi di sovrapposizione di date... TODO specificare meglio!! )
        $StartDate= $StartDate - 86400000; //un giorno in milli secondi

        $richiesta=@'{
            "aggregateBy": [{
                "dataTypeName": "com.google.activity.segment"
            }],
            "startTimeMillis": '.$StartDate.',
            "endTimeMillis": '.$EndDate.'
        }';       
        
        //Recupera i dati dalla GOOGLE API

        $result=$this->ga->RequestData($richiesta,$AccessToken);
        if($result===false)
        {
            
        //solleva un eccezione nel caso di Token "errato" ( scaduto ) 
            throw new Exception('Token Scaduto');   //TODO: bho altri errori???
        }

        //li formatta in un array e li ritorna
        $ReturnArray=array();
        
      
        foreach ( $result["bucket"] as $v )
        {
            $points=$v["dataset"][0]["point"];
            foreach($points as $point)
            {
                $ReturnArray[]=array("startTime"=>$point["startTimeNanos"]/1000000000,"endTime"=>$point["endTimeNanos"]/1000000000,"intVal"=>$point["value"][0]["intVal"]);
            }
        }

        //rimuovo ultimo elemento se l'endTime è uguale all'EndDate passato alla funzione ( problema descritto su OneNote)
        $NElement=count($ReturnArray);
        if( $NElement!=0)
        {
            $LastElement=$ReturnArray[$NElement-1];
            if($LastElement["endTime"] == $EndDate)
            {
                unset($ReturnArray[$NElement-1]);
            }
                //rimuovo il primo elemento se lo startime è uguale all'Startime passato alla funzione ( problema descritto su OneNote)
            if( $ReturnArray[0]["startTime"] == $StartDate)
            {
                unset($ReturnArray[0]);
            }
        }

        return $ReturnArray;
    }
    function SaveSleep($UserID,$Sleeps)
    {
        $conn=MysqliConn();
        $rows=array();
        //$UserID;
        foreach($Sleeps as $v )
        {
            $StartTime=$this->MillisToDate($v["startTime"]);         
            $EndTime=$this->MillisToDate($v["endTime"]);     

            if($v['intVal'] == 72 || $v["intVal"] == 109 || $v["intVal"] == 110 || $v["intVal"] == 111 || $v["intVal"] == 112)  //i numeri sono specifici per le categorie di sonno
            {
                //$rows[]="($UserID,FROM_UNIXTIME($v[startTime]),FROM_UNIXTIME($v[endTime]),$v[intVal])";
                $rows[]="($UserID,'$StartTime','$EndTime',$v[intVal])";
            }    
            
        }
        if ( count($rows) > 0)
        {     
            $AllRows= implode(",",$rows);
            $q= "insert into user_sleep_details value $AllRows ON DUPLICATE KEY UPDATE Type=Type";
            //echo $q;
            $r=$conn->query($q);
            if(!$r)
            {
                printf("Error ".$conn->errno.": %s\n", $conn->error);
                return null;
            }
        }
        
    }



    function MillisToDate($Millis)
    {
        return date("Y-m-d H:i:s",$Millis); //avendo settato la defult time zone ( inizio file ) 


        /*$date = new DateTime(date("Y-m-d H:i:s",$Millis), new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone("Europe/Rome"));
        return $date->format('Y-m-d H:i:s'); */
    }







    function GetDBStepsByID($ID)
    {
        $q=@"SELECT
                DATE(Start) as Data, sum(Count) AS Passi
            FROM
                user_steps_details
            WHERE
                ID_USER = $ID
            GROUP BY
                YEAR (Start),
                MONTH (Start),
                DAY (Start) DESC
            ORDER BY
                Data";

            $conn=MysqliConn();
            $r=$conn->query($q);
            if($r)
            {
                $ret=array();
                while($row=$r->fetch_assoc())
                {
                    $ret[]=$row;
                }
                return $ret;
            }
            else
                return null;
    }

    function GetDBSleepsByID($ID)
    {
        $q=@"SELECT
                    Start as Data, Type as TYPE
            FROM
                    user_sleep_details
            WHERE
                    ID_USER = $ID
            ORDER BY
                    Data";

            $conn=MysqliConn();
            $r=$conn->query($q);
            if($r)
            {
                $ret=array();
                while($row=$r->fetch_assoc())
                {
                    $ret[]=$row;
                }
                return $ret;
            }
            else
                return null;
    }

    function GetDBBPMyID($ID)
    {
        $q=@"SELECT
                    Start as Data, Bpm as BPM
            FROM
                    user_cardio_rate
            WHERE
                    ID_USER = $ID
            ORDER BY
                    Data";

            $conn=MysqliConn();
            $r=$conn->query($q);
            if($r)
            {
                $ret=array();
                while($row=$r->fetch_assoc())
                {
                    $ret[]=$row;
                }
                return $ret;
            }
            else
                return null;
    }
}

?>