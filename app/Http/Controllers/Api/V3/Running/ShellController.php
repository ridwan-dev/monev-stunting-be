<?php

namespace App\Http\Controllers\Api\V3\Running;

use App\Http\Controllers\Api\BaseController;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ShellController extends BaseController
{
   public function terminal(){
      $this->commandFunc("dir");
      $this->commandFunc("dir");
      
   }
   
   private function commandFunc($scrp){
      $process = Process::fromShellCommandline($scrp);
      $process->run();
      // executes after the command finishes
      if (!$process->isSuccessful()) {
         throw new ProcessFailedException($process);
      }
      $return = $process->getOutput();
      echo $return;
      return $return;

   }

}
