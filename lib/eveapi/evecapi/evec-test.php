<?php

header("Content-type: text/plain");
require_once('class.evec.php');
require_once('class.quicklook.php');
require_once('class.marketstat.php');
require_once('class.minerals.php');

$evec = new evec();

// QuickLook retrieves market order details for item of typeid, limited by the other arguments
// params required: typeid optional: hours, minQ, regionlimit (multiples) 
$params = array();
$params[(string) 'typeid'] = 34;
$params[(string) 'regionlimit'][] = 10000002;
$params[(string) 'regionlimit'][] = 10000052;
print "\n\nMarket Orders\n\n";
$xml = $evec->getQuickLook($params);
$a = QuickLook::getQuickLook($xml);
print_r($a);

print "\n\nMarketStats\n\n";
unset($params);
// MarketStat returns buy, sell and combine stats for unfulfilled buy sell orders for items as give by typeid and stats scope limited by the optional arguments.
// params required: typeid optional: sethours, setminQ, usesystem, regionlimit (multiples) 
$params = array();
$params[(string) 'typeid'][] = 34;  // multiples added by numberical sub array 
$params[(string) 'typeid'][] = 35; 
$params[(string) 'regionlimit'][] = 10000002;
$params[(string) 'regionlimit'][] = 10000052;
$xml = $evec->getMarketStat($params);
$a = MarketStat::getMarketStat($xml);
print_r($a);


print "\n\nEvemon Minerals pricelist\n\n";
$xml = $evec->getMinerals();
$a = Minerals::getMinerals($xml);

print_r($a);

?>
