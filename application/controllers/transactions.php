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
    public function index($offset = 0, $per_page = 15)
    {
        $data['character'] = $this->character;
        
        if ($offset === $this->character || $per_page === $this->character)
        {
            // @todo we should really get a proper solution for this
            redirect(site_url("/transactions/index"));
        }

        $transxml = $this->eveapi->getWalletTransactions();
        $data['translist'] = WalletTransactions::getWalletTransactions($transxml);
        
        $total = count($data['translist']);
        $data['translist'] = array_slice($data['translist'], $offset, $per_page, True);
        $this->pagination->initialize(array('base_url' => site_url("/transactions/index"), 'total_rows' => $total, 'per_page' => $per_page, 'num_links' => 5));

        $template['title'] = "Transactionlist for {$this->character}";
        $template['content'] = $this->load->view('transactionlist', $data, True);
        $this->load->view('maintemplate', $template);
    }

}
?>
