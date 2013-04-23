<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2013 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}
class ControllerPagesSaleTransactions extends AController {
	public $data = array();
	private $error = array();

  	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		$this->loadLanguage('sale/customer');

		$this->document->setTitle( $this->language->get('heading_title_transactions') );

		$this->document->initBreadcrumb( array (
       		'href'      => $this->html->getSecureURL('index/home'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		 ));
   		$this->document->addBreadcrumb( array (
       		'href'      => $this->html->getSecureURL('sale/customer/update','&id='.$this->request->get['customer_id']),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		 ));
   		$this->document->addBreadcrumb( array (
       		'href'      => $this->html->getSecureURL('sale/transactions','&customer_id='.$this->request->get['customer_id']),
       		'text'      => $this->language->get('heading_title_transactions'),
      		'separator' => ' :: '
   		 ));

		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['grid_settings'] = array(
			//id of grid
            'table_id' => 'transactions_grid',
            // url to load data from
			'url' => $this->html->getSecureURL('listing_grid/transactions','&customer_id='.$this->request->get['customer_id']),
            'sortname' => 'create_date',
            'sortorder' => 'desc',
			'multiselect' => 'false',
            // actions
            'actions' => array(
                'view' => array(
                    'text' => $this->language->get('button_view'),
				    'href' => 'Javascript:void(0);',
                ),
            ),
		);

		$this->data['grid_settings']['colNames'] = array(
			$this->language->get('column_create_date'),
			$this->language->get('column_created_by'),
			$this->language->get('column_credit'),
			$this->language->get('column_debit'),
			$this->language->get('column_transaction_type'),
		);
		$this->data['grid_settings']['colModel'] = array(
			array( 'name' => 'create_date',
					'index' => 'create_date',
					'width' => 120,
					'align' => 'center',
					'search' => false),

			array( 'name' => 'user',
					'index' => 'user',
					'width' => 140,
					'align' => 'left', ),

			array( 'name' => 'credit',
					'index' => 'credit',
					'width' => 50,
					'align' => 'center'),

			array(  'name' => 'debit',
					'index' => 'debit',
					'width' => 50,
					'align' => 'center'),

			array(  'name' => 'transaction_type',
					'index' => 'transaction_type',
					'width' => 110,
					'align' => 'center'),
		);

		$this->loadModel('sale/customer_transaction');

		$form = new AForm();
	    $form->setForm(array(
		    'form_name' => 'transactions_grid_search',
	    ));

	    $this->data['grid_search_form'] = array();
        $this->data['grid_search_form']['id'] = 'transactions_grid_search';
        $this->data['grid_search_form']['form_open'] = $form->getFieldHtml(array(
		    'type' => 'form',
		    'name' => 'transactions_grid_search',
		    'action' => '',
	    ));
        $this->data['grid_search_form']['submit'] = $form->getFieldHtml(array(
		    'type' => 'button',
		    'name' => 'submit',
		    'text' => $this->language->get('button_go'),
		    'style' => 'button1',
	    ));
		$this->data['grid_search_form']['reset'] = $form->getFieldHtml(array(
		    'type' => 'button',
		    'name' => 'reset',
		    'text' => $this->language->get('button_reset'),
		    'style' => 'button2',
	    ));
		$this->data['js_date_format'] = format4Datepicker($this->language->get('date_format_short'));
        $this->data['grid_search_form']['fields']['date_start'] = $form->getFieldHtml(array(
            'type' => 'input',
            'name' => 'date_start',
            'default' => dateInt2Display(time()),
        ));

        $this->data['grid_search_form']['fields']['date_end'] = $form->getFieldHtml(array(
            'type' => 'input',
            'name' => 'date_end',
            'default' => dateInt2Display(time()),
        ));

		$this->data['grid_settings']['search_form'] = true;


        $grid = $this->dispatch('common/listing_grid', array( $this->data['grid_settings'] ) );
		$this->view->assign('listing_grid', $grid->dispatchGetOutput());
		$this->view->assign ( 'search_form', $this->data['grid_search_form'] );

		$this->document->setTitle( $this->language->get('heading_title_transactions') );
		$this->view->assign( 'popup_action', $this->html->getSecureURL('listing_grid/transactions/get_transaction_info') );

		$this->view->assign( 'popup_action_save', $this->html->getSecureURL('listing_grid/transactions/savetransaction') );

		$form = new AForm('HT');
		$form->setForm(array(
			'form_name' => 'transaction_form',
		));
		$this->data['ajax_form_open'] = (string)$form->getFieldHtml(array(
					'type' => 'form',
					'name' => 'transaction_form',
					'action' => $this->html->getSecureURL('listing_grid/transactions/savetransaction','&customer_id='.$this->request->get['customer_id']),
				));

		$this->view->assign('help_url', $this->gen_help_url('customer_transactions_listing') );
		$balance = $this->model_sale_customer_transaction->getBalance($this->request->get['customer_id']);
		$currency = $this->currency->getCurrency($this->config->get('config_currency'));		
		$this->data['balance'] = $this->language->get('text_balance').' '.$currency['symbol_left'].round($balance,2).$currency['symbol_right'];

		$this->data['button_actas'] = $this->html->buildButton(array(
				    'text' => $this->language->get('button_actas'),
				    'style' => 'button1',
					'href' => $this->html->getSecureURL('sale/customer/actonbehalf', '&customer_id='.$this->request->get['customer_id']),
					'target' => 'new'
			    ));

		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/sale/transactions.tpl' );

        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
  

	public function actonbehalf() {

        $this->extensions->hk_InitData($this,__FUNCTION__);
    	
		if (isset($this->request->get['customer_id'])) {
			session_write_close();
			$session = new ASession('PHPSESSID_AC_SF');
 			$session->data['customer_id'] = $this->request->get['customer_id'];
 			session_write_close();
			$this->redirect($this->html->getCatalogURL('account/account'));
		}

        $this->extensions->hk_UpdateData($this,__FUNCTION__);

		$this->redirect($this->html->getSecureURL('sale/customer'));
	} 

}