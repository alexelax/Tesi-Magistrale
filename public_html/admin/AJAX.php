<?php

require_once "common.php";

if(!IsAdminLogged())
{
    return json_encode(array("error"=>"Not Logged"));
    exit();
}

require_once "../HospitalBandAPI.php";
$HB= new HospitalBand();


if(isset($_GET["GetUsers"]))
{
    //TODO: rimuovo i dati "sensibili" ( access key ecc ecc )
    echo json_encode($HB->GetAllPatients());
}
if(isset($_GET["GetUserData"]))
{
    //TODO: rimuovo i dati "sensibili" ( access key ecc ecc )
    echo json_encode($HB->GetPatient($_GET["GetUserData"]));                    
}
if(isset($_GET["UpdateUserData"]))
{
    //TODO: rimuovo i dati "sensibili" ( access key ecc ecc )
    echo json_encode($HB->UpdateData($_GET["UpdateUserData"]));                    
}
  
?>