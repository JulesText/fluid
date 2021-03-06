<?php
require_once "ses.php";
require_once "config.php";
$dir=$addon['options']['jpgraphdir'];
require_once "{$dir}jpgraph.php";
require_once "{$dir}jpgraph_line.php";
require_once "{$dir}jpgraph_bar.php";
require_once "{$dir}jpgraph_date.php";

$plots=array();

$sourcedata=$_SESSION["addon_{$addon['id']}"]['graph'];
$barplot= new BarPlot($sourcedata['ybar'],$sourcedata['xbar']);
$barplot->SetWidth(4);

$barplot->SetColor('#FFAAAA');
$barplot->SetFillColor('#FFAAAA');
$barplot->SetLegend($sourcedata['bartitle']);
$plots[]=$barplot;

$colours=array('#202020','#802020','#208020','#202080','#208080');
$i=0;
while (!empty($sourcedata["yline$i"])) {
    $lineplot= new LinePlot($sourcedata["yline$i"],$sourcedata["xline$i"]);
    $lineplot->SetColor($colours[$i]);
    $lineplot->SetWeight(2+$i*2);
    $lineplot->SetBarCenter();
    $lineplot->SetLegend($sourcedata["title$i"]);
    $plots[]=$lineplot;
    $i++;
}

$_SESSION["addon_{$addon['id']}"]['graph']=null;

/* ==============================================
    now format the whole graph
*/
$graph = new Graph(600,400);
$graph->SetScale("datelin");
$graph->img->SetMargin(30,10,50,80);

$graph->xaxis->scale->SetDateFormat('Y-m-d');
$graph->xaxis->scale->ticks->Set(28*24*60*60,7*24*60*60);
$graph->xaxis->scale->SetDateAlign( MONTHADJ_1);
$graph->xaxis->SetLabelAngle(90);
$graph->xgrid->show(true,false);
$graph->xgrid->setColor('#777777','#cccccc');

//$graph->yaxis->scale->ticks->Set(10,5);
$graph->ygrid->show(true,true);
$graph->ygrid->setColor('#777777','#cccccc');

$graph->legend->pos(0.01,0.01,'right','top');
$graph->legend->SetColumns(3);
$graph->legend->SetLineWeight(4);
$graph->legend->SetFont(FF_FONT1,FS_NORMAL);

// Add the plot(s) to the graph
for ($i=0;$i<count($plots);$i++) $graph->Add($plots[$i]);
$graph->Stroke();

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
