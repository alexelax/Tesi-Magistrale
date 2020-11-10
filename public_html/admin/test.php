<?php



function MillisToDate($Millis)
{
    $date = new DateTime(date("Y-m-d H:i:s",$Millis), new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone("Europe/Rome"));
    return $date->format('Y-m-d H:i:s'); 
}





?>
