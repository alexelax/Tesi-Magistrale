<?php

if(!isset($_GET["id"]))
    return;
$UserID=$_GET["id"];


require_once('../Autoload/Psr/autoloader.php');
require_once('../Autoload/php-enum/autoloader.php');
require_once('../Autoload/ZipStream/autoloader.php');
require_once('../Autoload/PhpSpreadsheet/autoloader.php');
require_once('../LoginFramework/login.php');
require_once("../HospitalBandAPI.php");

        /*function GetDBStepsByID($ID)
        {
            $q=@"SELECT
                    DATE(Start) as Data, sum(Count) AS Passi
                FROM
                    user_steps_details
                WHERE
                    ID_USER = $ID
                GROUP BY
                    YEAR (Start),
                    MONTH (Start),
                    DAY (Start) DESC
                ORDER BY
                    Data";
    
                $conn=MysqliConn();
                $r=$conn->query($q);
                if($r)
                {
                    $ret=array();
                    while($row=$r->fetch_assoc())
                    {
                        $ret[]=$row;
                    }
                    return $ret;
                }
                else
                    return null;
                
        }
    
        function GetDBSleepsByID($ID)
        {
            $q=@"SELECT
                        Start as Data, Type as TYPE
                FROM
                        user_sleep_details
                WHERE
                        ID_USER = $ID
                ORDER BY
                        Data";
    
                $conn=MysqliConn();
                $r=$conn->query($q);
                if($r)
                {
                    $ret=array();
                    while($row=$r->fetch_assoc())
                    {
                        $ret[]=$row;
                    }
                    return $ret;
                }
                else
                    return null;
        }
    
        function GetDBBPMyID($ID)
        {
            $q=@"SELECT
                        Start as Data, Bpm as BPM
                FROM
                        user_cardio_rate
                WHERE
                        ID_USER = $ID
                ORDER BY
                        Data";
    
                $conn=MysqliConn();
                $r=$conn->query($q);
                if($r)
                {
                    $ret=array();
                    while($row=$r->fetch_assoc())
                    {
                        $ret[]=$row;
                    }
                    return $ret;
                }
                else
                    return null;
        }
*/

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;






$lm = new LoginManager();
$HB= new HospitalBand();



$Name= $lm->GetUser($UserID)["Name"];
if($Name==false)
        return;
$Surname= $lm->GetUser($UserID)["Surname"];
$LastUpdate= $lm->GetUser($UserID)["LastUpdate"];
$RegistrationDate= $lm->GetUser($UserID)["RegistrationDate"];



$Steps= $HB->GetDBStepsByID($_GET["id"]);
//print_r($Steps);
if($Steps===null)
    return;

$Sleeps= $HB->GetDBSleepsByID($_GET["id"]);
//print_r($Sleeps);
if($Sleeps===null)
    return;
    
$BPMs= $HB->GetDBBPMyID($_GET["id"]);
//print_r($BPMs);
if($BPMs===null)
    return; 






// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Set document properties
$spreadsheet->getProperties()->setCreator('Medical Band')
    ->setLastModifiedBy('Medical Band')
    ->setTitle("$Name $Surname - $LastUpdate")
    ->setSubject("$Name $Surname - $LastUpdate")
    ->setDescription("Esportazione dei dati di $Name $Surname");










// Add some data
$sheet=$spreadsheet->setActiveSheetIndex(0);

$sheet->setCellValueByColumnAndRow(1,1, 'Data');
$sheet->setCellValueByColumnAndRow(2,1, 'Passi');
for($i=0;$i<count($Steps);$i++)
{
    $sheet->setCellValueByColumnAndRow(1,2+$i, $Steps[$i]['Data']);
    $sheet->setCellValueByColumnAndRow(2,2+$i, $Steps[$i]['Passi']);
}

// Rename worksheet
$spreadsheet->getActiveSheet()->setTitle('Steps');












$sheet= $spreadsheet->createSheet();

$sheet->setCellValueByColumnAndRow(1,1, 'Data');
$sheet->setCellValueByColumnAndRow(2,1, 'Tipo');
for($i=0;$i<count($Sleeps);$i++)
{
    $sheet->setCellValueByColumnAndRow(1,2+$i, $Sleeps[$i]['Data']);
    $sheet->setCellValueByColumnAndRow(2,2+$i, $Sleeps[$i]['TYPE']);
}


$sheet->setCellValueByColumnAndRow(5,1, 'Tipo');
$sheet->setCellValueByColumnAndRow(6,1, 'Descrizione');

$sheet->setCellValueByColumnAndRow(5,2, '72');
$sheet->setCellValueByColumnAndRow(6,2, 'Sonno');

$sheet->setCellValueByColumnAndRow(5,3, '109');
$sheet->setCellValueByColumnAndRow(6,3, 'Sonno leggero');

$sheet->setCellValueByColumnAndRow(5,4, '110');
$sheet->setCellValueByColumnAndRow(6,4, 'Sonno profondo');

$sheet->setCellValueByColumnAndRow(5,5, '111');
$sheet->setCellValueByColumnAndRow(6,5, 'REM');

$sheet->setCellValueByColumnAndRow(5,6, '112');
$sheet->setCellValueByColumnAndRow(6,6, 'Sveglio ( durante il ciclo notturno ) ');

// Rename worksheet
$sheet->setTitle('Sleep');











// Add some data
$sheet= $spreadsheet->createSheet();

$sheet->setCellValueByColumnAndRow(1,1, 'Data');
$sheet->setCellValueByColumnAndRow(2,1, 'BPM');
for($i=0;$i<count($BPMs);$i++)
{
    $sheet->setCellValueByColumnAndRow(1,2+$i, $BPMs[$i]['Data']);
    $sheet->setCellValueByColumnAndRow(2,2+$i, $BPMs[$i]['BPM']);
}

// Rename worksheet
$sheet->setTitle('BPMs');










// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"".$Surname."_".$Name."_".$LastUpdate."\".xlsx");
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;

?>

