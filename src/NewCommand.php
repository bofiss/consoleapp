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
         ->addArgument('name', InputArgument::REQUIRED )
         ->addOption('v', null, InputOption::VALUE_OPTIONAL, "Version par defaut", "7");
  }

   public function execute(InputInterface $input, OutputInterface $output){

     $directory = getcwd().'/'.$input->getArgument('name');

     $output->writeln('<info>Crafting application...</info>');
     $this->assertApplicationDoesNotExist($directory, $output);

     // download latest version of drupal 7.
      $this->download($zipFile=$this->makeFileName(), $input->getOption('v'))
          ->extract($zipFile, $directory, $input->getOption('v'))
          ->cleanUp($zipFile);
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


   private function download($zipFile, $options){
     //$this->client->setDefaultOption('verify', false);

     if($options == "8"){
       $response = $this->client->get('https://ftp.drupal.org/files/projects/drupal-8.0.5.tar.gz')->getBody();
     }else{
       $response = $this->client->get('https://ftp.drupal.org/files/projects/drupal-7.43.tar.gz')->getBody();
     }
     file_put_contents($zipFile, $response);
     return $this;
   }


   private function extract($zipFile, $directory, $options){
     $distill = new Distill();
     $distill->extract($zipFile, '/tmp');
     if($options == 8){
       $this->recurse_copy('/tmp/drupal-8.0.5', $directory);
     }else{
       $this->recurse_copy('/tmp/drupal-7.43', $directory);
     }
     $this->cleanUp('/tmp');
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

  private function cleanUp($zipfile){
    @chmod($zipfile, 0777);
    @unlink($zipfile);
    return $this;
  }

}
