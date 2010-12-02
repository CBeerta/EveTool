<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Industry extends Controller
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
		$characters = array_keys($this->eveapi->characters());
        $menu = array();
		foreach ($characters as $v)
		{
		    $menu["index/{$v}"] = $v;
		}
		$data['submenu'] = array('Sections' => array('index' => 'Industry Jobs'));
		$data['page_title'] = 'Industry'; 

		$this->load->view('template', $data);
	}

    static function _statusid_to_string($status)
    {
            $mapping = array(
                            0 => 'Failed',
                            1 => 'Delivered',
                            2 => 'Aborted',
                            3 => 'GM Aborted',
                            4 => 'Inflight Unarchored',
                            5 => 'Destroyed');
            return ($mapping[$status]);
    }
    
    static function _activityid_to_string($activityID)
    {
            $mapping = array(
                            0 => 'None',
                            1 => 'Manufacturing',
                            2 => 'Research Technology',
                            3 => 'Research Time Production',
                            4 => 'Research Material Production',
                            5 => 'Copying',
                            6 => 'Dublicating',
                            7 => 'Reverse Engineering',
                            8 => 'Inventing');
/*
            $mapping = array(
                            0 => 'None',
                            1 => 'Prod',
                            2 => 'Tech',
                            3 => 'PE',
                            4 => 'ME',
                            5 => 'Copy',
                            6 => 'Dub',
                            7 => 'Rev',
                            8 => 'Inv');
*/    
            return ($mapping[$activityID]);
    }

    public function index($offset = 0, $per_page = 15)
	{
        $index = 0;
        $data['data'] = array();

		foreach ($this->eveapi->characters() as $char)
		{
			$this->eveapi->setCredentials($char);
			$jobs = $this->eveapi->IndustryJobs();
			
			foreach ($jobs->result->jobs as $_job)
			{
				$job = (object) $_job->attributes();
				
	            $endtime = strtotime($job['endProductionTime'].' +0000');
	            
	            $data['data'][$index] = array(
	            		'outputTypeID' => (int) $job['outputTypeID'],
	            		'typeID' => (int) $job['outputTypeID'],
	                    'status' => Industry::_statusid_to_string((int) $job['completedStatus']),
	                    'activity' => Industry::_activityid_to_string((int) $job['activityID']),
	                    'amount' => (int) $job['runs'],
	                    'outputLocationID' => (int) $job['outputLocationID'],
	                    'ends' => api_time_to_complete((string) $job['endProductionTime']),
	                    'endtime' => $endtime,
	                    'installerID' => (int) $job['installerID'],
	                    'installer' => $char,
                    );

	            if ($job['activityID'] == 1 && $job['completedStatus'] == 0)
	            {
	                // Special Case, Means Job is still running
	                $data['data'][$index]['status'] = '<div style="color: green;">Running</div>';
	            }
	            $index++;				
			}
		}
		
        masort($data['data'], array('endtime'));
        
        $data['data'] = array_slice($data['data'], $offset, $per_page, True);
        $this->pagination->initialize(array('base_url' => site_url("/industry/index"), 'total_rows' => $index, 'per_page' => $per_page, 'num_links' => 5));

        $data['data'] = array_add_invtypes($data['data']);
        		
        $this->_template(array('content' => $this->load->view('industry_jobs', $data, True)));
	}

	public function research()
	{
	    print '<pre>';
		foreach ($this->eveapi->characters() as $char)
		{
			$this->eveapi->setCredentials($char);
			$jobs = eveapi::from_xml($this->eveapi->Research());

			print_r($jobs);
        }

        die();

	}
	
}


?>
