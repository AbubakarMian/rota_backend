<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Log;
use Response;
use stdClass;

trait APIResponse
{

	public function sendResponse($status_code = 200, $response = null, $error = [], $custom_error_code = null)
    {
    	$status = ($status_code === 200) ? true : false;
    	$error = !empty($error) ? [
    			'custom_code' => $custom_error_code,
    			'message' => $error
    	]: null;

    	$return = [
    		'status' 	=> $status,
    		'response' 	=> $response,
    		'error' 	=> $error
    	];

		Log::info(print_r($return,true));
		$res = new stdClass;
		$res->status = $status;
		$res->response = $response;
		$res->error = $error;
// dd('asd');
// dd(json_encode($status, $res));
    	return json_encode($res);
    }

}
