<?php
namespace Acme;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use GuzzleHttp\ClientInterface;
use Distill\Distill;

class NewCommand extends Command {

  private $client;

  public function __construct(ClientInterface $client){
     $this->client = $client;
     parent::__construct();
  }

   public function configure(){
     $this->setName("new")
         ->setDescription('Create a new Drupal application.')
         ->addArgument('name', InputArgument::REQUIRED );
  }

   public function execute(InputInterface $input, OutputInterface $output){

     $directory = getcwd().'/'.$input->getArgument('name');
     $this->assertApplicationDoesNotExist($directory, $output);

     // download latest version of drupal 7.
      $this->download($zipFile=$this->makeFileName())
          ->extract($zipFile, $directory);
          unlink($zipFile);
      $message  = "Application ready !!";
      $output->writeln("<comment>{$message}</comment>");
   }

   private function assertApplicationDoesNotExist($directory, OutputInterface $output){
     if(is_dir($directory)){
       $message = 'Application already exist!';
       $output->writeln("<error>{$message}</error>");
       exit(1);
     }
     mkdir($directory);
     return $this;
   }

   private function makefileName(){
     return getcwd().'/drupal_'.md5(time().uniqid()).'.tar.gz';
   }


   private function download($zipFile){
     $response = $this->client->get('http://drupalfr.org/sites/default/files/drupal-7.latest.tar.gz')->getBody();
     file_put_contents($zipFile, $response);
     return $this;
   }


   private function extract($zipFile, $directory){
     $distill = new Distill();
     $distill->extract($zipFile, '/temp');
     $this->recurse_copy('/temp/drupal-7.43', $directory);
     return $this;
   }

   private function recurse_copy($src,$dst) {
    $dir = opendir($src);
     @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
    return $this;
  }

}
