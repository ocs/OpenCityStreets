<?php

/**
*
* Find the nodes in a way, store them in a LINESTRING for spatial searching
*
* Created by Aaron
* slow but works, will optimize queries later
*
*/


// Settings

$set_db_server = ':/var/run/mysqld/mysqld.sock';   // use a socket if on localhost! location varies, see socket setting in mysql's my.conf
$set_db_user = 'root';
$set_db_pass = '';
$set_db_db = 'osm1';

// End of settings

if (!mysql_connect($set_db_server, $set_db_user, $set_db_pass))
        die("Error connecting to database\n\n");

if (!mysql_select_db($set_db_db))
        die("Error selecting database '$set_db_db'\n\n");

$start_time = time();

$result = mysql_query('SELECT way_id FROM ways');

$count = mysql_num_rows($result);
$done = 0;

echo "$count ways.\n";


while ($row = mysql_fetch_array($result))
{
        $done++;

        $res2 = mysql_query("SELECT nodes.node_lat,nodes.node_lng FROM nodes_to_ways 
                      LEFT JOIN nodes ON (nodes.node_id = nodes_to_ways.node_id) 
                      WHERE nodes_to_ways.way_id = {$row['way_id']} ORDER BY nodes_to_ways.order");

        $nqry = "";

        while ($node = mysql_fetch_array($res2))
        {
                $nqry .= $node['node_lat'] . ' ' . $node['node_lng'] . ',';
        }

        mysql_query("UPDATE ways SET way_geom = GeomFromText('LINESTRING(" . substr($nqry,0,-1) . ")') WHERE way_id = {$row['way_id']}");

        if ($done % 10000 == 0) { echo round($done/$count*100,2)."% done.\n"; }

}

echo "\n\ndone in ".(time() - $start_time)." seconds.\n";
