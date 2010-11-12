<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller that gets called by the 'cli.php' entry script
 *
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 */

class Cli extends Controller
{


    /**
     * Cron Updater updates the EveApi XML Caches for all characters found in the Database
     */
	public function cron_update()
	{
        $api = $this->eveapi->api;
        $characters = $this->eveapi->load_characters();

		print date('r')." - Updating XML Cache:\n";
	
		$index = 0;
		
		$what_to_update = array (
			'Account Balance' => 'AccountBalance',
			'Skill in Training' => 'SkillInTraining',
			'Character Sheet' => 'Charactersheet',
			'Skill Queue' => 'SkillQueue',
			/* 'Wallet Transactions' => 'WalletTransactions', */ /* this is somehow borked by ccp currently, frequently throws an error */
			'Wallet Journal' => 'WalletJournal',
			'Industry Jobs' => 'IndustryJobs',
			'Market Orders' => 'MarketOrders',
			'Standings' => 'Standings',
            'Mail Headers' => 'MailMessages',
		);

		foreach ($this->eveapi->characters as $char)
        {
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
            print " - {$char->name}: ";
            foreach ($what_to_update as $k => $v)
            {
                print ".. {$k}";
                $res = $api->char->$v();
            }
            print "\n";
        }

    }



}
