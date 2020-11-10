<?php

function RedirectLogin($AccountTipe)
{
    if($AccountTipe==1)
    {
        header("location: admin/index.php");
    }
    else if($AccountTipe==2)
    {
        header("location: RichiestaAuth/index.php");
    }

}


?>