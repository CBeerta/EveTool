<?php
/**************************************************************************
	PHP Api Lib Titles Class
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




class Titles
{
	function	getTitles($contents)
	{
		if(!empty($contents) && is_string($contents))
		{
			$output = array();
			$xml = new SimpleXMLElement($contents);
			foreach ($xml->result->rowset->row as $rs)
			{
				$index = count($output);
				foreach($rs->attributes() as $name => $value)
					{
					$output[$index][$name] =  (string) $value;
					}
				foreach ($rs->rowset as $rs1)
				{
					$rsatt = $rs1->attributes();
					$rsname = $rsatt[(string) 'name'];
					foreach ($rs1->row as $r)
					{
					$rsindex = count($output[$index][(string) $rsname]);
						foreach ($r->attributes() as $id => $rname)
						{
						$output[$index][(string) $rsname][$rsindex][(string) $id] = (string) $rname;
						}
					}
				}
			}
			unset($xml);
			return $output;
		}
		else
		{
			return null;
		}
	}
}

?>
