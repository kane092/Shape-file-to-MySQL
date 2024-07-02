<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Api (ApiController)
 * Api class to handle APIs
 * @author : Axis96
 * @version : 1.0
 * @since : 07 December 2019
 */
class Api extends BaseController {

	public function __construct()
    {
        parent::__construct();
    }

	public function index()
	{
		print "API Controller";
	}

	public function automatic_cron_everyweek()
	{
		// if($this->input->is_cli_request()) {
			$this->load->model('api_model');
			// $this->api_model->saveBFPData();
			$this->api_model->saveFireHydrantData();
		// } else {
		//     echo "This script can only be accessed via the command line" . PHP_EOL;
        //     return;
		// }
	}

	public function automatic_cron_everyday()
	{
		// if($this->input->is_cli_request()) {
			$this->load->model('api_model');
			// $path= FCPATH .'uploads/wp-file-manager-pro';
    		// $this->load->helper("file"); // load the helper
    		// delete_files($path, true, false, 1);

			// $downloaded = $this->api_model->downloadFileToServer();
			// if ($downloaded) {
			// 	$unzipped = $this->api_model->unzipDownloadedFile($downloaded);			
			// }
			$this->api_model->savePolygonData();
			$this->api_model->saveAddressData();
		// } else {
		//     echo "This script can only be accessed via the command line" . PHP_EOL;
        //     return;
		// }
	}
	
// 	public function createKMLFile()
// 	{
// 	    $this->load->model('api_model');
// 	    $this->api_model->createKMLFile();
// 	}

	public function guide()
	{
		$companyInfo = $this->settings_model->getsettingsInfo();
		$data['companyInfo'] = $companyInfo;
		$this->global['pageTitle'] = $companyInfo['name'];
		$this->load->view('/siteContent/apiguide', $this->global, $data, NULL);
	}

	public function getPolygonData()
	{
		$this->load->model('api_model');
		$params = array();
		$params['LOT_NUMBER'] = isset($_GET['lot_number']) ? $_GET['lot_number'] : "";
		$params['ROAD_NUM_1'] = isset($_GET['house_number']) ? $_GET['house_number'] : "";
		$params['ROAD_NAME'] = isset($_GET['street_name']) ? $_GET['street_name'] : "";
		$params['LOCALITY'] = isset($_GET['suburb']) ? $_GET['suburb'] : "";
		$data = $this->api_model->getPolygonData($params);
		echo json_encode($data); 
	}

// 	public function getAddressData()
// 	{
// 		$this->load->model('api_model');
// 		$params = $_GET;
// 		$data = $this->api_model->getAddressData($params);
// 		echo json_encode($data);
// 	}
}