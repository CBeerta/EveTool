<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Overview extends Controller
{
    /**
    *
    * Load the Template and add submenus
    *
    * @access private
    * @param array $data contains the stuff handed over to the template
    **/
	private function _template($data)
	{
    	$data['submenu'] = array('Sections' => array('index' => 'Eve Mail', 'events' => 'Events'));
		$data['page_title'] = 'Overview'; 

		$this->load->view('template', $data);
	}
	

    /**
    * Add Mailbodies to a MailMessages XML Dump
    *
    * @access private
    * @param array $headers XML of headers to fill with bodies
    **/	
	private function _add_mailbody($headers)
	{
		$api = $this->eveapi->api;
		$mails = $output = array();

		/*
		* First build an array with all message ids we need to pull sorted by character
		*/	
		foreach ($headers as $header)
		{
			$char = $header['character'];
			unset ($header['character']);
			$messageID = $header['messageID'];
			
			if (!isset($mails[$char->name]))
			{
				$mails[$char->name] = $char;
			}
			$mails[$char->name]->idlist[] = $header['messageID'];
			$mails[$char->name]->headers[$header['messageID']] = $header;
		}
		
		/*
		* Pull all mailbodies for respective characters
		*/
		foreach ($mails as $k => $v)
		{
			$api->setCredentials($v->apiUser, $v->apiKey, $v->characterID);
			$message = $api->char->MailBodies(array('ids' => implode(',', $v->idlist)));
			$_mailinglists = eveapi::from_xml($api->char->MailingLists(), 'mailingLists');

            foreach ($_mailinglists as $list)
            {
                $mailinglists[$list['listID']] = $list['displayName'];
            }

			foreach ($message->result->messages as $_msg)
			{
				$mails[$k]->bodies[$_msg->messageID] = (string) $_msg;
				
				if (!empty($mails[$k]->headers[$_msg->messageID]['toListID']))
				{
				    $mails[$k]->headers[$_msg->messageID]['toList'] = $mailinglists[$mails[$k]->headers[$_msg->messageID]['toListID']];
				}
				else if (!empty($mails[$k]->headers[$_msg->messageID]['toCorpOrAllianceID']))
				{
				    $mails[$k]->headers[$_msg->messageID]['toList'] = 'Corp or Alliance';
				}
			}
		}

		/*
		* Now go through all the mails again, and rebuild the array to be traversable
		*/
		foreach ($mails as $k => $v)
		{
			foreach ($v->idlist as $messageid)
			{
				$index = count($output);
				$output[$index] = (array) $v->headers[$messageid];
				$output[$index] += array(
					'for' => $k,
					'forID' => $v->characterID,
					'body' => $v->bodies[$messageid],
				);
			}
		}
		return ($output);
	}
	
	public function index()
	{
		$data = $headers = array();
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();

		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$headers = array_merge($headers, eveapi::from_xml($api->char->MailMessages(), 'messages', array('character' => $char)));
		}
		
		$mails = $this->_add_mailbody($headers);
		masort($mails, array('unixsentDate'));
		$mails = array_splice($mails, 0, 15);
        $this->_template(array('content' => $this->load->view('mails', array('mails' => $mails), True)));
	}

	public function events()
	{
		$data = $events = array();
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();

		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$events = array_merge ($events, eveapi::from_xml($api->char->UpcomingCalendarEvents(), 'upcomingEvents', array('character' => $char)));
		}

        $eventidlist = array();
		foreach($events as $k => $v)
		{
		    if (in_array($v['eventID'], $eventidlist))
		    {   
		        # remove dublicates
		        unset($events[$k]);
	        }
		    $eventidlist[] = $v['eventID'];
	    }
		masort($events, array('unixeventDate'));
		$events = array_splice($events, 0, 15);

        $this->_template(array('content' => $this->load->view('events', array('events' => $events), True)));
	}




/*

	public function index()
	{
		$data['items'] = array(array('title' => 'Kyara Completed Station Spinning 5', 'to' => 'Eurybe', 'from' => 'EVE Skill Training', 'body' => 'Kyara has successfully Trained Station Spinning to Level 5'));
		
		$content = $this->load->view('home', $data, true);
        $this->_template(array('content' => $content));		
	}
*/	

}


?>
