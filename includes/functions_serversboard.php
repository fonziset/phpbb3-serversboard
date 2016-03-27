<?php
namespace token07\serversboard\includes;

if (!defined('IN_PHPBB'))
{
	exit;
}

function get_supported_protocols()
{
	// Modified from vendor/austinb/gameq/examples/list.php
	$protocols = array();
	$protocols_path = __DIR__ . "/../vendor/austinb/gameq/src/GameQ/Protocols/";

	// Grab the dir with all the classes available
	$dir = dir($protocols_path);

	$protocols = [];

	// Now lets loop the directories
	while (false !== ($entry = $dir->read()))
	{
		if (!is_file($protocols_path . $entry))
		{
			continue;
		}

		// Lets get some info on the class
		$reflection = new \ReflectionClass('\\GameQ\\Protocols\\' . pathinfo($entry, PATHINFO_FILENAME));

		// Check to make sure we can actually load the class
		if (!$reflection->IsInstantiable()) {
			continue;
		}
		$class = $reflection->newInstance();
		$protocols[ $class->name() ] = [
			'class' => '\\GameQ\\Protocols\\' . pathinfo($entry, PATHINFO_FILENAME),
			'name'  => $class->nameLong(),
			'short'	=> $class->name(), // Sorting causes key name to get lost
			'parent' => '\\' . get_parent_class($class),
			'protocol'	=> $class->getProtocol(),
		];
		unset($class);
	}
	// Sort each server type by the protocol
	usort($protocols, function($a, $b) { return $a['protocol'] > $b['protocol']; } );
	
	return $protocols;
}
