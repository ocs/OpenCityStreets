<?php

/**
*
* OpenStreetMap XML Dump Importer
*
* Created by Nick Stallman <nick@nickstallman.net>
* TagFinder() and minor awesome changes by Aaron Wolfe
* 
*/


// Settings

$set_osm_filename = 'uk-090930.osm';
$set_db_server = ':/var/run/mysqld/mysqld.sock';   // use a socket if on localhost! location varies, see socket setting in mysql's my.conf
$set_db_user = 'root';
$set_db_pass = 'hmmm';
$set_db_db = 'osm1';
$set_log_interval = 100000;

// End of settings


$reader = new XMLReader();
if (!$reader->open($set_osm_filename)) 
	die("Error opening OSM file\n\n"); 

if (!mysql_connect($set_db_server,$set_db_user,$set_db_pass)) 
	die("Error connecting to database\n\n"); 

if (!mysql_select_db($set_db_db)) 
	die("Error selecting database '$set_db_db'\n\n"); 

$nodes_in = 0;
$ways_in = 0;

$tagfinder = new TagFinder();
$runtimer = new RunTimer();

mysql_query("LOCK TABLES nodes, nodes_to_ways, tags, tags_to_nodes, tags_to_ways, ways WRITE");

while ($reader->read())
{
	if (in_array($reader->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE)) && $name!='')
	{
		continue;
	}

	/*if ($reader->name == 'osm')
		continue;*/

	if ($reader->name == 'node')
	{
		$nodes_in++;
		if (($nodes_in + $ways_in) % $set_log_interval == 0)
			$runtimer->log_interval($nodes_in, $ways_in, $tagfinder->hits(), $tagfinder->misses());

		$node = array();
		$tags = array();
		$node['id'] = $reader->getAttribute('id');
		$node['lat'] = $reader->getAttribute('lat');
		$node['lng'] = $reader->getAttribute('lon');
		$node['version'] = $reader->getAttribute('version');

		if (!$reader->isEmptyElement)
		{
			while ($reader->read())
			{
				if ($reader->nodeType == XMLReader::END_ELEMENT)
					break;

				if ($reader->name == 'tag')
				{
					$v = strtolower(trim($reader->getAttribute('v')));
					$k = strtolower(trim($reader->getAttribute('k')));

					if (empty($v))
						continue;

					switch ($k)
					{
						case 'wikipedia:en':
						case 'postal_code':
						case 'tourism':
						case 'icao':
						case 'iata':
						case 'cuisine':
						case 'denomination':
						case 'sport':
						case 'highway':
						case 'leisure':
						case 'natural':
						case 'barrier':
						case 'religion':
						case 'historic':
						case 'amenity':
						case 'shop':
							$bits = explode(';', $v);
							foreach ($bits as $bit)
								$tags[] = $tagfinder->find_tag($k, trim($v));
							break;
					}
				}
			}
		}

		mysql_query("INSERT INTO `nodes` (`node_id`, `node_lat`, `node_lng`, `node_version`, `node_pt`) VALUES ({$node['id']}, {$node['lat']}, {$node['lng']}, {$node['version']}), GeomFromText('POINT({$node['lat']} {$node['lng']})'))");

		if (!empty($tags))
		{
			$query = 'INSERT INTO `tags_to_nodes` (`tag_id`, `node_id`) VALUES ';

			foreach ($tags as $tag)
			{
				$query .= '('.$tag.', '.$node['id'].'), ';
			}

			mysql_query(substr($query, 0, -2));
		}

	} elseif ($reader->name == 'way') {
		$ways_in++;
		if (($nodes_in + $ways_in) % $set_log_interval == 0)
			$runtimer->log_interval($nodes_in, $ways_in, $tagfinder->hits(), $tagfinder->misses());

		$way = array();
		$tags = array();
		$nodes = array();
		$way['id'] = $reader->getAttribute('id');
		$way['name'] = '';
		$way['version'] = trim($reader->getAttribute('version'));

		if (!$reader->isEmptyElement)
		{
			while ($reader->read())
			{
				if ($reader->nodeType == XMLReader::END_ELEMENT)
					break;

				if ($reader->name == 'tag')
				{
					$v = strtolower(trim($reader->getAttribute('v')));
					$k = strtolower(trim($reader->getAttribute('k')));

					if (empty($v))
						continue;

					switch ($k)
					{
						case 'name':
							$way['name'] = esc($v);
							break;
						case 'sport':
						case 'building':
						case 'amentiy':
						case 'highway':
						case 'leisure':
							$bits = explode(';', $v);
							foreach ($bits as $bit)
								$tags[] = $tagfinder->find_tag($k, trim($v));
							break;
					}
				}

				if ($reader->name == 'nd')
					$nodes[] = $reader->getAttribute('ref');
			}
		}

		mysql_query("INSERT INTO `ways` (`way_id`, `way_name`, `way_version`) VALUES ({$way['id']}, '{$way['name']}', {$way['version']})");

		if (!empty($nodes))
		{
			$query = 'INSERT INTO `nodes_to_ways` (`node_id`, `way_id`, `order`) VALUES ';

			foreach ($nodes as $order => $node)
			{
				$query .= '('.$node.', '.$way['id'].', '.$order.'), ';
			}

			mysql_query(substr($query, 0, -2));
		}

		if (!empty($tags))
		{
			$query = 'INSERT INTO `tags_to_ways` (`tag_id`, `way_id`) VALUES ';

			foreach ($tags as $tag)
			{
				$query .= '('.$tag.', '.$node['id'].'), ';
			}

			mysql_query(substr($query, 0, -2));
		}
	}
}

$reader->close();

mysql_query("UNLOCK TABLES");
mysql_close();

echo 'Done in ' . $runtimer->get_runtime() . ' seconds, using ' . round(memory_get_peak_usage() / 1024) . "KB.\n";



class TagFinder
{
	private $tagcache = array();
        private $hit = 0;
        private $miss = 0;

	public function hits()
	{
		return $this->hit;
	}

	public function misses()
	{
		return $this->miss;
	}
	
	public function find_tag($name, $value)
	{
		if (isset($this->tagcache[$name."|".$value]))
		{
			$this->hit++;
			return $this->tagcache[$name.'|'.$value];
		} else {
			$this->miss++;
			mysql_query('INSERT INTO `tags` (`tag_name`, `tag_value`) VALUES (\''.esc($name).'\', \''.esc($value).'\')');
			$this->tagcache[$name."|".$value] = mysql_insert_id();
			return mysql_insert_id();
		}
	}
}


function esc($data)
{
	return mysql_real_escape_string($data);
}



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
 	 	return(round(($this->microtime_float() - $this->start_time), 2));
	}

	private function microtime_float()
	{
    		list($usec, $sec) = explode(" ", microtime());
    		return ((float)$usec + (float)$sec);
	}

}
