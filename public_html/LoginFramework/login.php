<?php

include_once "_setting.php";

include_once "_function.php";





class LoginManager

{



	public $UseSession;

	private $conn;

	

	public function __construct($UseSession=true) {	

		$this->conn=MysqliConn();

		$this->UseSession=$UseSession;

	}

	

	

	function loginUnsafe($user,$pass)	//la password deve essere passata in chiaro

	{

		return $this->login($user,MD5($pass));

	}

	

	function login($user,$passMD5)	//la password deve essere passata già in MD5

	{

		global $user_tablename,$additionalData_tablename;

		

		$user=$this->conn->real_escape_string($user);

		$passMD5=$this->conn->real_escape_string($passMD5);

		

		$q="";

		

		

		$AddittionalDataColumns=GetAllAdditionalDataColumnsRealName();

		if(count($AddittionalDataColumns)==0)

		{

			$q="SELECT $user_tablename.ID,username,password from $user_tablename where username='$user' and password='$passMD5'";

		}

		else

		{

			$AddittionalDataColumns=implode (",",GetAllAdditionalDataColumnsRealName());

			$q="SELECT $user_tablename.ID,username,password,$AddittionalDataColumns from $user_tablename inner join $additionalData_tablename ON ($user_tablename.ID=$additionalData_tablename.id_user) where username='$user' and password='$passMD5'";

		}

		//print_r($q);

		$r=$this->conn->query($q);

		if($r)

		{

			if( $r->num_rows==0)

				return false;

			else if( $r->num_rows>1)	//SQL injection o errone nella query

				return null;	

			else

			{

				//creo un array associativo con i dati dell'utente

				$UserData=array();

				if($row=$r->fetch_assoc())

				{

					foreach($row as $k=>$v)

						$UserData[$k]=$v;

						

					if($this->UseSession)

						$this->setSessionUserData($UserData);

					

					return $UserData;

				}

				else

				{

					//non riesco a recuperare i dati dalla query

					return null;

				}

				

			}

		}

		else

		{

			//printf("Error ".$mysqli->errno.": %s\n", $mysqli->error);

			return null;

		}	

		

	}

	

	function logout()

	{

		if($this->UseSession)

		{
			global $session_UserData_name;
			SessionStart();

			unset($_SESSION[$session_UserData_name]);

		}

	}

	

	

	function isLogged()

	{

		if($this->UseSession)

		{

			$UserData=$this->getSessionUserData();

				return $UserData!=null;

		}



	}

	

	

	

	

	function registerUnsafe($user,$pass)

	{

		return $this->register($user,MD5($pass));

	}

	function register($user,$passMD5)

	{

		//TODO: registrazione dell'utente e settaggio delle additional data

		

		global $MySQL_DB_Name,$additionalData_tablename,$user_tablename;

		$user=$this->conn->real_escape_string($user);

		$passMD5=$this->conn->real_escape_string($passMD5);

		

		

		$q = "INSERT INTO $user_tablename (username, password) VALUES ('$user', '$passMD5')";

		if ($this->conn->query($q) === TRUE) {

			$last_id = $this->conn->insert_id;	

			$q="INSERT INTO $additionalData_tablename (id_user)

				SELECT * FROM (SELECT '$last_id') AS tmp

				WHERE EXISTS (

				   SELECT count(*)FROM information_schema.tables WHERE table_schema ='$MySQL_DB_Name' AND table_name ='$additionalData_tablename'

				) LIMIT 1;

				";

			//echo $q;

			if ($this->conn->query($q) === TRUE) {

				return true;	

			} else {

				return "Error: " . $q . "<br>" . $this->conn->error;

				

			}

			return true;	

		} else {

			return "Error: " . $q . "<br>" . $this->conn->error;

			

		}

				

	}

	

	function SetAdditionalData($id_user,$columnName,$value)
	{
		global $additionalData_tablename;
		$q="UPDATE $additionalData_tablename SET $columnName='$value' where id_user=$id_user";
		//print_r($q);
		if ($this->conn->query($q) === TRUE) {
			if($this->UseSession)
			{
				$UserData=$this->getSessionUserData();
				$UserData[$columnName]=$value;
				$this->setSessionUserData($UserData);
			}
			return true;	
		} else {
			return "Error: " . $q . "<br>" . $this->conn->error;
		}
	}



	

	function SetAdditionalDataMultiColumn($id_user,$DataArray) //$DataArray[columnName]=value

	{

		if(count($DataArray)>0)

		{

			global $additionalData_tablename;

			

			$UpdateStringSet=array();

			foreach($DataArray as $columnName=>$value)

			{

				$UpdateStringSet[]="$columnName='$value'";			

			}

			$UpdateStringSet=implode(" , ",$UpdateStringSet);

			

			

			$q="UPDATE $additionalData_tablename SET $UpdateStringSet where id_user=$id_user";

			if ($this->conn->query($q) === TRUE) {

				if($this->UseSession)

				{

					$UserData=$this->getSessionUserData();

					foreach($DataArray as $columnName=>$value)

					{

						$UserData[$columnName]=$value;		

					}

					$this->setSessionUserData($UserData);

				}

				return true;	

			} else {

				return "Error: " . $q . "<br>" . $this->conn->error;

			}

		}

		

	}

	

	

	

	function RefreshSessionUserData()

	{

		if($this->UseSession)

		{

			$UserData=$this->getSessionUserData();

			$this->login($UserData["username"],$UserData["password"]);

		}

	}

	function getSessionUserData()

	{
		global $session_UserData_name;
		SessionStart();

		if(isset($_SESSION[$session_UserData_name]))

			return $_SESSION[$session_UserData_name];

		return null;

	}
	
	function getID()

	{
		$ss=$this->getSessionUserData();
		if($ss==null)
			return null;

		else if( !isset($ss["ID"]))
			return null;
		else 
			return $ss["ID"];
	}

	function setSessionUserData($UserData)

	{
		global $session_UserData_name;
		SessionStart();

		$_SESSION[$session_UserData_name]=$UserData;

	}


	function GetUserSelectWhere($Select,$Where)
	{
		global $user_tablename;
		global $additionalData_tablename;
		if( $Select == null || $Select == "")
			$Select="*";

			
		$q="";
		$AddittionalDataColumns=GetAllAdditionalDataColumnsRealName();
		if(count($AddittionalDataColumns)==0)
		{
			$q="SELECT $Select from $user_tablename";	
		}
		else
		{
			$AddittionalDataColumns=implode (",",GetAllAdditionalDataColumnsRealName());
			$q="SELECT $Select from $user_tablename inner join $additionalData_tablename ON ($user_tablename.ID=$additionalData_tablename.id_user)";
		}

		if( $Where != null && $Where != "")
				$q.=" where $Where";

		
		$r=$this->conn->query($q);
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
			return false;	
	}
	
	function GetAllUsers()
	{

		global $user_tablename;
		global $additionalData_tablename;
		
		$q="";
		$AddittionalDataColumns=GetAllAdditionalDataColumnsRealName();
		if(count($AddittionalDataColumns)==0)
		{
			$q="SELECT $user_tablename.ID,username,password from $user_tablename";
		}

		else
		{
			$AddittionalDataColumns=implode (",",GetAllAdditionalDataColumnsRealName());
			$q="SELECT $user_tablename.ID,username,password,$AddittionalDataColumns from $user_tablename inner join $additionalData_tablename ON ($user_tablename.ID=$additionalData_tablename.id_user)";
		}
		$r=$this->conn->query($q);
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
			return false;	
	}

	function GetUser($ID)
	{
		global $user_tablename;
		global $additionalData_tablename;
		
		$q="";
		$AddittionalDataColumns=GetAllAdditionalDataColumnsRealName();
		if(count($AddittionalDataColumns)==0)
		{
			$q="SELECT $user_tablename.ID,username,password from $user_tablename where $user_tablename.ID=$ID";
		}

		else
		{
			$AddittionalDataColumns=implode (",",GetAllAdditionalDataColumnsRealName());
			$q="SELECT $user_tablename.ID,username,password,$AddittionalDataColumns from $user_tablename inner join $additionalData_tablename ON ($user_tablename.ID=$additionalData_tablename.id_user) where $user_tablename.ID=$ID";
			
		}
		$r=$this->conn->query($q);
		if($r)
		{
			if($row=$r->fetch_assoc())
			{
				return $row;
			}
		}
		else
			return false;	
	}

}



?>