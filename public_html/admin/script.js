//Questo JS server per richiamare tutte le funzioni nella pagina AJAX
var PrintAjax=false;
function PrintAjaxFunction(data)
{
    $("body").append(data);
}




function GetUsers(success)
{
    AjaxRequest("AJAX.php",{"GetUsers":1},function(data){
        success(data);
    });
}
function UpdateUserData(id,success)
{
    AjaxRequest("AJAX.php",{"UpdateUserData":id},function(data){
        if( data["success"]==true)
        {
            GetUserData(id,success);
        }
       else
       {
           alert("Errore durante l'aggiornamento dei dati...\r\n"+data["message"]);
       }
    });
}
function GetUserData(id,success)
{
    AjaxRequest("AJAX.php",{"GetUserData":id},function(data){
        success(data);
    });
}



function AjaxRequest(url,data,success)
{
    $.get("AJAX.php",data,function(d){
        if(PrintAjax)
            PrintAjaxFunction(d);
        success(JSON.parse(d));
    });
}