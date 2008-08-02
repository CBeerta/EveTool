<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Wallet extends MY_Controller
{

    /**
     * walletjournal
     *
     * Display a Journal with the latest Wallet Transactions
     *
     * @param   string
     */
    public function journal($character = False)
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

        $walletxml = $this->eveapi->getWalletJournal();
        $data['wallet'] = WalletJournal::getWalletJournal($walletxml);
        $data['reftypes'] = $this->eveapi->reftypes;

        $template['title'] = "Wallet Journal for {$character}";
        $template['content'] = $this->load->view('walletjournal', $data, True);
        $this->load->view('maintemplate', $template);
    }
}
?>
