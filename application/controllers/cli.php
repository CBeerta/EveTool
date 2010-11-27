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
		print date('r')." - Updating XML Cache:\n";
	
		$index = 0;
		
		$what_to_update = array (
			'Account Balance' => 'AccountBalance',
			'Skill in Training' => 'SkillInTraining',
			'Character Sheet' => 'CharacterSheet',
			'Skill Queue' => 'SkillQueue',
			/* 'Wallet Transactions' => 'WalletTransactions', */ /* this is somehow borked by ccp currently, frequently throws an error */
			'Wallet Journal' => 'WalletJournal',
			'Industry Jobs' => 'IndustryJobs',
			'Market Orders' => 'MarketOrders',
			'Standings' => 'Standings',
            'Mail Headers' => 'MailMessages',
            'Upcoming Calendar Events' => 'UpcomingCalendarEvents',
		);

		foreach ($this->eveapi->characters() as $char)
        {
			$this->eveapi->setCredentials($char);
            print " - {$char->name}: ";
            foreach ($what_to_update as $k => $v)
            {
                print ".. {$k}";
                $res = $this->eveapi->api->char->$v();
            }
            print "\n";
        }

    }



}
