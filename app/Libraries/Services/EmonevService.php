<?php

namespace App\Libraries\Services;

use App\Libraries\Services\Core\Service;

class EmonevService extends Service
{
    protected $baseUri = 'SERVICE_KOMPONEN_URI';

    //public const KOMPONEN_URL = 'api/Stunting/KmpnStkr?id=kgm055&token=p9cOUH9C2gIjhg9wUoMXXsAA2xnoumgcjfF4qScrMicLnR6';    
    public const KOMPONEN_URL = 'api/Stunting/KmpnStkr?';
    public const HEADERS = ['token' => 'qWhTMaNeFjpp29b6ehfCxxww7turYCVco1V11w3uEgHwfGqiMvK9Q4vOnN3a','id' => 'kgm055'];
    
}
