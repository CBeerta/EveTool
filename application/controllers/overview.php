<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Overview extends Controller
{
	public $page_title = 'Overview';
	public $submenu = array('evemail' => 'EveMail');
	
	public function _remap($method)
	{
		$data['page_title'] = $this->page_title;
		$data['submenu'] = $this->submenu;
		
		$data['content'] = $this->$method();
		$this->load->view('template', $data);
	}
	
	public function index()
	{
		$data['items'] = array(array('title' => 'Kyara Completed Station Spinning 5', 'to' => 'Eurybe', 'from' => 'EVE Skill Training', 'body' => 'Kyara has successfully Trained Station Spinning to Level 5'));
		
		$content = $this->load->view('home', $data, true);
		
		return ($content);
	}
	
	private function _get_mailbody($header)
	{
		$char = $header['character'];
		$messageID = $header['messageID'];
		
		$api = $this->eveapi->api;
		$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
				
		$message = $api->char->MailBodies(array('ids' => $messageID));


	}
	
	
	public function evemail()
	{
		$data = $headers = array();
		$api = $this->eveapi->api;
		$characters = $this->eveapi->load_characters();

		foreach ($this->eveapi->characters as $char)
		{
			$api->setCredentials($char->apiUser, $char->apiKey, $char->characterID);
			$mails = $api->char->MailMessages();
			
			foreach ($mails->result->messages as $_message)
			{
				$message = (object) $_message->attributes();
				$row = array(
					'character' => $char,
					'messageID' => (int) $message->messageID,
					'senderID' => (int) $message->senderID,
					'sentDate' => strtotime((string) $message->sentDate),
					'toCorpOrAllianceID' => (int) $message->toCorpOrAllianceID,
					'toCharacterIDs' => (int) $message->toCharacterIDs,
					'title' => (string) $message->title,
					);
				$headers[] = $row;
			}
			
		}
		$headers = array_splice($headers, 0, 25);
		masort($headers, array('sentDate'));
		
		print_r($this->_get_mailbody($headers[2]));
		//print_r($headers);
		
		$content = '';
		return ($content);
	}

}


?>