<?php
namespace Acme;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
class DitBonjourCommand extends Command {

   public function configure(){
     $this->setName("ditBonjour")
         ->setDescription('Salutation matinale.')
         ->addArgument('name', InputArgument::REQUIRED, 'Votre nom.' )
         ->addOption('salutation', null, InputOption::VALUE_OPTIONAL, "Remplacer la salutation par defaut", "Bonjour");
   }

   public function execute(InputInterface $input, OutputInterface $output){
     $message = sprintf('%s, %s', $input->getOption('salutation'), $input->getArgument('name'));
     $output->writeln("<info>{$message}</info>");
   }
}
