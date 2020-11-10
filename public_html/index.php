<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once "LoginFramework/login.php";
$lm=new LoginManager();

if( $lm-> isLogged())
{
	header("location: admin/index.php");
	exit();
}

?>

<html>
    <head>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" ></script>    
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet'>
    <style>
    body {
        font-family: 'Lato'; 
        font-size: 20px;
    }
    .btn-primary {
        background-color: #faad14 !important;
        border-color: #faad14 !important;
        border-width: 2px;
        color: #FFFFFF !important; /* colore scritta bottone */
        box-shadow: none !important; /* alone */
    }
    a { 
        text-decoration: none; 
        color: #FFFFFF; 
    } 
    a:hover { 
        text-decoration: none;
        color: #FFFFFF; 
    }

    </style>
    </head>
    <body background="./Resources/Sfondo.png">
    <div>
    <img src="./Resources/Logo.png" alt="Logo" width="120px" height="180px" style="float:left; margin:-30px 50px">
        <a href="Login.php" class="a"><p align="right" style="margin:70px 120px; font-size:30px"><span style="padding: 10px; border: 1px solid;">LOGIN</span></p></a>
    </div>
        
        <br><br><br><br>
        <!-- TITOLO TESI: Analisi e sviluppo di soluzioni per la raccolta dati dei Patient Reported Outcomes (PRO), tramite device in ambito IoT medicale -->
        <h1 align="center" style="color:white; font-size:70px">MEDICAL BAND</h1>
        <br><br>
        <h3 align="center" style="color:white">WebApp per la raccolta e l'analisi di dati (numero di passi, qualità del sonno, battito cardiaco),<br><p></p> 
                                               ricavati da dispositivi IoT a basso costo, al fine di correlarli con altri dati di origine clinica o con i Patient Reported Outcomes (PRO)</h3>
        
        <br><br><br>

        <div align="center">
        <a href="/Regist.php"><button type="button" class="btn btn-primary btn-lg">Registra Nuovo Utente</button></a>
        </div>
    </body>


</html>