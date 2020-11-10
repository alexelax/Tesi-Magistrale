<div class="sx"> 



<?php



if( isset($_GET["Nome"]))

{

	$zip = new ZipArchive;

	$res = $zip->open($_GET["Nome"]);

	if ($res === TRUE) 

	{

		try 

		{

			$zip->extractTo('./');

			$zip->close();

			echo 'Completato con successo!';

		}

		catch (Exception $e) 

		{

			echo "0".$e->getMessage();

		}

	} 

	else 

	{

	  echo '0ERRORE!';

	}

	return;

}

else

{

	//echo "Passa a questa pagina la variabile 'Nome' in modalità GET, Scrivendo solo il nome del file (senza l'estensione .zip)";

	echo "DoubleClick sul file da unzippare<br>";

	echo "<ul class='ListFile'>";

	$d=opendir('./');

	 while (false !== ($entrada = readdir($d))) 

	{

		if ($entrada != "." && $entrada != ".." && $entrada!=basename(__FILE__) && pathinfo($entrada, PATHINFO_EXTENSION)=="zip") 

		{

			echo "<li lnk='".basename($entrada)."'>".basename($entrada)."</li>";

		}

	}

	echo "</ul>";

}



?>

</div>

<style>

	ul

	{

		width:500px;

	}

	li

	{

		list-style: none;

		padding:2px;

		border:1px solid black;

		margin-bottom:4px;

		cursor: pointer;

	}

	li.unzipping

	{

		background-color: #FFE0B2;

		cursor: default;

	}

	li.unzipping:hover

	{

		background-color: #FFE0B2;

		cursor: default;

	}

	

	li.unzipped

	{

		background-color: #B3FFB6;

		cursor: default;

	}

	li.unzipped:hover

	{

		background-color: #B3FFB6;

		cursor: default;

	}

	

	li.Errunzipped

	{

		background-color: #FC9999;

		cursor: default;

	}

	li.Errunzipped:hover

	{

		background-color: #FC9999;

		cursor: default;

	}

	

	

	li:hover

	{

		background-color:#FFFFDB;

	}

	.dx

	{

		float:left;

		width:500px;

		-webkit-touch-callout: none;

		-webkit-user-select: none;

		-khtml-user-select: none;

		-moz-user-select: none;

		-ms-user-select: none;

		user-select: none;

	}

	.sx

	{

		float:left;

		width:700px;

		-webkit-touch-callout: none;

		-webkit-user-select: none;

		-khtml-user-select: none;

		-moz-user-select: none;

		-ms-user-select: none;

		user-select: none;

	}

	.dx textarea

	{

		width:400px;

		height:200px;

		

	}

	

</style>

<script src="jquery-1.9.1.js"></script>

<script>

$(document).ready(function(){

	$(".ListFile li").dblclick(function()

	{

		$("#textStatus").val("");

		if( !$(this).hasClass("unzipped"))

		{

			var d=document.createElement("div");

			$(this).addClass("unzipping");

			var liClick=this;

			$(d).load("<?php echo basename(__FILE__); ?>",jQuery.param({Nome:$(this).attr("lnk")}),function(){

				var Strtrim=$.trim($(d).text());

				var stat=Strtrim.substring(0,1);				

				var txt=Strtrim.substring(1,Strtrim.length);

				TestoStatus=txt;

				$(liClick).removeClass("unzipping");

				finito=1;

				if(stat=="1")

				{

					$(liClick).addClass("unzipped");

				}

				else

					$(liClick).addClass("Errunzipped");

				

			});

			LoopWaitUnzip();

		}

	});

});

var finito=0;

//  0 -> non finito

//  1 -> finito 



var MilliSecLoop=500;

var TestoStatus=""

function LoopWaitUnzip()

{

	if(finito==0)

	{

		$("#textStatus").val($("#textStatus").val()+" . ");

		window.setTimeout(LoopWaitUnzip,MilliSecLoop);

	}

	else

	{

		$("#textStatus").val($("#textStatus").val()+"\r\n"+TestoStatus);

	}

}



</script>

<div class="dx">

Status:<br>

<textarea id="textStatus"></textarea>

</div>