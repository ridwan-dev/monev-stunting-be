<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\DataStunting;

class StuntingDataController extends BaseController
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth:api', 
                'scopes:edit,create,delete'
            ])->except(
                [
                    'index', 'show', 'tahunSumber'
                ]
            );
    }

    public function index()
    {
        $stuntingDatas = DataStunting::all();
        return $this->returnJsonSuccess("Data Stunting fetched successfully", $stuntingDatas);
    }

    public function tahunSumber($tahun, $sumber){
        $stuntingDatas = new DataStunting;
        if($tahun != "all"){
            $stuntingDatas->where('tahun', $tahun);
        }

        if($sumber != "all"){
            $stuntingDatas->where('sumber', $tahun);
        }

        $stuntingDatas = $stuntingDatas->get();
        return $this->returnJsonSuccess("Data Stunting fetched successfully", $stuntingDatas);
    }
}
