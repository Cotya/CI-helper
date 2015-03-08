<?php
/**
 * 
 * 
 * 
 * 
 */

namespace Cotya\CI_Helper\Magento\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TravisCISkeletonCreateCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('travis:skeleton:create')
            ->setDescription('creates a basic skeleton for travis CI')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will overwrite existing travis file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = '.travis.yml';
        if (!$input->getOption('force')) {
            if (file_exists($file)) {
                $output->writeln("<error>\"$file\" already exists</error>");
                die(1);
            }
        }

        file_put_contents($file, $this->getTemplate());
        $output->writeln("<fg=green>\"$file\" created</fg=green>");
        
    }
    
    protected function getTemplate()
    {
        $template = <<<'YAML'
language: php
php:
#  - 5.3
  - 5.5
  - 5.6
env:
  - MAGENTO=1.8.1.0
  - MAGENTO=1.9.1.0
  - MAGENTO=2.0.42.0-beta10
matrix:
  fast_finish: true
  include:
    - php: 5.6
      env: TEST_SUITE=Static
  allow_failures:
    - env: TEST_SUITE=Static
before_install:
  - mkdir test-root/
install:
  - composer install --dev
before_script:
  - php vendor/bin/cotya-setup_travis.php
script:
  - > 
    sh -c "if [ '$TEST_SUITE' = 'Static' ]; then
      ./vendor/bin/phpcs -v --standard=PSR2 --ignore="/test-root|/lib|/shell|/vendor|/m1" ./;
    else
      vendor/bin/phpunit
    fi"

YAML;
        return $template;
    }
}
