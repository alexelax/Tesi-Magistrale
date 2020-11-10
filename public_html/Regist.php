<?php
include_once "LoginFramework/login.php";
$lm=new LoginManager();
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if( isset($_POST["AJAX"]) && $_POST["AJAX"]=="GeneratePassword" && isset($_POST["data"]))
{
    $data=json_decode($_POST["data"],true);
    echo CalculatePsw($data["Name"],$data["Surname"],$data["CF"]);
    exit();
}
$ToPrint=null;
$Error=false;
if(isset($_POST["Name"]) && isset($_POST["Surname"]) && isset($_POST["CF"]))       //Effettua la registrazione e salva su DB l'utente
{
    
    if(strlen($_POST["Name"])>0  && strlen($_POST["Surname"])>0)
    {
        $Pass=CalculatePsw($_POST["Name"],$_POST["Surname"],$_POST["CF"]);

        //check CF
        if(!CheckCF($_POST["CF"]))
        {
            $Error=true;
            $ToPrint="Codice fiscale errato";
        }
        else
        {
            
            $r=$lm->registerUnsafe($_POST["CF"],$Pass);      //imposto i campi CF e Pass come Username e Password
            if($r!==true)
            {
                $Error=true;
                $ToPrint="User già presente";
            }
            else
            {
                $lm->loginUnsafe($_POST["CF"],$Pass);
                $ID=$lm->getID();
                $lm->SetAdditionalData($ID,"RegistrationDate", date("Y-m-d H:i:s"));
                $lm->SetAdditionalData($ID,"Name", $_POST["Name"]);
                $lm->SetAdditionalData($ID,"Surname", $_POST["Surname"]);
                //Tipo di Account -> gia settato come default  ( 2 - user ) nel DB
                header("location:/RichiestaAuth/");
                exit();
            }
    
         
        }
    }
    else
    {
        $Error=true;
        $ToPrint="Il nome e cognome devono avere almeno 1 carattere";
    }
   
}

function CalculatePsw($Nome,$Cognome,$CF)
{    
    return $Nome.".".$Cognome.".".date("Y");
}
function CheckCF($CF)      //TODO: controllo se il nome e cognome sono corretti con il CF??
{
    $regex="/^(?:[A-Z][AEIOU][AEIOUX]|[B-DF-HJ-NP-TV-Z]{2}[A-Z]){2}(?:[\dLMNP-V]{2}(?:[A-EHLMPR-T](?:[04LQ][1-9MNP-V]|[15MR][\dLMNP-V]|[26NS][0-8LMNP-U])|[DHPS][37PT][0L]|[ACELMRT][37PT][01LM]|[AC-EHLMPR-T][26NS][9V])|(?:[02468LNQSU][048LQU]|[13579MPRTV][26NS])B[26NS][9V])(?:[A-MZ][1-9MNP-V][\dLMNP-V]{2}|[A-M][0L](?:[1-9MNP-V][\dLMNP-V]|[0L][1-9MNP-V]))[A-Z]$/i";
    
    if (preg_match($regex, $CF)) 
        return true;

    return false;
}

function Regist()
{
    global $actual_link;
    ?>
    
    <form method="POST" action="<?php echo $actual_link; ?>" style="margin:0px 30px; font-size:16px" onsubmit="return validateform()">
        <table id="RegistrationTable">
                <tr>
                    <td>
                        Nome:
                    </td>
                    <td>
                        <input type="text" class="text-line" name="Name" id="Name" style="margin:0px 10px"/>
                    </td>
                </tr>               
                <tr>
                    <td>
                        Cognome:
                    </td>
                    <td>
                        <input type="text" class="text-line" name="Surname" id="Surname"  style="margin:0px 10px"/>
                    </td>
                </tr>        
                <tr>
                    <td>
                        Codice Fiscale:
                    </td>
                    <td>
                        <input type="text" class="text-line" name="CF" id="CF" style="margin:0px 10px"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        Password:
                    </td>
                    <td>
                        <input type="text" class="text-line" name="psw" id="psw" style="margin:0px 10px" disabled=disabled/>
                    </td>
                </tr>
                <!--<tr>
                    <td>
                        Conferma Password:
                    </td>
                    <td>
                        <input type="text" class="text-line" name="confpsw" style="margin:0px 10px"/><br><br>
                    </td>
                </tr>-->
        </table>       
        <br>                      
        <div align="right">
            <button class="btn btn-primary btn-lg" >Avanti</button>                
        </div>

    </form>

    <a href="../index.php"><button class="btn btn-outline-primary btn-lg" style="float:left; margin:-48px 30px">Annulla</button></a>

    <?php
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
    .btn-outline-primary {       
        border-color: #faad14 !important;
        border-width: 2px;
        color: #faad14 !important; /* colore scritta bottone */
        box-shadow: none !important; /* alone */
    }
    .btn-outline-primary:hover {       
        background-color: #FFFFFF !important;
    }
    #RegistrationTable td{
        height:40px;
    }
    </style>
    <script>
        $(document).ready(function () {
            /*$('#CF, #Name, #Surname').keyup(function (e) {
                if (e.keyCode != 8 && e.keyCode != 46) {
                    GetPsw();
                }
            });*/
            $('#CF, #Name, #Surname').focusout(function (e) {
                GetPsw();
            });
            
        });


        function CheckName()
        {
            if($("#Name").val().length > 0)
                return true;
            return false;
        }
        function CheckSurname()
        {
            if($("#Surname").val().length > 0)
                return true;
            return false;
        }    
        function CheckCF()      //TODO: controllo se il nome e cognome sono corretti con il CF
        {
            var str=$("#CF").val(); 
            var regex = /^(?:[A-Z][AEIOU][AEIOUX]|[B-DF-HJ-NP-TV-Z]{2}[A-Z]){2}(?:[\dLMNP-V]{2}(?:[A-EHLMPR-T](?:[04LQ][1-9MNP-V]|[15MR][\dLMNP-V]|[26NS][0-8LMNP-U])|[DHPS][37PT][0L]|[ACELMRT][37PT][01LM]|[AC-EHLMPR-T][26NS][9V])|(?:[02468LNQSU][048LQU]|[13579MPRTV][26NS])B[26NS][9V])(?:[A-MZ][1-9MNP-V][\dLMNP-V]{2}|[A-M][0L](?:[1-9MNP-V][\dLMNP-V]|[0L][1-9MNP-V]))[A-Z]$/i
            return regex.test(str); 
        } 
        
        function GetPsw()
        {
            var data = JSON.stringify({"Name":$("#Name").val(),"Surname":$("#Surname").val(),"CF":$("#CF").val()});
            $.post("Regist.php",{"AJAX":"GeneratePassword","data":data},function(d){
                $("#psw").val(d);
            });
        }
        function validateform()
        {
            if (!CheckName())
            {
                $("#ErrorMessage").html("Campo NOME vuoto");
                return false;
            }
            else if (!CheckSurname())
            {
                $("#ErrorMessage").html("Campo COGNOME vuoto");
                return false;
            }
            else if (!CheckCF())
            {           
                $("#ErrorMessage").html("CODICE FISCALE errato");    
                 return false;
            }

            $("#ErrorMessage").html("");
            return true;
        }


        </script>
    </head>
    <body background="../Resources/Sfondo.png">
    <div>
    <img src="../Resources/Logo.png" alt="Logo" width="120px" height="180px" style="float:left; margin:-30px 50px">
    <p align="right" style="margin:70px 120px; color:white; font-size:15px"></p>
    </div>
        
        <br><br><br><br><br>

        <div class="card" style="margin:auto; width:420px"> 
        <div class="card-body">
            <div align="center">
            <img src="../Resources/User_Icon.png" alt="Icon" width="70px" height="70px" style="float:left; margin:10px 50px">
                <br>
                    <h5 style="color:#4a6bc3; margin:-10px 50px" class="card-title"><b>REGISTRA NUOVO UTENTE</b></h5>
                <br><br>

            <div id="ErrorMessage">
                <?php
                    if($Error)
                    {
                        echo $ToPrint;  //TODO: scirvere errori in rosso
                    }
                ?>
            </div>
            <?php
                Regist();

            ?>
        </div>
        </div>  
    </body>  


</html>