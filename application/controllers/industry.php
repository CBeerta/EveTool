<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Industry extends MY_Controller
{
    /**
     * industryjobs
     *
     * Display a table with Industry Jobs, active and historic
     *
     * @param   string
     */
    public function jobs($maxDays = 7)
    {
        $character = $this->character;
        $data['character'] = $character;

        $char_jobs = MY_IndustryJobs::getIndustryJobs($this->eveapi->getIndustryJobs());
        if ($this->has_corpapi_access && getUserConfig($this->Auth['user_id'], 'pull_corp'))
        {
            $corp_jobs = MY_IndustryJobs::getIndustryJobs($this->eveapi->getIndustryJobs(True));
            $data['corpmates'] = $this->eveapi->corp_members;
        }
        $index = 0;
        $data['data'] = array();
        
        $maxDays = is_numeric($maxDays) ? $maxDays : 7;

        $jobs = empty($char_jobs) ? array() : $char_jobs;
        if (!empty($corp_jobs))
        {
            $jobs = array_merge($char_jobs, $corp_jobs);
        }
        foreach ($jobs as $job)
        {
            $endtime = strtotime($job['endProductionTime'].' +0000');
            if ($endtime < gmmktime() - ($maxDays*24*60*60) )
            {
                continue;
            }
            $data['data'][$index] = (array) get_inv_type($job['outputTypeID']);
            $data['data'][$index] += array(
                    'status' => MY_IndustryJobs::statusIDToString($job['completedStatus']),
                    'activity' => MY_IndustryJobs::activityIDToString($job['activityID']),
                    'amount' => $job['runs'],
                    'outputLocationID' => $job['outputLocationID'],
                    'ends' => api_time_to_complete($job['endProductionTime']),
                    'installerID' => $job['installerID'],
                    'location' => $job['outputLocationID']);

            if ($job['activityID'] == 1 && $job['completedStatus'] == 0)
            {
                // Special Case, Means Job is still running
                $data['data'][$index]['status'] = '<div style="color: green;">Running</div>';
            }
            $index++;
        }
        ksort($data['data']);
        $data['maxDays'] = $maxDays;

        $template['content'] = $this->load->view('jobs', $data, True);
        $this->load->view('maintemplate', $template);
    }
}

?>
