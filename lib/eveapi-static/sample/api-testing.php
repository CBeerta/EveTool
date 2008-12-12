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
header("Content-type: text/plain");
require_once('./classes/eveapi/class.api.php');
require_once('./classes/eveapi/class.alliancelist.php');
require_once('./classes/eveapi/class.balance.php');
require_once('./classes/eveapi/class.charactersheet.php');
require_once('./classes/eveapi/class.charselect.php');
require_once('./classes/eveapi/class.corporationsheet.php');
require_once('./classes/eveapi/class.generic.php');
require_once('./classes/eveapi/class.membertrack.php');
require_once('./classes/eveapi/class.transactions.php');
require_once('./classes/eveapi/class.walletjournal.php');
require_once('./classes/eveapi/class.starbases.php');
require_once('./classes/eveapi/class.assetlist.php');
require_once('./classes/eveapi/class.industryjobs.php');

require_once('./config.php');

$api = new Api();
$api->debug(true);
$api->cache(true); // that's the default, done for testing purposes
$api->setTimeTolerance(5); // also the default value
$api->setCredentials($apiuser,$apipass);
$apicharsxml = $api->getCharacters();
$apichars = CharSelect::getCharacters($apicharsxml);

$apicharsnew = Characters::getCharacters($apicharsxml);
if ($apichars == $apicharsnew)
	print ("\n\nNew character selection function matches legacy character selection function output.\n\n");
else
	print ("\n\nERROR: New character selection function output broken!\n\n");

// Find the character I'm interested in

foreach($apichars as $index => $thischar)
{
	if($thischar['charname']==$mychar)
	{
		$apichar=$thischar['charid'];
		$apicorp=$thischar['corpid'];
	}
}


// Set Credentials and grab the list of members of my corporation
$api->setCredentials($apiuser,$apipass,$apichar);
$membersxml = $api->getMemberTracking();
$members = MemberTrack::getMembers($membersxml);
print ("Here's the raw array output of your corp members, $mychar. Enjoy!\n\n");
print_r($members);

$membersnew = MemberTracking::getMemberTracking($membersxml);
if ($members == $membersnew)
	print ("\n\nNew member tracking function matches legacy member tracking function output.\n\n");
else
	print ("\n\nERROR: New member tracking function output broken!\n\n");

$refid = 0; // In case I don't find the right one, at least I'll have a sane value

// Find the refTypeID that corresponds to a player donation - it happens to be '10', BTW
$reftypesxml = $api->getRefTypes();
$reftypes = Generic::getRefTypes($reftypesxml);
$reftypesnew = RefTypes::getRefTypes($reftypesxml);
if ($reftypes == $reftypesnew)
	print ("\n\nNew refTypes function matches legacy refTypes function output.\n\n");
else
	print ("\n\nERROR: New refTypes function output broken!\n\n");

foreach($reftypes as $id => $name)
	if($name == 'Player Donation')
		$reftypeid = $id;

// Now grab player donations to my corp. We'll grab the data, and if there's 1000, there'll likely be more, and we'll go again

$beforeRefID = null;
print ("\n\nAnd here are the player donations to your master corp wallet in the past week!\n\n");

do
{
	$walletxml = $api->getWalletJournal($beforeRefID,true);
	$wallet = WalletJournal::getWalletJournal($walletxml);

	if(!$wallet)
	{
		print("Received empty wallet data\n");
		break;
	}

	// $reftypeid is a player donation, found above. Could be anything eles, of course. Find relevant entries and output.
    // The below is a simple foreach loop. On my PHP 5.2 host, it is plenty fast - but if you have issues, try the alternative further below
	
//	$begin = microtime(true);
	foreach($wallet as $index => $line)
	{
		if($line['refTypeID']==$reftypeid)
		{
			$formatted = number_format((float)$line[amount],2);
			print("$line[ownerName1] donated $formatted ISKies on $line[date] and gave this reason: $line[reason]\n");
		}
	}
//	$end = microtime(true) - $begin;
//	print("The foreach took ".$end." seconds\n");
	
	// And here's another implementation, that avoids the array copy on every iteration
/*
//	$begin = microtime(true);
	foreach(array_keys($wallet) as $key)
	{
		if($wallet[$key]['refTypeID']==$reftypeid)
			print($wallet[$key]['ownerName1']." donated ".number_format((float)$wallet[$key][amount],2)." ISKies on ".$wallet[$key]['date']." and gave this reason: ".$wallet[$key]['reason']."\n");
	}
//	$end = microtime(true) - $begin;
//	print("The foreach took ".$end." seconds\n");
*/
	// Set the last refID in the array to be the one we're grabbing from
	$beforeRefID=$wallet[count($wallet)-1]['refID'];
//	print("The last refID in there was determined to be $beforeRefID\n");
} while(count($wallet) == 1000);

print("\n\nRaw char wallet journal output\n\n");
$walletxml = $api->getWalletJournal();
$wallet = WalletJournal::getWalletJournal($walletxml);
print_r($wallet);

print ("\n\nRaw char balance output\n\n");
$balancexml = $api->getAccountBalance();
$balance = AccountBalance::getAccountBalance($balancexml);
print_r($balance);

$blnc = new Balance($apiuser,$apipass,$apichar);
$balanceold = $blnc->getBalance();
if ($balance == $balanceold)
	print ("\n\nLegacy char balance function matches new balance function output.\n\n");
else
	print ("\n\nERROR: Legacy char balance function output broken!\n\n");

print ("\n\nRaw corp balance output\n\n");
$balancexml = $api->getAccountBalance(true);
$balance = AccountBalance::getAccountBalance($balancexml);
print_r($balance);

$balanceold = $blnc->getBalance(true);
if ($balance == $balanceold)
	print ("\n\nLegacy corp balance function matches new balance function output.\n\n");
else
	print ("\n\nERROR: Legacy corp balance function output broken!\n\n");

print ("\n\nRaw corp sheet output\n\n");
$corpxml = $api->getCorporationSheet();
$corp = Corporationsheet::getCorporationSheet($corpxml);
print_r($corp);

print ("\n\nRaw starbase list output\n\n");
$starbasexml = $api->getStarbaseList();
$starbase = StarbaseList::getStarbaseList($starbasexml);
print_r($starbase);

if(!empty($starbase))
{
	print ("\n\nRaw starbase detail output\n\n");
	$baseid = $starbase[0]['itemID'];
	$starbasedetailxml = $api->getStarbaseDetail($baseid);
	$starbasedetail = StarbaseDetail::getStarbaseDetail($starbasedetailxml);
	print_r($starbasedetail);
}

print ("\n\nRaw skill training output\n\n");
$skillxml = $api->getSkillInTraining();
$skill = CharacterSheet::getSkillInTraining($skillxml);
print_r($skill);

print ("\n\nRaw char transactions output\n\n");
$transxml = $api->getWalletTransactions();
$trans = WalletTransactions::getWalletTransactions($transxml);
print_r($trans);

$transold = Transaction::getTransaction($transxml);
if ($trans == $transold)
	print ("\n\nLegacy char transaction function matches new transaction function output.\n\n");
else
	print ("\n\nERROR: Legacy char transaction function output broken!\n\n");

print ("\n\nRaw corp transactions output\n\n");
$transxml = $api->getWalletTransactions(null, true);
$trans = WalletTransactions::getWalletTransactions($transxml);
print_r($trans);

$transold = Transaction::getTransaction($transxml);
if ($trans == $transold)
	print ("\n\nLegacy corp transaction function matches new transaction function output.\n\n");
else
	print ("\n\nERROR: Legacy corp transaction function output broken!\n\n");

print("\n\nRaw char industry jobs output\n\n");
$industryxml = $api->getIndustryJobs();
$industry = IndustryJobs::getIndustryJobs($industryxml);
print_r($industry);

print("\n\nRaw corp industry jobs output\n\n");
$industryxml = $api->getIndustryJobs(true);
$industry = IndustryJobs::getIndustryJobs($industryxml);
print_r($industry);

print("\n\nRaw char asset list output\n\n");
$assetxml = $api->getAssetList();
$asset = AssetList::getAssetList($assetxml);
print_r($asset);

print("\n\nRaw corp asset list output\n\n");
$assetxml = $api->getAssetList(true);
$asset = AssetList::getAssetList($assetxml);
print_r($asset);

print ("\n\nRaw character sheet output\n\n");
$charsheetxml = $api->getCharacterSheet();
$charsheet = CharacterSheet::getCharacterSheet($charsheetxml);
print_r($charsheet);

print ("\n\nRaw alliance list output\n\n");
$alliancexml = $api->getAllianceList();
$alliance = Alliancelist::getAllianceList($alliancexml);
print_r($alliance);

print ("\n\nRaw skill tree output\n\n");
$skilltreexml = $api->getSkillTree();
$skilltree = Generic::getSkillTree($skilltreexml);
print_r($skilltree);

$skilltreenew = SkillTree::getSkillTree($skilltreexml);
if ($skilltree == $skilltreenew)
	print ("\n\nNew skill tree function matches legacy skill tree function output.\n\n");
else
	print ("\n\nERROR: New skill tree function output broken!\n\n");

$api->printErrors();
?>