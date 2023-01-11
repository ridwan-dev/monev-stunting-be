<?php

namespace App\Http\Controllers\Api\V1\Interkoneksi;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Libraries\Services\{
    RkaklService,
    Core\Exception as ServiceException
};
use App\Libraries\Services\Core\Auth;

use App\Models\Staging\Rkakl;

class InterkoneksiRkaklController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('service');
    }

    public function getDataRkakl(Request $request, $tahun, $kddept){
        try {
            $rkaklService = RkaklService::get(RkaklService::PAGU_URL . "pagu?year=$tahun&kddept=$kddept");
            ServiceException::on($rkaklService);
            $data = $rkaklService->data;
        } catch (\Exception $e) {
            $data = [];
        }

        if(count($data) > 0){
            Rkakl::truncate();
        }

        foreach($data as $komponen){
            $collection = new Rkakl();
            $collection->kddept = $komponen->KDDEPT;
            $collection->kdunit = $komponen->KDUNIT;
            $collection->kdprogram = $komponen->KDPROGRAM;
            $collection->kdgiat = $komponen->KDGIAT;
            $collection->kdoutput = $komponen->KDOUTPUT;

            $collection->kdsoutput = $komponen->KDSOUTPUT;
            $collection->jmlpagu = $komponen->JMLPAGU;
            $collection->tahun = $tahun;

            $collection->save();
        }



        return $this->returnJsonSuccess("Data stored successfully", $data);
    }
    
}
