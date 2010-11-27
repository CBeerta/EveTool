<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Industry extends Controller
{
	public $page_title = 'Industry';
	public $submenu = array('jobs' => 'Industry Jobs');

    static function statusid_to_string($status)
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
    
    static function activityid_to_string($activityID)
    {
            /*
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
             */
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
            return ($mapping[$activityID]);
    }

    public function index($offset = 0, $per_page = 15)
	{
		$data['page_title'] = $this->page_title;
		
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
	                    'status' => Industry::statusid_to_string((int) $job['completedStatus']),
	                    'activity' => Industry::activityid_to_string((int) $job['activityID']),
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
        
        foreach ($data['data'] as $k => $v)
        {
        	/* Add the invType stuff after we truncate the info for pagination, to reduce database queries */
        	$data['data'][$k] += (array) get_inv_type($v['outputTypeID']);
        }
		
		$data['content'] = $this->load->view('industry_jobs', $data, true);
		$this->load->view('template', $data);
	}
	
}


?>
