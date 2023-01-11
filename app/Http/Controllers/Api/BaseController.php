<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * Class BaseController
 *
 * @package App\Http\Controllers\Api
 */
class BaseController extends Controller
{
    public function returnJsonSuccess($message, $data=null){
        if($data == null){
            return response()->json([
                'status' => true,
                'message' => $message
            ], 200,[], JSON_NUMERIC_CHECK);
        }else{
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $data
            ], 200);
        }
    }

    public function returnJsonSuccessCheck($message, $data=null){
        if($data == null){
            return response()->json([
                'status' => true,
                'message' => $message
            ], 200,[], JSON_NUMERIC_CHECK);
        }else{
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $data
            ], 200,[], JSON_NUMERIC_CHECK);
        }
    }

    public function returnJsonError($message, $statusCode, $data=null){
        if($data == null){
            return response()->json([
                'status' => false,
                'message' => $message
            ], $statusCode);
        }else{
            return response()->json([
                'status' => false,
                'message' => $message,
                'data' => $data
            ], $statusCode);
        }
    }
}
