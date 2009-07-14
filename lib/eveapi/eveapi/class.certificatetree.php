<?php
/**************************************************************************
	PHP Api Lib SkillTree Class
	Portions Copyright (C) 2007 Kw4h
	Portions Copyright (c) 2008 Thorsten Behrens
	Portions Copyright (c) 2008 Dustin Tinklin

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

class CertificateTree
{
	function descendtree($child)
	{
		foreach ($child->rowset as $rs)
		{
			$rsatts = $rs->attributes();
			$rsname = $rsatts[(string) "name"];
			$key = $rsatt[(string) "key"];
			foreach ($rs->row as $row)
			{
				$rat = $row->attributes();
				$index = $rat[(string) $key];
				foreach ($row->attributes() as $name => $value)
				{
					$co[(string) $rsname][(string) $index][(string) $name] = (string) $value;
				}
				if(count((array)$row->children()) > 1) // children contains @attributes, which we don't care about
				{
					$clatts = $row->rowset->attributes;
					$clid = $clatts[(string) "name"]
					$co[(string) $rsname][(string) $index][(string) $clid] = CertificateTree::descendtree($row->children());
				}

			}
		}
		
		return $co;
	}
							
				
	static function getCertificateTree($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			foreach ($xml->result->rowset as $rs)
			{
				$rsatts = $rs->attributes();
				$rsname = $rsatts[(string) "name"];
				$key = $rsatt[(string) "key"];
				foreach ($rs->row as $row)
				{
					$rat = $row->attributes();
					$index = $rat[(string) $key];
					foreach ($row->attributes() as $name => $value)
					{
						$output[(string) $rsname][($string) $index][(string) $name] = (string) $value;
					}
					if(count((array)$row->children()) > 0) // children contains @attributes, which we don't care about
					{
						$output[(string) $rsname][(string) $index]  = CertificateTree::descendtree($row->children());
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