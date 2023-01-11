<?php

namespace App\Http\Controllers\Api\V1\Interkoneksi;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Libraries\Services\{
    EmonevService,
    Core\Exception as ServiceException
};
use App\Libraries\Services\Core\Auth;

use App\Models\Staging\EmonevStunting;

class InterkoneksiEmonevController extends BaseController
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

    public function getDataKomponen(Request $request, $tahun){
        try {
            $emonevService = EmonevService::get(EmonevService::KOMPONEN_URL . "&thn=$tahun");
            ServiceException::on($emonevService);
            $data = $emonevService->data;
        } catch (\Exception $e) {
            $data = [];
        }

        if(count($data) > 0){
            EmonevStunting::truncate();
        }

        foreach($data as $komponen){
            $collection = new EmonevStunting(['table' => 'emonev_stunting_'.$tahun]);
            $collection->kdprov = $komponen->kdprov;
            $collection->nmprov = $komponen->nmprov;
            $collection->kdkab = $komponen->kdkab;
            $collection->nmkab = $komponen->nmkab;

            $collection->kdstkr = $komponen->kdstkr;
            $collection->nmstkr = $komponen->nmstkr;
            $collection->kddept = $komponen->kddept;
            $collection->nmdept = $komponen->nmdept;
            $collection->kdunit = $komponen->kdunit;
            $collection->nmunit = $komponen->nmunit;
            $collection->kdprog = $komponen->kdprog;
            $collection->nmprog = $komponen->nmprog;
            $collection->kddit = $komponen->kddit;
            $collection->nmdit = $komponen->nmdit;
            $collection->kdgiat = $komponen->kdgiat;
            $collection->nmgiat = $komponen->nmgiat;
            $collection->kdkro = $komponen->kdkro;
            $collection->nmkro = $komponen->nmkro;
            $collection->kdro = $komponen->kdro;
            $collection->kdkmpn = $komponen->kdkmpn;
            $collection->nmkmpn = $komponen->nmkmpn;
            $collection->jns_kmpn = $komponen->jns_kmpn;
            $collection->satuan = $komponen->satuan;
            $collection->vol = $komponen->vol;
            $collection->rli_fisik = $komponen->rli_fisik;
            $collection->alo = $komponen->alo;
            $collection->serapan = $komponen->serapan;
            $collection->stts_plksn = $komponen->stts_plksn;
            $collection->prcntg_plksn = $komponen->prcntg_plksn;

            $collection->save();
        }



        return $this->returnJsonSuccess("Data stored successfully", $data);
    }
    
}
