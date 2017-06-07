<?php
namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OauthClientCreateCommand extends ContainerAwareCommand
{
    protected function configure ()
    {
        $this
            ->setName('fos:oauth-server:client-create')
            ->setDescription('Creates a new client')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Client name')
            ->addOption('mail', null, InputOption::VALUE_OPTIONAL, 'Contact mail')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'Website')
            ->addOption('redirect-uri', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets the redirect uri. Use multiple times to set multiple uris.', null)
            ->addOption('grant-type', 'gt', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Set allowed grant type. Use multiple times to set multiple grant types', null)
        ;
    }
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $client->setName($input->getOption('name'));
        $client->setMail($input->getOption('mail'));
        $client->setUrl($input->getOption('url'));
        $clientManager->updateClient($client);
        $output->writeln(sprintf('Added a new client with  public id <info>%s</info>.', $client->getPublicId()));
    }
}