<?php
	defined('BASEPATH') or exit('No direct script access allowed');

class Fiserv extends App_Controller
{
    public function index($id, $hash)
    {
	if ($this->input->post('card')) {
	    $ch = curl_init();
	    $online_payment_amount = $this->session->userdata('online_payment_amount');
	    $currency = $this->session->userdata('currency');
	    if ($currency == "XCD"){
		$currency_code = '951';
	    } elseif ($currency == "USD"){
		$currency_code = '840';
	    }
	    //var_dump($this->session->userdata);exit();
	    $this->session->unset_userdata('online_payment_amount');
            $this->session->unset_userdata('currency');

	    $fields = array(
    		'cardNumber'=>$this->input->post('card'),
   		'expireYear'=>$this->input->post('year'),
    		'expireMonth'=>$this->input->post('month'),
    		'code'=>$this->input->post('code'),
    		'amount'=>$online_payment_amount,
    		'currency'=>$currency_code,
    	    );
	
	curl_setopt($ch, CURLOPT_URL,"https://api.epic.dm/crm_api.php");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	//var_dump($server_output);exit();
	if ($server_output == "APPROVED"){
		$success = $this->fiserv_gateway->addPayment(
            		[
              		'amount'        => $online_payment_amount,
              		'invoiceid'     => $id,
              		'transactionid' => "",
              		]
           	);
		if ($success) {
                	set_alert('success', _l('online_payment_recorded_success'));
            	} else {
                	set_alert('danger', _l('online_payment_recorded_success_fail_database'));
            	}
		redirect(site_url('clients/invoices'));
	} else {
		set_alert('danger', _l('online_payment_fail'));
		redirect(site_url('clients/invoices'));
	}

	curl_close ($ch);die;
        }

	$this->load->view('forms/fiserv_pay_form');    
    }
}
?>
