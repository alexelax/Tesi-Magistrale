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
        height:100%;
    }
    .nav-tabs{
    background-color:#6f92e8; /* colore navbar */
    }
    .nav-tabs > li > a:hover{
    background-color: #3b54d1 !important;   /* colore di over barra */        
        color:#FFF; /* colore over scritte */
    }
    .nav-tabs > li > a {
    color: #d8e1f9; /* colore scritte */
    }
    .active a { 
        background-color: #283a94 !important;   /* colore tab attiva */
        color:white !important;

    }
    .nav-tabs .active .nav-link:hover {
        border-color: transparent;
        background-color: #283a94 !important;
        cursor:default;
    }
    #StepsTab,#SleepTab,#BPMTab
    {
        display:none;
    }
    #StepsTab.active,#SleepTab.active,#BPMTab.active
    {
        display:block;
        width:100%;
    }

    </style>

    <script src="script.js" ></script> 
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
      
    <script> 
        var ID=<?php echo $_GET["ID"]; ?>;
        $(document).ready(function()
        {
            ClickNavBind();

            ShowStepsChart();
            
        });

function ShowStepsChart()
{
    if(StepsLoad)
    {
        return;
    }
    StepsLoad=true;


    google.charts.load('current', {
    callback: function () {
        var data = google.visualization.arrayToDataTable([
        ['Data','Steps'],
                <?php
                    $Steps=$HB-> GetDBStepsByID($_GET["ID"]);
                    if($Steps!=null)
                    {
                        foreach($Steps as $k=>$v)
                        {
                            echo "[new Date(\"$v[Data] 00:00:00\"), $v[Passi]],\r\n";
                        }
                    }
               ?>
        ]);

        var Number=<?php echo $Steps==null?0:count($Steps);    ?>;
        if(Number==0)
        {
            $("#Steps_control_div").html("<h2 style='color:red'>Non ci sono dati nel DB</h2>");
            return;

        }

        var dash = new google.visualization.Dashboard(document.getElementById('dashboard'));

        var control = new google.visualization.ControlWrapper({
        controlType: 'ChartRangeFilter',
        containerId: 'Steps_control_div',
        options: {
            filterColumnIndex: 0,
            ui: {
            chartOptions: {
                height: '100',
                width: '70%',
                chartArea: {
                width: '80%'
                }
            },
            chartView: {
                columns: [0, 1]
            }
            }
        }
        });

        var chart = new google.visualization.ChartWrapper({
        chartType: 'LineChart',
        containerId: 'Steps_chart_div'
        });

        google.visualization.events.addListener(control, 'statechange', function () {
            var dateRange = control.getState().range;
            var dayInMS =  1 * 24 * 60 * 60 * 1000;
            var periodShown = dateRange.end.getTime() - dateRange.start.getTime();
            if (periodShown <= dayInMS) {
                chart.setOption('hAxis.format', 'H:MM:SS');
            } else {
                chart.setOption('hAxis.format', 'dd/MM/yyyy');
            }
            chart.draw();
        });

        function setOptions(wrapper) {
        wrapper.setOption('height', '400');
        wrapper.setOption('width', '100%');
        wrapper.setOption('animation.duration', 0);
        wrapper.setOption('hAxis.format', 'dd/MM/yyyy');
        }

        setOptions(chart);

        dash.bind([control], [chart]);
        dash.draw(data);
    },
    packages: ['controls', 'corechart']
    });
}

function ShowSleepChart()
{
    if(SleepLoad)
    {
        return;
    }
    SleepLoad=true;

   
    google.charts.load('current', {
    callback: function () {
        var data = google.visualization.arrayToDataTable([
        ['Data','Types'],
                <?php
                    $Types=$HB-> GetDBSleepsByID($_GET["ID"]);
                    if($Types!=null)
                    {
                        foreach($Types as $k=>$v)
                        {
                            echo "[new Date(\"$v[Data]\"), $v[TYPE]],\r\n";
                        }
                    }
               ?>
        ]);

        var Number=<?php echo $Types==null?0:count($Types);    ?>;
        if(Number==0)
        {
            $("#Sleeps_control_div").html("<h2 style='color:red'>Non ci sono dati nel DB</h2>");
            return;
        }

        var dash = new google.visualization.Dashboard(document.getElementById('dashboard'));

        var control = new google.visualization.ControlWrapper({
        controlType: 'ChartRangeFilter',
        containerId: 'Sleeps_control_div',
        options: {
            filterColumnIndex: 0,
            ui: {
            chartOptions: {
                height: '100',
                width: '70%',
                chartArea: {
                width: '80%'
                }
            },
            chartView: {
                columns: [0, 1]
            }
            }
        }
        });

        var chart = new google.visualization.ChartWrapper({
        chartType: 'LineChart',
        containerId: 'Sleeps_chart_div'
        });

        google.visualization.events.addListener(control, 'statechange', function () {
            var dateRange = control.getState().range;
            var dayInMS =  1 * 24 * 60 * 60 * 1000;
            var periodShown = dateRange.end.getTime() - dateRange.start.getTime();
            if (periodShown <= dayInMS) {
                chart.setOption('hAxis.format', 'H:MM:SS');
            } else {
                chart.setOption('hAxis.format', 'dd/MM/yyyy');
            }
            chart.draw();
        });

        function setOptions(wrapper) {
        wrapper.setOption('height', '400');
        wrapper.setOption('width', '100%');
        wrapper.setOption('animation.duration', 0);
        wrapper.setOption('hAxis.format', 'dd/MM/yyyy');
        }

        setOptions(chart);

        dash.bind([control], [chart]);
        dash.draw(data);
    },
    packages: ['controls', 'corechart']
    });
}


function ShowBPMChart()
{
    if(BPMLoad)
    {
        return;
    }
    BPMLoad=true;

    
    google.charts.load('current', {
    callback: function () {
        var data = google.visualization.arrayToDataTable([
        ['Data','BPMs'],
                <?php
                    $BPMs=$HB-> GetDBBPMyID($_GET["ID"]);
                    if($BPMs!=null)
                    {
                        foreach($BPMs as $k=>$v)
                        {
                            echo "[new Date(\"$v[Data]\"), $v[BPM]],\r\n";
                        }
                    }
               ?>
        ]);

        var Number=<?php echo $BPMs==null?0:count($BPMs);    ?>;
        if(Number==0)
        {
            $("#BPM_control_div").html("<h2 style='color:red'>Non ci sono dati nel DB</h2>");
            return;
        }

        var dash = new google.visualization.Dashboard(document.getElementById('dashboard'));

        var control = new google.visualization.ControlWrapper({
        controlType: 'ChartRangeFilter',
        containerId: 'BPM_control_div',
        options: {
            filterColumnIndex: 0,
            ui: {
            chartOptions: {
                height: '100',
                width: '70%',
                chartArea: {
                width: '80%'
                }
            },
            chartView: {
                columns: [0, 1]
            }
            }
        }
        });

        var chart = new google.visualization.ChartWrapper({
        chartType: 'LineChart', //ColumnChart
        containerId: 'BPM_chart_div'
        });

        google.visualization.events.addListener(control, 'statechange', function () {
            var dateRange = control.getState().range;
            var dayInMS =  1 * 24 * 60 * 60 * 1000;
            var periodShown = dateRange.end.getTime() - dateRange.start.getTime();
            if (periodShown <= dayInMS) {
                chart.setOption('hAxis.format', 'H:MM:SS');
            } else {
                chart.setOption('hAxis.format', 'dd/MM/yyyy');
            }
            chart.draw();
        });

        function setOptions(wrapper) {
        wrapper.setOption('height', '400');
        wrapper.setOption('width', '100%');
        wrapper.setOption('animation.duration', 0);
        wrapper.setOption('hAxis.format', 'dd/MM/yyyy');
        }

        setOptions(chart);

        dash.bind([control], [chart]);
        dash.draw(data);
    },
    packages: ['controls', 'corechart']
    });
}


       function ClickNavBind()
       {
            $("a.nav-link").click(function()
            {
                var IdTab=$(this).attr("id_Tab");
                $("#TabsContainer .active").removeClass("active");
                $("#"+IdTab).addClass("active");
                
                $(".nav-tabs .active").removeClass("active");
                $(this).parent().addClass("active");
                
                var Func=$(this).attr("show_func");
                eval(Func);
            });
       }            
       
       var StepsLoad=false;
       var SleepLoad=false;
       var BPMLoad=false;

    </script>
    </head>  

    <body> 
            <br>
                <h1 id="NameSurname" align="center"> 
                Report di: <?php 
                    $User=$HB->GetPatient($_GET["ID"]);
                    echo $User["Surname"]." ".$User["Name"];
                ?>
                </h1>
               
            <br>
    
            <ul class="nav nav-tabs" id="myTab">
            <li class="active">
                <a class="nav-link" href="#" id_Tab="StepsTab" show_func="ShowStepsChart();">STEPS</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id_Tab="SleepTab" show_func="ShowSleepChart();">SLEEP</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id_Tab="BPMTab" show_func="ShowBPMChart();">BPM</a>
            </li>
            </ul>
        
            <div id="TabsContainer">
                <div id="StepsTab" class="active">
                    <div id="Steps_chart_div"></div>
                    <div id="Steps_control_div"></div>
                    
                </div>
                <div id="SleepTab">
                <p align="center"><b>Tipologie di sonno:</b><i> 72 Sonno - 109 Sonno leggero - 110 Sonno profondo - 111 REM - 112 Sveglio (durante ciclo di sonno)</i></p>
                    <div id="Sleeps_chart_div"></div>
                    <!-- Mettere legenda sonno sotto Types -> 72 Sonno, 109 Sonno leggero, 110 Sonno profondo, 111 REM, 112 Sveglio (durante ciclo di sonno) -->
                    <div id="Sleeps_control_div"></div>                 
                </div>
                <div id="BPMTab" >
                    <div id="BPM_chart_div"></div>
                    <div id="BPM_control_div"></div>
                </div>
                <br>
                <p align="center">È possibile manipolare il grafico inferiore (evidenziato da due cursori laterali) per selezionare uno specifico slot temporale ed analizzarlo nel dettaglio.
                   <br>Inoltre è possibile spostare la finestra temporale così creata lungo tutto il grafico per mantenere lo stesso periodo di tempo che si vuole analizzare.</p>
            </div>
       
    </body>


</html>