<?php
	include_once "_setting.php";
	
		
	function MysqliConn()
	{
		global $conn;
		//usando le variabili globali creo la connessione ( verifico prima che la connessione non sià già stata creata)
		if(!is_DBconnected())
		{
			global $MySQL_Host,$MySQL_Username,$MySQL_Password,$MySQL_DB_Name;
			//Creo la connessione
			$conn = new mysqli($MySQL_Host, $MySQL_Username, $MySQL_Password,$MySQL_DB_Name);
			
			if (mysqli_connect_errno()){
				printf("Connect failed: %s\n", mysqli_connect_error());
				return null;
			}
		}
		
		return $conn;
	}


	function is_DBconnected()
	{
		//controlla la conn è già stata creata
		global $conn;
		if($conn)
			return true;
		else
			return false;
	}


	function GetAllAdditionalDataColumnsRealName()
	{
		global $additionalData;
		$arr=array();
		foreach($additionalData as $k=>$v)
			$arr[]=$k;
			
		return $arr;
	}
	
	function SessionStart()
	{
		if(!isset($_SESSION))
			session_start();
	}
?>