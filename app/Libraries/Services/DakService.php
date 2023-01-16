<?php

namespace App\Libraries\Services;

use App\Libraries\Services\Core\Service;

class DakService extends Service
{
    protected $baseUri = 'SERVICE_RENJA_DAK_URI';

    public const DAK_URL = 'api/v1/dak-stunting/@?kode_pemda=@&apikey=@';  
    public const DAK_PENGADAAN = 'api/v1/dak-pengadaan/@?apikey=@';
    public const DAK_PEMDA = 'api/v1/dak-wilayah-pemda/@?apikey=@';
    
    protected $useAuthHeader = false;
}
