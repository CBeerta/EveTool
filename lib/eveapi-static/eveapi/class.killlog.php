<?php
/**************************************************************************
	PHP Api Lib KillLog Class
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

class KillLog
{
	function getKillLog($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	       	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $name => $value)
				{
					$output[$index][(string) $name] = (string) $value;
					foreach  ($row->victim->attributes() as $name => $value)
					{
						$output[$index]['victim'][(string) $name] = (string) $value;
					}
					foreach ($row->rowset as $srow)
					{
						$rowatts = $srow->attributes();
						$rowname = $rowatts['name'];
						foreach ($srow->row as $arow)
						{
							$aindex = count($output[$index][(string)$rowname]);
							foreach ($arow->attributes() as $aname => $avalue)
							{
								$output[$index][(string) $rowname][$aindex][(string) $aname] = (string) $avalue;
							}
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