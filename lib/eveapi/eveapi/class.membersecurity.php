<?php
/**************************************************************************
	PHP Api Lib MemberSecurity Class
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




class MemberSecurity
{
	function	getMemberSecurity($contents)
	{
		if(!empty($contents) && is_string($contents))
		{
			$output = array();
			$xml = new SimpleXMLElement($contents);
			foreach ($xml->result->member as $member)
			{
				$mindex = count($output);
				foreach($member->attributes() as $mname => $mvalue)
				{
					$output[$mindex][$mname] = (string) $mvalue;
				}
				foreach ($member->rowset as $rs)
				{
					$rsatt = $rs->attributes();
					$rsname =  $rsatt[(string) 'name'];
					foreach ($rs->row as $r)
					{
						$rindex = count($output[$mindex][(string) $rsname]);
						foreach ($r->attributes() as $id => $name)
						{
							$output[$mindex][(string) $rsname][$rindex][ (string) $id] =  (string) $name;
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
