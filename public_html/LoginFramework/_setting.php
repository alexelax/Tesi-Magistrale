<?php

/*

	File contenente i principali settaggi per la login

*/







//Dati di accesso al DB MySQL



$MySQL_Host="localhost";

$MySQL_Username="id12780973_hospitalband";

$MySQL_Password="HospitalBand";

$MySQL_DB_Name="id12780973_hospitalband";




$prefixTable="login_";

$user_post_tablename="user";

$additionalData_post_tablename="additionalData";

$user_tablename=$prefixTable.$user_post_tablename;

$additionalData_tablename=$prefixTable.$additionalData_post_tablename;





$prefixSession="login_";

$session_UserData_post_name="UserData";

$session_UserData_name=$prefixSession.$session_UserData_post_name;



//Campi aggiuntivi

/*

	Questa variabile serve per andare a definire i campi aggiuntivi alla registrazione di un utente







Esempio



$additionalData=array(

	"nome"=>array("tipo"=>"VARCHAR","mod"=>"32","beaty"=>"Nome","nullable"=>false,"default"=>""),

	"cognome"=>array("tipo"=>"VARCHAR","mod"=>"32","beaty"=>"Cognome","nullable"=>false,"default"=>""),

);



Se nullable è a false, deve esserci obbligatoriamente il campo "default"

*/





$additionalData=array(

	"AuthCode"=>array("type"=>"VARCHAR","mod"=>"512","beaty"=>"AuthCode","nullable"=>true),
	"AccessToken"=>array("type"=>"VARCHAR","mod"=>"512","beaty"=>"AccessToken","nullable"=>true),
	"RefreshToken"=>array("type"=>"VARCHAR","mod"=>"512","beaty"=>"RefreshToken","nullable"=>true),
	"Valid"=>array("type"=>"tinyint","mod"=>"1","beaty"=>"Valid","nullable"=>true),
	"LastUpdate"=>array("type"=>"timestamp","mod"=>"0","beaty"=>"LastUpdate","nullable"=>true,"default"=>"NULL"),
	"RegistrationDate"=>array("type"=>"timestamp","mod"=>"0","beaty"=>"RegistrationDate","nullable"=>false),
	"TypeOfAccount"=>array("type"=>"int","mod"=>"11","beaty"=>"TypeOfAccount","nullable"=>false,"default"=>"2"),
	"Name"=>array("type"=>"VARCHAR","mod"=>"64","beaty"=>"Name","nullable"=>true),
	"Surname"=>array("type"=>"VARCHAR","mod"=>"64","beaty"=>"Surname","nullable"=>true)
);



?>