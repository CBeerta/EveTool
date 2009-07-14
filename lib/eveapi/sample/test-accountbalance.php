<?php
/**************************************************************************
	PHP Api Lib
	Copyright (c) 2008 Thorsten Behrens

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
require_once('./classes/eveapi/class.api.php');
require_once('./classes/eveapi/class.characters.php');
require_once('./classes/eveapi/class.accountbalance.php');

require_once('./print-as-html.php');
require_once('./config.php');

$api = new Api();
$api->debug(true);
$api->cache(true); // that's the default, done for testing purposes
$api->setTimeTolerance(5); // also the default value
$api->setCredentials($apiuser,$apipass);
$apicharsxml = $api->getCharacters();
$apichars = Characters::getCharacters($apicharsxml);

// Find the character I'm interested in

foreach($apichars as $index => $thischar)
{
	if($thischar['charname']==$mychar)
	{
		$apichar=$thischar['charid'];
		$apicorp=$thischar['corpid'];
	}
}

// Set Credentials
$api->setCredentials($apiuser,$apipass,$apichar);

print ("<P>Raw char balance output</P>");
$dataxml = $api->getAccountBalance();
$data = AccountBalance::getAccountBalance($dataxml);
print_as_html(print_r($data,TRUE));

$blnc = new Balance($apiuser,$apipass,$apichar);
$balanceold = $blnc->getBalance();
if ($data == $balanceold)
	print ("<P>Legacy char balance function matches new balance function output.</P>");
else
	print ("<P>ERROR: Legacy char balance function output broken!</P>");

unset ($dataxml,$data,$blnc,$balanceold);

print ("<P>Raw corp balance output</P>");
$dataxml = $api->getAccountBalance(true);
$data = AccountBalance::getAccountBalance($dataxml);
print_as_html(print_r($data,TRUE));

$blnc = new Balance($apiuser,$apipass,$apichar);
$balanceold = $blnc->getBalance(true);
if ($data == $balanceold)
	print ("<P>Legacy corp balance function matches new balance function output.<P>");
else
	print ("<P>ERROR: Legacy corp balance function output broken!</P>");

unset ($dataxml,$data,$blnc,$balanceold);

$api->printErrors();
?>