<?php defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' ); 

/**
 * Class : BaseController
 * Base Class to control over all the classes
 * @author : Kishor Mali
 * @version : 1.1
 * @since : 15 November 2016
 */
class BaseController extends CI_Controller {
	protected $role = '';
	protected $vendorId = '';
	protected $firstName = '';
	protected $lastName = '';
	protected $roleText = '';
	protected $global = array ();
	protected $lastLogin = '';

	/**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
//         $this->load->model('settings_model');
// 		$this->SiteData();
    }
	
	/**
	 * Takes mixed data and optionally a status code, then creates the response
	 *
	 * @access public
	 * @param array|NULL $data
	 *        	Data to output to the user
	 *        	running the script; otherwise, exit
	 */
	public function response($data = NULL) {
		$this->output->set_status_header ( 200 )->set_content_type ( 'application/json', 'utf-8' )->set_output ( json_encode ( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display ();
		exit ();
	}

	/**
	 * This function used to get the site's Site Data fro the database
	 */
	public function SiteData()
	{
		$this->companyName = $this->settings_model->getsettingsInfo()['name'];
		//Logos
		if(!empty($this->settings_model->getSettingsInfo()['whiteLogo']))
		{
			$this->logoWhite = base_url().'uploads/'.$this->settings_model->getSettingsInfo()['whiteLogo'];
		}
		else
		{
			$this->logoWhite = base_url().'assets/dist/img/logo-white.png';
		}
		if(!empty($this->settings_model->getSettingsInfo()['darkLogo']))
		{
			$this->logoDark = base_url().'uploads/'.$this->settings_model->getSettingsInfo()['darkLogo'];
		}
		else
		{
			$this->logoDark = base_url().'assets/dist/img/logo.png';
		}
		if(!empty($this->settings_model->getSettingsInfo()['favicon']))
		{
			$this->favicon = base_url().'uploads/'.$this->settings_model->getSettingsInfo()['favicon'];
		}
		else
		{
			$this->favicon = base_url().'assets/dist/img/favicon.png';
		}
		$this->siteTitle = $this->settings_model->getSettingsInfo()['title'];
		$this->siteDescription = $this->settings_model->getSettingsInfo()['description'];
		$this->siteKeywords = $this->settings_model->getSettingsInfo()['keywords'];
		$this->chatWidget = $this->settings_model->getSettingsInfo()['chatWidget'];
		$this->currency = $this->settings_model->getSettingsInfo()['currency'];
	}
	
	/**
	 * This function used to check the user is logged in or not
	 */
	function isLoggedIn() {
		$isLoggedIn = $this->session->userdata ( 'isLoggedIn' );
		
		if (! isset ( $isLoggedIn ) || $isLoggedIn != TRUE) {
			redirect ( 'login' );
		} else {
			$this->role = $this->session->userdata ( 'role' );
			$this->vendorId = $this->session->userdata ( 'userId' );
			$this->firstName = $this->session->userdata ( 'firstName' );
			$this->lastName = $this->session->userdata ( 'lastName' );
			$this->roleText = $this->session->userdata ( 'roleText' );
			$this->lastLogin = $this->session->userdata ( 'lastLogin' );
			if(!empty($this->session->userdata ( 'ppic' )))
			{
				$this->ppic = base_url().'uploads/'.$this->session->userdata ( 'ppic' );
			}
			else
			{
				$this->ppic = base_url().'assets/dist/img/avatar.png';
			}
			
			$this->global ['firstName'] = $this->security->xss_clean($this->firstName);
			$this->global ['userId'] = $this->vendorId;
			$this->global ['lastName'] = $this->security->xss_clean($this->lastName);
			$this->global ['companyName'] = $this->security->xss_clean($this->companyName);
			$this->global ['role'] = $this->security->xss_clean($this->role);
			$this->global ['role_text'] = $this->security->xss_clean($this->roleText);
			$this->global ['last_login'] = $this->security->xss_clean($this->lastLogin);
			$this->global ['ppic'] = $this->security->xss_clean($this->ppic);
		}
	}
	
	/**
	 * This function is used to check the access
	 */
	function isAdmin($module_id, $action_id) {
		if ($this->role == ROLE_CLIENT) {
			return false;
		} else {
			if($this->role == ROLE_ADMIN) {
				return true;
			} else if($this->role == ROLE_MANAGER){
				if (!$this->user_model->getPermissions($module_id, $action_id, $this->vendorId))
				{
					return false;
				}
				else
				{
					return true;
				}
			}
		}
	}
	

	/**
	 * This function is used to load the set of views
	 */
	function loadThis() {
		$this->global ['pageTitle'] = 'Access Denied';
		
		$this->load->view ('access');
	}
	
	/**
	 * This function is used to logged out user from system
	 */
	function logout() {
		$this->session->sess_destroy ();

			redirect ( 'login' );
	}

	/**
     * This function used to load views
     * @param {string} $viewName : This is view name
     * @param {mixed} $headerInfo : This is array of header information
     * @param {mixed} $pageInfo : This is array of page information
     * @param {mixed} $footerInfo : This is array of footer information
     * @return {null} $result : null
     */
    function loadViews($viewName = "", $headerInfo = NULL, $pageInfo = NULL, $footerInfo = NULL){

        $this->load->view('/partials/header', $headerInfo);
        $this->load->view('/'.$viewName, $pageInfo);
        $this->load->view('/partials/footer', $footerInfo);
	}

	/**
	 * This function used provide the pagination resources
	 * @param {string} $link : This is page link
	 * @param {number} $count : This is page count
	 * @param {number} $perPage : This is records per page limit
	 * @return {mixed} $result : This is array of records and pagination data
	 */
	function paginationCompress($link, $count, $perPage = 10, $segment = SEGMENT) {
		$this->load->library ( 'pagination' );

		$config ['base_url'] = base_url () . $link;
		$config ['data_page_attr'] = 'class="page-link"';
		$config ['total_rows'] = $count;
		$config ['uri_segment'] = $segment;
		$config ['per_page'] = $perPage;
		$config ['num_links'] = 5;
		$config ['full_tag_open'] = '<div class="dataTables_paginate paging_simple_numbers" id="data-table_paginate"><ul class="pagination">';
		$config ['full_tag_close'] = '</ul></div>';
		$config ['first_tag_open'] = '<li class="arrow">';
		$config ['first_link'] = 'First';
		$config ['first_tag_close'] = '</li>';
		$config ['prev_link'] = 'Previous';
		$config ['prev_tag_open'] = '<li class="paginate_button page-item previous" id="data-table_previous">';
		$config ['prev_tag_close'] = '</li>';
		$config ['next_link'] = 'Next';
		$config ['next_tag_open'] = '<li class="paginate_button page-item next" id="data-table_next">';
		$config ['next_tag_close'] = '</li>';
		$config ['cur_tag_open'] = '<li class="paginate_button page-item active"><a href="#" aria-controls="data-table" data-dt-idx="1" tabindex="0" class="page-link">';
		$config ['cur_tag_close'] = '</a></li>';
		$config ['num_tag_open'] = '<li class="paginate_button page-item ">';
		$config ['num_tag_close'] = '</li>';
		$config ['last_tag_open'] = '<li class="arrow">';
		$config ['last_link'] = 'Last';
		$config ['last_tag_close'] = '</li>';
	
		$this->pagination->initialize ( $config );
		$page = $config ['per_page'];
		$segment = $this->uri->segment ( $segment );
	
		return array (
				"page" => $page,
				"segment" => $segment
		);
	}
}