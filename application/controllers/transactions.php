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
    public function index()
    {
        $character = $this->character;
        $data['character'] = $character;

        $transxml = $this->eveapi->getWalletTransactions();
        $data['translist'] = WalletTransactions::getWalletTransactions($transxml);

        $template['title'] = "Transactionlist for {$character}";
        $template['content'] = $this->load->view('transactionlist', $data, True);
        $this->load->view('maintemplate', $template);
    }

}
?>
