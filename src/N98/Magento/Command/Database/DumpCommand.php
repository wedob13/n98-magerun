<?php

namespace N98\Magento\Command\Database;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        $this
            ->setName('database:dump')
            ->setAliases(array('db:dump'))
            ->addArgument('filename', InputArgument::OPTIONAL, 'Dump filename')
            ->addOption('only-command', null, InputOption::VALUE_NONE, 'Print only mysqldump command. Do not execute')
            ->setDescription('Dumps database with mysqldump cli client according to informations from local.xml')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);

        $this->writeSection($output, 'Dump MySQL Database');

        if (($fileName = $input->getArgument('filename')) === null)  {
            $dialog = $this->getHelperSet()->get('dialog');
            $fileName = $dialog->ask($output, '<question>Filename for SQL dump:</question>', $this->dbSettings['dbname']);
        }

        if (substr($fileName, -4, 4) !== '.sql') {
            $fileName .= '.sql';
        }

        $exec = 'mysqldump '
            . '-h' . strval($this->dbSettings['host'])
            . ' '
            . '-u' . strval($this->dbSettings['username'])
            . ' '
            . (!strval($this->dbSettings['password'] == '') ? '-p' . $this->dbSettings['password'] . ' ' : '')
            . strval($this->dbSettings['dbname'])
            . ' > '
            . $fileName;

        if ($input->getOption('only-command')) {
            $output->writeln($exec);
        } else {
            $output->writeln('<comment>Start dumping database: <info>' . $this->dbSettings['dbname'] . '</info> to file <info>' . $fileName . '</info>');
            exec($exec, $commandOutput, $returnValue);
            if ($returnValue > 0) {
                $output->writeln('<error>' . implode(PHP_EOL, $commandOutput) . '</error>');
            } else {
                $output->writeln('<info>Finished</info>');
            }
        }
    }

}