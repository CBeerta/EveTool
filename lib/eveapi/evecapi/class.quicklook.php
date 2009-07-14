<?php
/**************************************************************************
	PHP Api Lib Eve Central Quicklook Class
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

class QuickLook
{
	function getQuickLook($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	       	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			$children = $xml->quicklook->children();
			foreach ($children as $key=>$value)
			{
			//print $key ." = ". $value ."\n"; 
			if($key == 'sell_orders' or $key == 'buy_orders' or $key == 'regions')
			{
			}
			else {
			$output[(string) $key] = (string) $value;
			}
			}
			foreach ($children as $child)
			{
				if($child->getName() == 'sell_orders' or $child->getName() == 'buy_orders' or $child->getName() == 'regions')
				{
					$bsname = (string) $child->getName();
					foreach ($child->children() as $order)
					{
						$index = count($output[$bsname]);
						foreach ($order->attributes() as $name => $value)
						{
							$output[(string) $bsname][$index][(string) $name] = (string) $value;
						}
						foreach ($order->children() as $key=>$value)
						{
						$output[(string) $bsname][$index][(string) $key] = (string) $value;
						}
					}
				if($child->getName() == 'regions')
					{
					$bsname = (string) $child->getName();
					foreach ($child->children() as $key=>$value)
					{
					$output[(string) $bsname][] = (string) $value;
					}
					if(!$child->children())
					{
					$output[(string) $bsname][] = "";
					}
					}
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
