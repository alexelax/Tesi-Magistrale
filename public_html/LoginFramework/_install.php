<?php
/*
	File che permette l'installazione del framework per la login
	crea le tabelle nel DB
*/
include_once "_setting.php";
include_once "_function.php";

//Mi connetto al db
$mysqli =  MysqliConn();
if($mysqli==null)
	exit();

//Creo le tabelle standard

$query_creazione_tablella_user=@"
CREATE TABLE ".$user_tablename." (
  ID bigint(20) NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL,
  password char(32) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";



if (!$mysqli->query($query_creazione_tablella_user)) {
	if($mysqli->errno!="1050")	//1050: tabella già esistente
		printf("Error ".$mysqli->errno.": %s\n", $mysqli->error);
}



//Creo la tabella di additionalData 

if(count($additionalData)>0)
{
	$query_creazione_tablella_additionalData=@"
	CREATE TABLE ".$additionalData_tablename." ( 
	  ID bigint(20) NOT NULL AUTO_INCREMENT,
	  id_user bigint(20) NOT NULL, ";
	foreach($additionalData as $nome_campo=>$subarr)
	{  
		$query_creazione_tablella_additionalData.="$nome_campo $subarr[type]".(isset($subarr["mod"])?"($subarr[mod])":"")." ".($subarr["nullable"]?"":"NOT NULL")." ".(isset($subarr["default"])?"DEFAULT '$subarr[default]'":"").", ";
	} 
	$query_creazione_tablella_additionalData.=@"PRIMARY KEY (ID),
	  FOREIGN KEY (id_user) REFERENCES ".$user_tablename."(ID)   ON DELETE CASCADE   ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	
	if (!$mysqli->query($query_creazione_tablella_additionalData)) {
		if($mysqli->errno!="1050")	//1050: tabella già esistente
			printf("Error ".$mysqli->errno."<br><br>Query:$query_creazione_tablella_additionalData<br><br>%s\n", $mysqli->error);
		else
		{
			//la tabella esiste già, devo controllarne i campi e fare l'ALTER TABLE
			$ColonneEsistenti=GetColumns($MySQL_DB_Name,$additionalData_tablename);
			$ColonneEsistenti = array_diff($ColonneEsistenti, ["ID","id_user"]);	//tolgo dal conteggio le colonne obbligatorie
			
			$query_alter_additionalData="";
			foreach($additionalData as $nome_campo=>$subarr)
			{  
				$query_alter_additionalData="";
				if( in_array($nome_campo, $ColonneEsistenti))
				{
					//la modifico
					$query_alter_additionalData="ALTER TABLE ".$additionalData_tablename." MODIFY $nome_campo $subarr[type]".(isset($subarr["mod"])?"($subarr[mod])":"")." ".($subarr["nullable"]?"":"NOT NULL")." ".(isset($subarr["default"])?"DEFAULT '$subarr[default]'":"");
				}
				else
				{
					//la aggiungo
					$query_alter_additionalData="ALTER TABLE ".$additionalData_tablename." ADD $nome_campo $subarr[type]".(isset($subarr["mod"])?"($subarr[mod])":"")." ".($subarr["nullable"]?"":"NOT NULL")." ".(isset($subarr["default"])?"DEFAULT '$subarr[default]'":"");
				}
				$ColonneEsistenti = array_diff($ColonneEsistenti, [$nome_campo]);
				
				if(!$mysqli->query($query_alter_additionalData))
					printf("Error ".$mysqli->errno."<br><br>Query:$query_alter_additionalData<br><br>%s\n", $mysqli->error);
			} 
			
			if(count($ColonneEsistenti)>0)	//se ci sono colonne da cancellare
			{
				//Cancello le colonne che non utilizzo 
				$query_alter_remove_columns="ALTER TABLE ".$additionalData_tablename; 
				$primo=true;
				foreach( $ColonneEsistenti as $k=>$v)
				{
					if($primo)
						$primo=false;
					else
						$query_alter_remove_columns.=" , ";
						
					
					//colonne da rimuovere
					$query_alter_remove_columns.=" DROP COLUMN $v";
				}
				echo $query_alter_remove_columns."<br>";
				if(!$mysqli->query($query_alter_remove_columns))
					printf("Error ".$mysqli->errno."<br><br>Query:$query_alter_remove_columns<br><br>%s\n", $mysqli->error);
			}
			
		}
	}
	
}
else
{
	$q="DROP TABLE IF EXISTS ".$additionalData_tablename;
	if(!$mysqli->query($q))
		printf("Error ".$mysqli->errno.": %s\n", $mysqli->error);
	
}


echo "OK";
//Se la tabella esiste già, vado a fare l'ALTER TABLE in modo da far combaciare i campi del DB con quelli espressi nel file _setting.php nella variabile $additionalData




function CheckColumnExist($DB_name,$TableName,$ColumnName)
{
	global $mysqli;

	$q="SELECT count(*) FROM information_schema.COLUMNS where TABLE_SCHEMA='$DB_name' and TABLE_NAME='$TableName' and COLUMN_NAME='$ColumnName'";
	$r=$mysqli->query($q);
	if($r)
	{
		if($row=$r->fetch_row())
			return $row[0]==1;
	}
	else 
	{
		printf("Error ".$mysqli->errno.": %s\n", $mysqli->error);
		return null;
	}
	
}
 
function GetColumns($DB_name,$TableName)
{
	global $mysqli;

	$q="SELECT COLUMN_NAME FROM information_schema.COLUMNS where TABLE_SCHEMA='$DB_name' and TABLE_NAME='$TableName'";
	$r=$mysqli->query($q);
	if($r)
	{
		$ar=array();
		while($row=$r->fetch_row())
			$ar[]=$row[0];
		return $ar;
	}
	else 
	{
		printf("Error ".$mysqli->errno.": %s\n", $mysqli->error);
		return null;
	}	
}

?>