<?php

/**
*
* Intersections Calculator
*
* Created by Nick Stallman <nick@nickstallman.net>
* Minor awesome changes by Aaron - Believe It.
*
*/



// Settings

$set_db_server = ':/var/run/mysqld/mysqld.sock';   // use a socket if on localhost! location varies, see socket setting in mysql's my.conf
$set_db_user = 'root';
$set_db_pass = 'noitsnotmypassword';
$set_db_db = 'osm1';

// adjust interval and memory_limit together. 100000 to 64MB is tight but works for UK data
$set_log_interval = 100000;
ini_set("memory_limit","64M");


// End of settings

if (!mysql_connect($set_db_server, $set_db_user, $set_db_pass))
        die("Error connecting to database\n\n");

if (!mysql_select_db($set_db_db))
        die("Error selecting database '$set_db_db'\n\n");

$start_time = time();
$way_cache = array();

echo "Selecting data...\n";

$result = mysql_query('SELECT node_id,GROUP_CONCAT(DISTINCT way_id) as ways FROM nodes_to_ways GROUP BY node_id HAVING COUNT(node_id)>1');
$count = mysql_num_rows($result);
$done = 0;

echo "Time to execute select: " . (time() - $start_time) . " seconds.  $count records returned.\n";

while ($row = mysql_fetch_array($result))
{
        $done++;
 

        $ways = explode(",", $row['ways']);
        sort($ways);

        $c = count($ways);
        for ($i = 0; $i < $c; $i++)
        {
                for ($j = $i + 1; $j < $c; $j++)
                {
                        $way_cache[intval($ways[$i])][intval($ways[$j])] = 1;
                }
        }


 
        if ($done % $set_log_interval === 0)
        {
                echo "Done $done. ".round($done / (time() - $start_time))."/sec\t".round($done / $count * 100, 2)."%\t" . round(memory_get_peak_usage() 
/ 1024) . "KB\tcache size: " . sizeof($way_cache) . "  Inserting";
                do_inserts($way_cache);
        }
}

echo "Final inserts";
do_inserts($way_cache);

echo "\nDone in " . (time() - $start_time) . " seconds.\n";



function do_inserts(&$way_cache)
{ 
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
                        mysql_query('INSERT INTO ways_intersections VALUES '.implode(',', $inserts));
                        $inserts = array();
                        $inserts_count = 0;
                        echo '.';
                }
        }

        if ($inserts_count)
                mysql_query('INSERT INTO ways_intersections VALUES '.implode(',', $inserts));

        $way_cache = array();
        echo "\n";
}

