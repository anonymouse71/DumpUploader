<?php
set_time_limit(0);
$start = microtime(true);
require_once 'func.php';
//sql file
$filename = "saiful_str_2014_06_28_06_00_02.sql";
//database config
define('HOST', 'localhost');
define('DBNAME', 'dump');
define('DBUSER', 'root');
define('DBPASS', '');
try{
	$db=new PDO('mysql:host='.HOST.';dbname='.DBNAME.';charset=utf8',DBUSER,DBPASS);
	echo "<br>Connected With MySQL Server<br>";
}
catch (Exception $e)
{
 	throw new Exception( 'Something wrong', 0, $e);
}

$tables = '';
$rows = '';
$templine = '';
$lines = file($filename);
foreach ($lines as $key => $line)
{
	if (substr($line, 0, 2) == '--' || $line == '') {continue;}
	$templine .= $line;
	if (substr(trim($line), -1, 1) == ';')
	{
		if(preg_match("/CREATE TABLE/", $templine))
		{
			preg_match("/`[a-zA-Z0-9]+`/", $templine, $matches);
			$matches = str_replace('`','',$matches);
			$tables[] = $templine;
			echo "Table Found :: $matches[0]<br>";
		}
		if(preg_match("/INSERT INTO/", $templine))
		{
			$tableName = between ('INTO ', ' VALUES', $templine);
			$rows[$tableName][] = '('.between ('VALUES(', ');', $templine).')';
		}
	$templine = '';
	}

}

echo "All Table and Rows Saved into array!<br>";

foreach ($tables as $key => $table) 
{
	preg_match("/`[a-zA-Z0-9]+`/", $table, $matches);
	$t_name = str_replace('`','',$matches[0]);
	echo "Creating Table $t_name<br>";
	$query = $db -> prepare($table);
	$query -> execute();
	$track = $query->errorInfo();
	if($track[0]=='00000') echo "Table Created successfully<br>";
	else echo $track[2]."<br>";
	

	// create data insert query
	if(count($rows[$t_name])>0)
	{
		$sqlQuery = "INSERT INTO $t_name VALUES ".implode(' , ',$rows[$t_name]);
		$query = $db -> prepare($sqlQuery);
		$query -> execute();
		echo $query->rowCount(). " Row Inserted In Table '$t_name'<br>";
	}
}
$timeNeeded = microtime(true) - $start;
echo "Complited in ".$timeNeeded." Second<br>";
?>