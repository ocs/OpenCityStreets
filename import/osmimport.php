<?php

/**
*
* OpenStreetMap XML Dump Importer
*
* Created by Nick Stallman <nick@nickstallman.net>
* TagFinder() and minor changes by Aaron Wolfe
* 
*/

$reader = new XMLReader();
$reader->open('uk-090930.osm');

mysql_connect('', 'root', 'radnor22');
mysql_select_db('ocs');

$things = 0;
$start_time = time();
$tagfinder = new TagFinder();

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
		$things++;
		if ($things % 100000 == 0)
			print_perf($things, $start_time);

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

		mysql_query("INSERT INTO `nodes` (`node_id`, `node_lat`, `node_lng`, `node_version`) VALUES ({$node['id']}, {$node['lat']}, {$node['lng']}, {$node['version']})");

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
		$things++;
		if ($things % 100000 == 0)
			print_perf($things, $start_time);

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

mysql_query("UNLOCK TABLES");

echo "\nThat took ".time() - $start_time . " seconds.\n";
echo "Done with ".memory_get_peak_usage()." used\n";

class TagFinder
{
	public $tagcache = array();
	public $hit = 0;
	public $miss = 0;

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

function print_perf($things, $started)
{
	$persec = round($things / (time() - $started));
	echo "$things: $persec/sec\n";
}
