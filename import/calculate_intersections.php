<?php

/**
*
* Intersections Calculator
*
* Created by Nick Stallman <nick@nickstallman.net>
* 
*/


// Settings

$set_osm_filename = 'uk-090930.osm';
$set_db_server = ':/var/run/mysqld/mysqld.sock';   // use a socket if on localhost! location varies, see socket setting in mysql's my.conf
$set_db_user = 'root';
$set_db_pass = '';
$set_db_db = '';
$set_log_interval = 100000;

// End of settings

if (!mysql_connect($set_db_server, $set_db_user, $set_db_pass))
	die("Error connecting to database\n\n");

if (!mysql_select_db($set_db_db))
	die("Error selecting database '$set_db_db'\n\n"); 

$way_cache = array();

$result = mysql_query('SELECT node_id FROM nodes');
$count = mysql_num_rows($result);
$done = 0;

while ($row = mysql_fetch_array($result))
{
	$done++;

	$result2 = mysql_query('SELECT way_id FROM nodes_to_ways WHERE node_id = '.$row['node_id']);
	if (mysql_num_rows($result2) > 1)
	{
		$ways = array();
		while ($row2 = mysql_fetch_array($result2))
		{
			$ways[] = $row2['way_id'];
		}
		sort($ways);

		$c = count($ways);
		for ($i = 0; $i < $c; $i++)
		{
			for ($j = $i + 1; $j < $c; $j++)
			{
				$way_cache[$ways[$i]][$ways[$j]] = 1;
			}
		}
	}
}

$inserts = array();
$inserts_count = 0;
foreach ($way_cache as $way_1 => $ways)
{
	foreach ($ways as $way_2 => $num)
	{
		$inserts[] = "($way_1, $way_2)";
		$inserts_count++;
	}

	if ($inserts_count > 10000)
	{
		mysql_query('INSERT INTO intersections VALUES '.implode(',', $inserts));
		$inserts = array();
		$inserts_count = 0;
	}
}

mysql_query('INSERT INTO intersections VALUES '.implode(',', $inserts));