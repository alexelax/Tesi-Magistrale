<?php

$client_id = '1064331362456-bes7ag9p0rpmiu5bh0knk0264u8pdb6b.apps.googleusercontent.com';
$client_secret = 'ZZseiDwb4Au9jl21wxWtvCL1';
$redirect_AUTH_uri = 'https://tesi2020.000webhostapp.com/RichiestaAuth/index.php';
$redirect_EXCHANGE_uri = 'https://tesi2020.000webhostapp.com/RichiestaAuth/index.php';
$Scopes=["https://www.googleapis.com/auth/fitness.activity.read",
"https://www.googleapis.com/auth/fitness.blood_glucose.read",
"https://www.googleapis.com/auth/fitness.blood_pressure.read",
"https://www.googleapis.com/auth/fitness.body.read",
"https://www.googleapis.com/auth/fitness.body_temperature.read",
"https://www.googleapis.com/auth/fitness.location.read",
"https://www.googleapis.com/auth/fitness.nutrition.read",
"https://www.googleapis.com/auth/fitness.oxygen_saturation.read",
"https://www.googleapis.com/auth/fitness.reproductive_health.read",
"https://www.googleapis.com/auth/fitness.activity.write"];


$db_user="id12780973_hospitalband";
$db_pass="HospitalBand";
$db_name="id12780973_hospitalband";
$db_host="localhost";

//TO_REMOVE -> solo di test per evitare mille click durante l'auth
/*$Scopes=["https://www.googleapis.com/auth/fitness.activity.write"];*/
?>