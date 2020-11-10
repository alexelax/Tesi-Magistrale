<?php

require_once "common.php";
if(!IsAdminLogged())
{
    //redirect to index
    header("location:../index.php");
    exit();
}


require_once "../HospitalBandAPI.php";
$HB= new HospitalBand();


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
        font-family: 'Lato'; font-size: 16px;
    }

    .OverlayFullscreen.Show 
    {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #00000075;
        z-index: 999;
        text-align: center; 
    }
    .OverlayFullscreen.Hide {
        display: none;
    }

    
    .OverlayFullscreen .Centered {

        position: absolute;
        top: 50%;
        transform: translate(-50%,-50%);
        Left: 50%;
    }

    #OverlayShow .Centered{
        width: 80%;
        height: 80%;
    }
    #OverlayShow iframe {
        border: 0;
        width: 100%;
        height: 100%;
    }
    #OverlayShow #Exit {
        position: absolute;
        right: -48;
        top: -48;
        cursor: pointer;
    }

    #OverlayProgressBar .Centered{
        width: 35%;
        height: 22%;
        background-color: white;
        text-align:center;
        padding: 50px;
    }

    .ProgressBarPercentage
    {
        position: absolute;
        left: 50%;
        transform: translate(-50%,0%);
        font-size: 33px;
    }



    .btn-primary {
        background-color: #faad14 !important;
        border-color: #faad14 !important;
        border-width: 2px;
        color: #FFFFFF !important; /* colore scritta bottone */
        box-shadow: none !important; /* alone */
    }


    </style>

            <script src="jquery.js"> </script>
            <script src="script.js"> </script>
            <script> 
                //A pagina caricata si richiama la LoadUsers che carica div#ContainerUsers tutti gli utenti ( in una tabella )
                //cliccando us "Get Data" viene fatta richiesta al server di aggiornare i dati e ritorna i dati aggiornati ( quello che importa è "LastUpdate" )
                

                
                $(document).ready(function()
                {
                    LoadUsers();        
                    //ShowOverlay(16);
                    $("#Exit").click(function()
                    {
                        HideOverlay();
                    })
                });


                /**
                 * Carica tutti gli utenti nella tabella
                 */
                function LoadUsers()
                {
                   /* 
                   // OLD
                   GetUsers(function(data){
                        
                        var t=$("<table></table>");
                        $("#ContainerUsers").html(t);
                        $(t).append("<tr><th>ID</th><th>User</th><th>LastUpdate</th><th>Actions</th></tr>");//creo l'header della tablella
                        //var Buttons=CreateButtons();    //creo i bottoni relative alle azioni che si possono fare

                        //Per ciascun utente stampo una riga
                        data.forEach(function(user) {   
                            var row=CreateGUIUser(user);
                            $(t).append(row)
                        });
                        //$(".btnContainer").append(Buttons); //aggiungo a ciascuna riga i bottoni
                        //TODO: sposto l'aggiunta dei bottoni nella creazione riga? più lento ma più logico...

                    });
                    */  

                   
                    GetUsers(function(data){      

                        var t=$("<table></table>");
                        $(t).attr("style","width:100%");
                        $("#ContainerBootstrapUsers").html(t);

                        var NumCol=3;
                        var CurrentCol=NumCol+1;

                        var PercentColumnSize=100/NumCol;
                        var CurrentRow;
                        var Index=0;

                        Object.keys(data).forEach(function (key){
                            var user = data[key];

                        //data.forEach(function(user) {     // solo per array
                            if(CurrentCol > NumCol)
                            {
                                CurrentCol=1;
                                CurrentRow=$("<tr></tr>");
                                $(t).append(CurrentRow);
                            }
                            var td=$("<td></td>");
                            $(td).attr("style","vertical-align: top;width:"+PercentColumnSize+"%");
                            $(CurrentRow).append(td);
                            $(td).append(CreateBootstrapUser(user));

                            CurrentCol++;
                            Index++;
                        });
                        
                        if(Index>0 && Index<NumCol)
                        {
                            for( var i = 0; i < NumCol-Index; i++ )  //creo un numero di colonne mancanti (caso 1-2)
                            {
                                var td=$("<td></td>");
                                $(td).attr("style","vertical-align: top;width:"+PercentColumnSize+"%");
                                $(CurrentRow).append(td);       //visto che siamo ancora nella prima riga -> currentRow punta ancora alla prima riga
                            }
                        }

                        $("#ContainerBootstrapUsers").html(t);
                        BindButtonChangeIcon();
                    });

                }
                /**
                 * Richiede l'update dei dati al server e ritorna i dati aggiornati ( data aggiornata )
                 */
                function UpdateUser(id)
                {
                    return new Promise(resolve => {
                        UpdateUserData(id,(data)=>{
                            var DOMUser=GetDOMUser(id);
                            //TODO: controllo se esiste...
                            RefreshGUIUser(DOMUser,data); 
                            resolve('resolved');
                        });
                    });
                   
                }



               




                /**
                 * Ritorna la row corrispondente all'utente
                 */
                function GetDOMUser(id)
                {
                    return $("#ContainerBootstrapUsers table tr[ID_user="+id+"]");
                }
                /**
                 * Aggiorna la row di un utente con i nuovi dati
                 */
                function RefreshGUIUser(DOMUser,data)
                {
                    $(DOMUser).html("<td>"+data["ID"]+"</td><td>"+data["username"]+"</td><td>"+data["LastUpdate"]+"</td>")
                    $(DOMUser).attr("ID_user",data["ID"]);
                    $(DOMUser).attr("username",data["username"]);
                    $(DOMUser).attr("AuthCode",data["AuthCode"]);
                    $(DOMUser).attr("AccessToken",data["AccessToken"]);
                    $(DOMUser).attr("RefreshToken",data["RefreshToken"]);
                    $(DOMUser).attr("LastUpdate",data["LastUpdate"]);
                    var td=$("<td></td>");
                    $(td).append(CreateButtons());
                    $(DOMUser).append(td);
                }
                /**
                 * Crea una row Utente
                 */
                function CreateGUIUser(data)
                {
                    var row=$("<tr></tr>");
                    RefreshGUIUser(row,data);
                    return row;
                }
                /**
                 * Crea i bottoni per interagire con i dati dell'user
                 */
                function CreateButtons()
                {
                    
                    var Buttons=$("<div></div>");

                    //Bottone SINCRONIZZA
                    var b1=$("<button>Sincronizza</button>");
                    $(b1).click(function(){
                        var row=$(this).parent().parent().parent();
                        UpdateUser(row.attr("ID_user"));
                    });
                    $(Buttons).append(b1);
                    $(Buttons).append(" ");

                    //Bottone DOWNLOAD
                    var b2=$("<button>Download</button>");
                    $(b2).click(function(){
                       
                    });
                    $(Buttons).append(b2);
                    $(Buttons).append(" ");
                    
                    //Bottone SHOW
                    var b2=$("<button>Show</button>");
                    $(b2).click(function(){
                        
                    });
                    $(Buttons).append(b2);

                    return Buttons;
                }


                var UniqueID=0;
                /**
                 * Crea una row Utente
                 */
                function CreateBootstrapUser(data)
                {
                    //console.log(data);
                    var v1=document.createElement('DIV');
                    $(v1).attr('class','card w-75');
                    $(v1).attr('style','margin:10px 60px; float: left;');
                    $(v1).attr('id_user',data["ID"]);
                    var v2=document.createElement('DIV');
                    $(v2).attr('class','card-body');
                    var v3=document.createElement('TABLE');
                    $(v3).attr('style','width:100%');
                    var v4=document.createElement('TBODY');
                    var v5=document.createElement('TR');
                    var v6=document.createElement('TD');
                    var v7=document.createElement('H5');
                    $(v7).attr('align','center');
                    $(v7).attr('class','card-title');
                    $(v7).append(data["Name"] +" "+ data["Surname"]);
                    $(v6).append(v7);
                    var v8=document.createElement('P');
                    $(v8).attr('align','center');
                    $(v8).attr('class','card-text');
                    $(v8).attr("id","LastUpdate");
                    $(v8).append(data["LastUpdate"]);
                    $(v6).append(v8);
                    $(v5).append(v6);
                    var v9=document.createElement('TD');
                    var v10=document.createElement('A');
                    $(v10).attr('data-toggle','collapse');
                    $(v10).attr('role','button');
                    $(v10).attr('aria-expanded','false');
                    $(v10).attr('data-target','#collapse_user_'+UniqueID);
                    $(v10).attr('class','bnt_collapse');
                    $(v10).attr('style','cursor:pointer;');
                    var v11=document.createElement('IMG');
                    $(v11).attr('src','../Resources/Plus_Button.png');
                    $(v11).attr('alt','Plus');
                    $(v11).attr('width','50px');
                    $(v11).attr('height','50px');
                    $(v11).attr('style','float:right; ');
                    $(v11).append('');
                    $(v10).append(v11);
                    $(v9).append(v10);
                    $(v5).append(v9);
                    $(v4).append(v5);
                    $(v3).append(v4);
                    $(v2).append(v3);
                    var v12=document.createElement('DIV');
                    $(v12).attr('class','collapse');
                    $(v12).attr('id','collapse_user_'+UniqueID);
                    var v13=document.createElement('DIV');
                    $(v13).attr('align','center');
                    $(v13).attr('style','padding-top:25px;');
                    var v14=document.createElement('BUTTON');
                    $(v14).attr('type','button');
                    $(v14).attr('class','btn btn-primary');
                    $(v14).attr("style","margin: 10px;");
                    $(v14).attr('onclick','Syncronize('+data["ID"]+');');
                    $(v14).append('SINCRONIZZA');
                    $(v13).append(v14);
                    var v15=document.createElement('BUTTON');
                    $(v15).attr('type','button');
                    $(v15).attr('class','btn btn-primary');
                    $(v15).attr("style","margin: 10px;");
                    $(v15).attr('onclick','Download('+data["ID"]+');');
                    $(v15).append('DOWNLOAD');
                    $(v13).append(v15);
                    var v16=document.createElement('BUTTON');
                    $(v16).attr('type','button');
                    $(v16).attr('class','btn btn-primary');
                    $(v16).attr("style","margin: 10px;");
                    $(v16).attr('onclick','Show('+data["ID"]+');');
                    $(v16).append('SHOW');
                    $(v13).append(v16);
                    $(v12).append(v13);
                    $(v2).append(v12);
                    $(v1).append(v2);

                    UniqueID++;
                    return v1;
                }
                async function Syncronize(id)
                {
                    await UpdateBootstrapUser(id);
                    alert("SINCRONIZZAZIONE COMPLETATA! \n Dati Aggiornati!"); 
                }

                function Download(id)
                {   
                    //build the new URL
                    var my_url = 'download.php?id=' +id;
                    //load it into a hidden iframe
                    var iframe = $("<iframe/>").attr({
                        src: my_url,
                        style: "visibility:hidden;display:none"
                    }).appendTo($("#tmpDiv"));
                }


                function Show(id)
                {
                    ShowOverlay(id);
                }


                function UpdateBootstrapUser(id)
                {        
                    return new Promise(resolve => {
                        UpdateUserData(id,function(data){

                            //console.log(data);
                            var DOMUser=$("div.card[id_user="+id+"]");
                            //console.log(DOMUser);

                            //console.log(data["LastUpdate"]);
                            $(DOMUser).find("#LastUpdate").html(data["LastUpdate"]);
                            resolve('resolved');
                            
                        });
                    });
                }

                 /**
                 * Aggiorna tutti gli utenti
                 */
                async function UpdateUsers()
                {
                    SetProgressBarValue(0);
                    ShowProgressBar();
                    var Users=GetAllPrintedUsers();
                    var NumEl=Users.length;
                    for(var i=0;i<NumEl;i++)
                    {
                        await UpdateBootstrapUser(Users[i]);
                        SetProgressBarValue(100*(i+1)/NumEl);
                        console.log("risolto il "+(100*(i+1)/NumEl)+"%");
                    }

                    //TODO: aspetta 2 secondi e scrive finito sulla progress....

                    HideProgressBar();
                }


                /**
                 * recupera tutti gli ID dei DOM Utenti stampati
                 */
                function GetAllPrintedUsers()
                {
                    var Users=[];
                    $("#ContainerBootstrapUsers table tr div[id_user]").each(function( index ) { 
                        Users.push($(this).attr("id_user"));
                    });

                    return Users;
                }


                function ShowOverlay(IDUser)
                {
                    $("#OverlayShow iframe").attr("src","show.php?ID="+IDUser);
                    $("#OverlayShow").removeClass("Hide");
                    $("#OverlayShow").addClass("Show");
                }

                function HideOverlay()
                {
                    $("#OverlayShow").removeClass("Show");
                    $("#OverlayShow").addClass("Hide");
                    $("#OverlayShow iframe").attr("src","white.php");
                }




                function ShowProgressBar()
                {
                    $("#OverlayProgressBar").removeClass("Hide");
                    $("#OverlayProgressBar").addClass("Show");
                }
                function HideProgressBar()
                {
                    $("#OverlayProgressBar").removeClass("Show");
                    $("#OverlayProgressBar").addClass("Hide");
                }
               
                function SetProgressBarValue(percent)
                {
                    $("#OverlayProgressBar .progress-bar").css("width",percent+"%");
                    $("#OverlayProgressBar .ProgressBarPercentage").text(percent+"%");
                }

            </script>
    </head>
    <?php include("../_header.php");?>

    <body>
            <div id="OverlayShow" class="OverlayFullscreen Hide">
                <div id="OverlayShowCenter" class="Centered">
                    <div id="Exit">
                    <img src="../Resources/Exit.png"/>
                    </div> 
                    <iframe src="white.php"></iframe>
                </div>
            </div>


            <div id="OverlayProgressBar" class="OverlayFullscreen Hide">
                <div id="OverlayProgressBarCenter" class="Centered">
                    <span>Sincronizzazione in corso...</span><br><br>
                    
                    <div class="progress" style="border:1px solid #3936fb;height:50px;">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="00" aria-valuemin="0" aria-valuemax="100" style="width:90%; height: 50px;"></div>
                            <span class="ProgressBarPercentage"> 70%</span>
                        </div>
                    </div>
                
                </div>
            </div>
            <br><br>
            <div align="right" style="margin:0px 60px;">
                <button type="button" class="btn btn-primary btn-lg" onclick="UpdateUsers();">SINCRONIZZA TUTTO</button>
            </div>
            <br><br><br>

            
            
            <!--
            BLOCCO CARTA DEMO
            <div class="card w-25" style="margin:10px 60px; float: left;" id_user="14">
            <div class="card-body">
                <table style="width:100%">
                    <tr>
                        <td>
                            <h5 align="center" class="card-title">DEMO</h5>
                            <p align="center" class="card-text">2020-04-01 17:00:59</p> 
                        </td>
                        <td>
                            <a data-toggle="collapse" role="button" aria-expanded="false" data-target="#collapse_user_0" class="bnt_collapse" style="cursor:pointer;"><img src="../Resources/Plus_Button.png" alt="Plus" width="50px" height="50px" style="float:right; "></a>
                        </td>
                    </tr>
                </table>
                
               <div class="collapse" id="collapse_user_0">
                    <div align="center" style="padding-top:25px;">
                        <button type="button" class="btn btn-primary btn-lg" onclick="Syncronize();">SINCRONIZZA</button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="Download();">DOWNLOAD</button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="Show();">SHOW</button>
                    </div>
                </div>
            </div>
            </div>
            https://getbootstrap.com/docs/4.0/components/collapse/
            -->
            
            <div id="ContainerBootstrapUsers"></div>
            <!--<div id="ContainerUsers"></div>-->
               
            <div id="tmpDiv"></div>

            <script>
                //cambio immagine ai bottoni
                function BindButtonChangeIcon()
                {
                 $("a.bnt_collapse").click(function()
                 {
                    var selector=$(this).attr("data-target");

                    if(!$(selector).hasClass("show"))
                    {
                        //imposto il meno
                        $(this).children("img").attr("src","../Resources/Minus_Button.png");
                    }
                    else
                    {
                        //imposto il più
                        $(this).children("img").attr("src","../Resources/Plus_Button.png");

                    }
                 });
                };

            </script>
    
       
    </body>


</html>