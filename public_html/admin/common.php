<?php

include_once "../LoginFramework/login.php";

$lm=new LoginManager();

function IsAdminLogged()
{
    global $lm;
    
    if( $lm-> isLogged())
    {
        //TODO: controllo il tipo di account
        //Se è admin true
        return true;
    }


    return false;
}


?>