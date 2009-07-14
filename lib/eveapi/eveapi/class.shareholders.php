<?php
/**************************************************************************
	PHP Api Lib ShareHolders Class
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

class ShareHolders
{
	function getShareHolders($contents)
	{
	if (!empty($contents) && is_string($contents))
		{
	        $output = array();
	 		$xml = new SimpleXMLElement($contents);
			foreach($xml->result->rowset as $rowset)
			{
				$att = $rowset->attributes();
				$type = $att['name'];
				foreach ($rowset->row as $row)
				{
					$index = count($output[$type]);
					foreach ($row->attributes() as $name => $value)
					{
						$output[(string) $type][$index][(string) $name] = (string) $value;
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
