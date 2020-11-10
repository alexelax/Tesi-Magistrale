<?php
include_once "login.php";

$lm=new LoginManager();
if( $lm-> isLogged())
{
	$lm->logout();
	echo "logout effettuato";
}
else
{
	$r=$lm->registerUnsafe("pippo","pluto");
	/*if($r!==true)
		echo $r;*/

	echo "<br><br><br>";

	$UserData=$lm->loginUnsafe("pippo","pluto");
	echo "autenticato";


	$add=array("nome"=>"NOME","cognome"=>"COGNOME");
	$lm->SetAdditionalDataMultiColumn($UserData["ID"],$add);
	
	
	

}

?>