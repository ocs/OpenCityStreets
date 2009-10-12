<?php

/**
*
* Draw some info for a box, useful for timing queries
*
*  You will need php gd libs
*
* Hastily thrown together by Aaron
*
*
*/


// Settings

$set_db_server = ':/var/run/mysqld/mysqld.sock';   // use a socket if on localhost! location varies, see socket setting in mysql's my.conf
$set_db_user = 'root';
$set_db_pass = '';
$set_db_db = 'osm1';

// define your box
$set_lat1 = 51;
$set_lng1 = 0;

$set_lat2 = 51.3;
$set_lng2 = 0.3;

$set_img_file = 'test.png';
$set_img_width = 1000;
$set_img_height = $set_img_width * ( abs($set_lat2 - $set_lat1) / abs($set_lng2 - $set_lng1) );


// End of settings

if (!mysql_connect($set_db_server, $set_db_user, $set_db_pass))
        die("Error connecting to database\n\n");

if (!mysql_select_db($set_db_db))
        die("Error selecting database '$set_db_db'\n\n");

$timer = new RunTimer();

$result = mysql_query("SELECT way_id, AsText(way_geom) as way_geom FROM ways WHERE MBRIntersects(GeomFromText('Polygon(({$set_lat1} 
{$set_lng1},{$set_lat2} {$set_lng1},{$set_lat2} {$set_lng2},{$set_lat1} {$set_lng2},{$set_lat1} {$set_lng1}))'), way_geom)");

$count = mysql_num_rows($result);
$selecttime = $timer->get_runtime();

echo "Time to execute select: $selecttime seconds.  $count records returned.\n";

$im = imagecreate($set_img_width,$set_img_height);

$bgcolor = imagecolorallocate($im, 255,255,255);
$node_color = imagecolorallocate($im, 0,0,0);


while ($row = mysql_fetch_array($result))
{
 $points = explode(",",substr($row['way_geom'], 11, -1)); 

 $lastx = 0;
 $lasty = 0;

 for ($loop=0;$loop < sizeof($points);$loop++)
 {
  $lldata = explode(" ",$points[$loop]);

  $x = intval( ($lldata[1] - $set_lng1) * ($set_img_width/($set_lng2 - $set_lng1)) );
  $y = $set_img_height - intval( ($lldata[0] - $set_lat1) * ($set_img_height/(abs($set_lat2 - $set_lat1))) );

  if ($loop>0)
  {
   imageline($im,$lastx,$lasty,$x,$y,$node_color);      
  }

  $lastx = $x;
  $lasty = $y;
 }

}


# there must be a better way to do this...
$txt = "Area: (".$set_lat1.",".$set_lng1.") to (".$set_lat2.",".$set_lng2 .")  $count ways in $selecttime seconds";
for ($x = 2;$x <= 6;$x++)
{
 for ($y = 2;$y <= 6;$y++)
 {
  imagestring($im,4,$x,$y,$txt, $node_color);
 }
}

imagestring($im,4,4,4,$txt, 0);

imagepng($im,$set_img_file,0,NULL);





class RunTimer
{
 private $start_time = 0;
 private $last_time = 0;
 private $last_things = 0;
 private $last_tfhit = 0;
 private $last_tfmiss = 0;
 
 function __construct()
 {
  $this->start_time = $this->microtime_float();
  $this->last_time = $this->start_time;
 }
 
 
 public function log_interval($nodes_in, $ways_in, $tfhit, $tfmiss)
 {
  $now = $this->microtime_float();
  $things = $nodes_in + $ways_in;
 
  $persec_overall = round($things / ($now - $this->start_time));
  $persec_interval = round(($things - $this->last_things) / ($now - $this->last_time));
  $tagcache_overall = round($tfhit / ($tfhit + $tfmiss) * 100, 3);
  $tagcache_interval = round(($tfhit - $this->last_tfhit) / (($tfhit + $tfmiss) - ($this->last_tfhit + $this->last_tfmiss)) * 100, 3);
 
  echo "nodes: $nodes_in\tways: $ways_in\tspeed: $persec_overall/sec ($persec_interval/sec)\ttags cached: $tagcache_overall% ($tagcache_interval%)\n";
 
  $this->last_time = $now;
  $this->last_things = $things;
  $this->last_tfhit = $tfhit;
  $this->last_tfmiss = $tfmiss;
 
 }
 
 public function get_runtime()
 {
  return(round(($this->microtime_float() - $this->start_time), 6));
 }
 
 private function microtime_float()
 {
     list($usec, $sec) = explode(" ", microtime());
     return ((float)$usec + (float)$sec);
 }
 
}
