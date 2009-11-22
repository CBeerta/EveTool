<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Character Information Pages
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 * @todo Add Certificates
 * @todo Add Ship capabilities
 */

class Character extends MY_Controller
{
    /**
     * Display a Characters Skilltree
     * 
     * - Current Training info
     * - Skill Queue
     * - Clone info
     * - Skill Tree
     *
     */
    function skilltree()
    {
        $data['character'] = $this->character;
        $data['data'] = array();
        $balance = AccountBalance::getAccountBalance($this->eveapi->getAccountBalance());
        $data['balance'] = $balance[0]['balance'];

        $training = CharacterSheet::getSkillInTraining($this->eveapi->getSkillInTraining());
        $charsheet = CharacterSheet::getCharacterSheet($this->eveapi->getcharactersheet());
		$queue = SkillQueue::getSkillQueue($this->eveapi->getSkillQueue());
		$data['queue'] = $queue;
		      
        $skillTree = array();
        $data['skillsTotal'] = $data['skillPointsTotal'] = 0;
        $data['skillsAtLevel'] = array_fill(0, 6, 0);
        foreach ($charsheet['skills'] as $skill)
        {
            $data['skillPointsTotal'] += $skill['skillpoints'];
            $s = $this->eveapi->skilltree[$skill['typeID']];

            if (!isset($skillTree[$s['groupID']]))
            {
                $skillTree[$s['groupID']] = array('groupSP' => 0, 'skillCount' => 0);
            }

            $skillTree[$s['groupID']]['skills'][$skill['typeID']] = array(
                'typeID' => $skill['typeID'],
                'skillpoints' => $skill['skillpoints'],
                'rank' => $s['rank'],
                'typeName' => $s['typeName'],
                'description' => $s['description'],
                'level' => $skill['level']);
            $skillTree[$s['groupID']]['groupSP'] += $skill['skillpoints'];
            $skillTree[$s['groupID']]['skillCount'] ++;
            $data['skillsTotal'] ++;
            $data['skillsAtLevel'][$skill['level']] ++;
        }
        $data['skillTree'] = $skillTree;
        
        $learning = isset($skillTree[267]['skills'][3374]['level']) ? 1 + (float)$skillTree[267]['skills'][3374]['level'] * 0.02 : 1;

        $attributes = array(
            'intelligence' => array(3377,12376),
            'charisma' => array(3376,12383),
            'perception' => array(3379,12387),
            'memory' => array(3378,12385),
            'willpower' => array(3375,12386));

        foreach ($attributes as $attribute => $se)
        {
            $sll = isset($skillTree[267]['skills'][$se[0]]['level']) ? $skillTree[267]['skills'][$se[0]]['level'] : 0; // Learnings
            $shl = isset($skillTree[267]['skills'][$se[1]]['level']) ? $skillTree[267]['skills'][$se[1]]['level'] : 0; // Advanced Learnings
            $enhancer = isset($charsheet['enhancers'][$attribute.'Bonus']['augmentatorValue']) ? $charsheet['enhancers'][$attribute.'Bonus']['augmentatorValue'] : 0;
            $data['attributes'][$attribute] = number_format(($charsheet['attributes'][$attribute] + $enhancer + $sll + $shl) * $learning, 2);
            if ($enhancer > 0)
            {
                $data['attributes'][$attribute] .= " (+{$enhancer})";
            }
        }
        $data['charinfo'] = $charsheet;

        if ($training['skillInTraining'] != 0)
        {
            $training['trainingTypeName'] = $this->eveapi->skilltree[$training['trainingTypeID']]['typeName'];
        }
        else
        {
            $training['trainingTypeName'] = 'Not Training';
            $training['trainingToLevel'] = Null;
            $training['trainingTypeID'] = -1;
        }
        $data['training'] = $training;
        
        $template['content'] = $this->load->view('skilltree', $data, True);
        $this->load->view('maintemplate', $template);
    }

}
