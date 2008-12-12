<?php
/**************************************************************************
	PHP Api Lib CharacterSheet Class
	Portions Copyright (C) 2007 Kw4h
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

class CharacterSheet
{	
	static function getSkillInTraining($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			
			$output = array();
			foreach ($xml->result->children() as $name => $value)
			{
				$output[(string) $name] = (string) $value;
			}
			unset ($xml); // manual garbage collection			
			return $output;
		}
		else
		{
			return null;
		}
	}
	
	static function getCharacterSheet($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			
			$output = array();
			
			// get the general info of the char
			$output['info'] = array();
			foreach ($xml->result->children() as $name => $value)
			{
				// The enhancers,  attributes and skills will be handled separately further down
				// This is admittedly a bit crude - it might be better to find out whether $value is a nested object(SimpleXMLElement)
				// Then again, this is fast and easy, so, alright
				if ($name == "attributeEnhancers" || $name == "attributes" || $name == "rowset")
					continue;

				$output['info'][(string) $name] = (string) $value;
			}
			
			// get the attributeEnhancers of the char
			$output['enhancers'] = array();
			foreach ($xml->result->attributeEnhancers as $attribute)
			{				
				foreach ($attribute->children() as $name => $value)
				{
					$output['enhancers'][(string) $name] = array();
					
					foreach ($value->children() as $key => $val)
					{
						$output['enhancers'][(string) $name][(string) $key] = (string) $val;
					}
				}
			}
			
			// get the attributes of the char
			$output['attributes'] = array();
			foreach ($xml->result->attributes->children() as $name => $value)
			{
				$output['attributes'][(string) $name] = (int) $value;
			}
			
			// get the actual skills
			$output['skills'] = array();
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output['skills']);
				foreach ($row->attributes() as $name => $value)
				{
					$output['skills'][$index][(string) $name] = (int) $value;
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

// Class to bring getSkillInTraining in line with how all other parsers work
class SkillInTraining
{
	static function getSkillInTraining($contents)
	{
		$output = CharacterSheet::getSkillInTraining($contents);
		
		return $output;
	}
}
?>