<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../_dati.php";
require_once "../GoogleAPI.php";
require_once "../HospitalBandAPI.php";
include_once "../LoginFramework/login.php";


$lm=new LoginManager();

if( !$lm-> isLogged())
{
	header("location: ../index.php");
	exit();
}


if(isset($_GET["error"]))	
{
	echo $_GET["error"]."<br><a href='./'>Ri-autenticati</a>";
}
else if (isset($_GET["code"]))	//Fase post_autenticazione -> prendo L'auth code e richiedo l'access token e refresh token
{
	$UserData=$lm->getSessionUserData();
	$lm->SetAdditionalData($UserData["ID"],"AuthCode",$_GET["code"]);
	
	
	$fit=new GoogleAPI($client_id,$client_secret,$redirect_AUTH_uri,$redirect_EXCHANGE_uri);
	$AccessToken = $fit->ExchangeAccessToken($_GET["code"]);
	if($AccessToken!=false)
	{
		//print_r($AccessToken);
		$lm->SetAdditionalData($UserData["ID"],"AccessToken",$AccessToken["access_token"]);
		$lm->SetAdditionalData($UserData["ID"],"RefreshToken",$AccessToken["refresh_token"]);
		//salvo l'access token e il refresh token
		ProceduraCompletata();

	}
	else
	{
		echo "Errore nel recupero dell'AccessToken";
	}
	
}

else	//richiesta nuovo Auth
{
	if(!isset($_GET["RichiediAccesso"]))
	{
		HTML();
		exit();
	}
	else
	{
		$fit=new GoogleAPI($client_id,$client_secret,$redirect_AUTH_uri,$redirect_EXCHANGE_uri);
		$fit->Auth($Scopes);	//fa una redirect
	}
}
	

//TODO: stampo a video anche l'user e password dell'utente appena registrato

function HTML()
{
?>
<head>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" ></script>  	
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet'>
    <style>
    body {
        font-family: 'Lato'; font-size: 20px;
    }
    .text-line {
        background: transparent;
        border: none;
        outline: none;
        border-bottom: 2px solid #4a6bc3;
    }
    .btn-primary {
        background-color: #faad14 !important;
        border-color: #faad14 !important;
        border-width: 2px;
        color: #FFFFFF !important; /* colore scritta bottone */
        box-shadow: none !important; /* alone */
    }
    </style>
</head>

<body style="text-align:center">
	<?php
	include("../_header.php");
	?>
	<br><br><br><br><br>
	
	<form>
        <button class="btn btn-primary btn-lg" name="RichiediAccesso">RICHIEDI ACCESSO A GOOGLE</button>
	</form>
<?php
}


function ProceduraCompletata()
{
?>
<head>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script> 
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet'>
    <style>
    body {
        font-family: 'Lato'; font-size: 20px;
    }
    .text-line {
        background: transparent;
        border: none;
        outline: none;
        border-bottom: 2px solid #4a6bc3;
    }
    </style>
</head>

<body style="text-align:center">
	<?php
	include("../_header.php");
	?>
	<br><br><br><br><br>
	
	<h3>Procedura Completata!<h3>
<?php
}
?>



