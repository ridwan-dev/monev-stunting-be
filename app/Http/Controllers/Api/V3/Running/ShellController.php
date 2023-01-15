<?php

namespace App\Http\Controllers\Api\V3\Running;

use App\Http\Controllers\Api\BaseController;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ShellController extends BaseController
{
   public function terminal(){
      //$this->commandFunc("cd /var/www/html/api-dev-v3");
      //$this->commandFunc("sh ../execute.sh");
      $this->commandFunc("cd ..");
      $this->runPull();
      //$this->commandFunc("git config --global --add safe.directory /var/www/html/api-dev-v3");
      //$this->commandFunc("git pull origin develop");
      /* $this->commandFunc("cd /var/www/html/testing-v3");
      $this->commandFunc("git pull");
      $this->commandFunc("cd /var/www/html/dashboard");
      $this->commandFunc("git pull");
      $this->commandFunc("cd /var/www/html/api-dev-v3");
      $this->commandFunc("git pull");
      $this->commandFunc("cd /var/www/html/api-testing-v3");
      $this->commandFunc("git pull");
      $this->commandFunc("cd /var/www/html/api-prod-v3");
      $this->commandFunc("git pull"); */
      
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

   private function runPull()
   {

      $process = new Process(['git', 'pull']);
      $this->info("Running 'git pull'");

      $process->run(function($type, $buffer) {
         $this->pullLog[] = $buffer;

         if($buffer == "Already up to date.\n") {
               $this->alreadyUpToDate = TRUE;
         }
         
      });

      return $process->isSuccessful();

   }

}
