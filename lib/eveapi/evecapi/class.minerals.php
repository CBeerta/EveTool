<?php
/**************************************************************************
	PHP Api Lib Eve Central MarketStat Class
	Copyright (c) 2008 Dustin Tinklin

	This file is part of PHP Api Lib.

	PHP Api Lib is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	PHP Api Lib is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with PHP Api Lib.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

class Minerals
{
	function getMinerals($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	       	$output = array();
		// no xml line or initial tags, adding.
		$contents = '<?xml version="1.0" encoding="utf-8" ?>' ."\n" .'<evec_api version="2.0" method="minerals_xml">' ."\n" . $contents .'</evec_api>';
	 		$xml = new SimpleXMLElement($contents);
			foreach ($xml->minerals->children() as $min)
			{
				$index = count($output);
				foreach($min->children() as $key=>$value)
				{
					$output[$index][(string) $key] = (string) $value;
				}
			}
			unset ($xml); // manual garbage collection
			return $output;
		}
		else
		{
			return null;
		}
	}
}
?>
