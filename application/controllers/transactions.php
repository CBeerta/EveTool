<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Transaction List Pages
 *
 *
 * @author Claus Beerta <claus@beerta.de>
 */
  
class Transactions extends MY_Controller
{
    /**
     * transactionlist
     *
     * Display a List with Transaction by the Character
     *
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
