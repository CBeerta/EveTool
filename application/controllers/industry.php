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
    
    var $maxAge = 604800; // 1 week FIXME: make this user-tunable
    
    public function jobs($character = False)
    {
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }
        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);
        $data['character'] = $character;

        $jobs = IndustryJobs::getIndustryJobs($this->eveapi->getIndustryJobs());
        $index = 0;
        $data['data'] = array();

        foreach ($jobs as $job)
        {
            if (strtotime($job['endProductionTime'].' +0000') < gmmktime() - $this->maxAge)
            {
                continue;
            }
            $data['data'][$index] = array(
                    'typeID' => $job['outputTypeID'],
                    'typeName' => getInvType($job['outputTypeID'])->typeName,
                    'status' => IndustryJobs::statusIDToString($job['completedStatus']),
                    'activity' => IndustryJobs::activityIDToString($job['activityID']),
                    'amount' => $job['runs'],
                    'ends' => timeToComplete($job['endProductionTime']),
                    'location' => locationIDToName($job['outputLocationID']));

            if ($job['activityID'] == 1 && $job['completedStatus'] == 0)
            {
                // Special Case, Means Job is still running
                $data['data'][$index]['status'] = '<div style="color: green;">Running</div>';
            }
            $index++;
        }
        $template['content'] = $this->load->view('eve/jobs', $data, True);
        $this->load->view('eve/maintemplate', $template);
    }
}

?>
