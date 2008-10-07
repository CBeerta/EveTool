<?php
/**************************************************************************
	PHP Api Lib Alliance List Class
	Portions Copyright (C) 2008 Gordon Pettey
	Portions Copyright (c) 2008 Thorsten Behrens

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


class Alliancelist 
{
	static function getAllianceList($contents) 
	{
		if (!empty($contents) && is_string($contents)) 
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			foreach ($xml->result->rowset->row as $row) 
			{ // Alliances
				$output['id'][(string) $row['allianceID']] = (string) $row['name'];
				foreach ($row->rowset->row as $row2) 
				{ // Corporations
					$index = count($output[(string) $row['name']]);
					$output[(string) $row['name']][$index]['corporationID'] = array();
					foreach ($row2->attributes() as $key => $val) 
					{
						$output[(string) $row['name']][$index][(string) $key] = (string) $val;
					}
				}
			}
			unset ($xml); // manual garbage collection
			return ($output);
		}
		else
		{
			return null;
		}
	}
}
?>