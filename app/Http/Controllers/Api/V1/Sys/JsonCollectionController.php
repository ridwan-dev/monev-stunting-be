<?php

namespace App\Http\Controllers\Api\V1\Sys;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\MvJsonCollection;
use App\Models\Pub\MvJsonCollection2;

class JsonCollectionController extends BaseController
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $collection = MvJsonCollection::select('id', 'endpoint')->get();
        return $this->returnJsonSuccess("Api's fetched successfully", $collection);
    }

    public function getByHash(Request $request, $hashId){
        $id = \UrlHash::decodeId('cirgobanggocir', $hashId, 50);
        $collection = MvJsonCollection::where('id', $id)->first();

        $collection2 = MvJsonCollection2::where('id',$id)->first();

        $col = json_encode(array_merge(json_decode($collection->json_agg,TRUE),json_decode($collection2->json_agg,TRUE)));
       // dd($col);
      //  dd($collection);
        return $this->returnJsonSuccess("Api's fetched successfully", json_decode($col));
    }
}
