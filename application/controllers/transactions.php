<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Transactions extends MY_Controller
{
    /**
     * transactionlist
     *
     * Display a List with Transaction by the Character
     *
     * @param   string
     * @param   int
     */
    public function index($character = False, $highlight = Null)
    {
        if (!in_array($character, array_keys($this->chars)))
        {
            die("Could not find matchign char {$character}");
        }

        $data['character'] = $character;

        $this->eveapi->setCredentials(
            $this->chars[$character]['apiuser'], 
            $this->chars[$character]['apikey'], 
            $this->chars[$character]['charid']);

        $transxml = $this->eveapi->getWalletTransactions();
        $data['translist'] = WalletTransactions::getWalletTransactions($transxml);
        $data['highlight'] = $highlight;

        $template['title'] = "Transactionlist for {$character}";
        $template['content'] = $this->load->view('transactionlist', $data, True);
        $this->load->view('maintemplate', $template);
    }

}
?>
