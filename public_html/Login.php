<?php


include_once "LoginFramework/login.php";
include_once "_RedirectLogin.php";

$lm=new LoginManager();

if(isset($_GET["logout"]))
{
    $lm->logout();
    header("location: index.php");
}

if( $lm-> isLogged())
{
    
    //prendo il tipo di account;      //1 -> Admin | 2 -> User
    $TipoDiAccount=$lm->getSessionUserData()["TypeOfAccount"];
    RedirectLogin($TipoDiAccount);

}
else
{
    //Se non sono autenticato
    if( isset($_POST["User"]) && isset($_POST["Pass"]))
    {      

        $UserData=$lm->loginUnsafe($_POST["User"],$_POST["Pass"]);
        if($UserData==false || $UserData==null) 
        {
            //NON AUTENTICATO!
            LoginHTML("User o Pass Errati!");
        }
        else
        {
            //autenticato
            //in base al tipo di account -> redirect o all'admin o alla request AUTH
            $TipoDiAccount = $lm->getSessionUserData()["TypeOfAccount"];
            RedirectLogin($TipoDiAccount);
        }
        
        

    }
    else
    {
        LoginHTML();
    }
}






function LoginHTML($Message="")
{
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

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
    </style>
    </head>
    <body background="../Resources/Sfondo.png">
    <div>
    <img src="../Resources/Logo.png" alt="Logo" width="120px" height="180px" style="float:left; margin:-30px 50px">
    <p align="right" style="margin:70px 120px; color:white; font-size:15px"></p>
    </div>
        
        <br><br><br><br><br>

        <div class="card" style="margin:auto; width:400px">
        <div class="card-body">
            <div align="center">
            <img src="../Resources/User_Icon.png" alt="Icon" width="70px" height="70px" style="float:left; margin:10px 30px">
                <br><p></p>
                <h5 style="color:#4a6bc3; margin:-10px 80px" class="card-title"><b>LOGIN</b></h5><br><br>
                <h7 style="color:#4a6bc3; margin:-15px 0px">Se sei un paziente inserisci come username il tuo CODICE FISCALE</h7>
                <br><br>
            </div>
            <div style="text-align: center; color: red;">
                <?php
                    if($Message!="")
                    {
                        echo $Message;
                    }
                ?>
            </div>
            <form method="POST" action="<?php echo $actual_link; ?>">
                <table align="center">
                    <tr>
                        <td>
                            Username:
                        </td>
                        <td>
                            <input name="User"/>
                        </td>
                    </tr>
                    <tr><td><tr><td><tr><td><tr><td><tr><td><tr><td>
                    <tr>
                        <td>
                            Password:
                        </td>
                        <td>
                            <input name="Pass"/>
                        </td>
                    </tr>
                </table>
                <br>
                <button class="btn btn-primary btn-lg" style="float:right;">Login</button>
            </form>         
                <a href="../index.php"><button class="btn btn-outline-primary btn-lg" style="float:left; margin:-20px 2px">Annulla</button></a>
            </div>
        </div>
        </div>  
    </body>


</html>

<?php
}

?>