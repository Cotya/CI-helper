<?php
/**
 *
 *
 *
 *
 */


$magentoTargetVersion = getenv('MAGENTO');
$moduleRoot = realpath(__DIR__.'/../../');

echo "moduleRoot: $moduleRoot";

$passthru = function($shellCommand) {
    echo $shellCommand.PHP_EOL;
    passthru($shellCommand);
};

if (strpos($magentoTargetVersion, '1.')===0) {
    $passthru('vendor/bin/mage-ci install test-root $MAGENTO magento_test -c -t -r http://mage-ci.ecomdev.org');
    $passthru('vendor/bin/mage-ci install-module test-root '.$moduleRoot);
} elseif (strpos($magentoTargetVersion, '2.')===0) {
    $passthru('wget https://github.com/magento/magento2/archive/0.42.0-beta10.tar.gz');
    $passthru('tar -xzf 0.42.0-beta10.tar.gz');
    rename('magento2-0.42.0-beta10', 'test-root');
    rename(
        'test-root/dev/tests/integration/etc/install-config-mysql.travis.php.dist',
        'test-root/dev/tests/integration/etc/install-config-mysql.php'
    );
    chdir('test-root');
    $passthru('composer install --no-interaction --dev');

    chdir($moduleRoot);
    $moduleName = simplexml_load_file('etc/module.xml')->xpath('module')[0]->getName();
    $moduleDir = str_replace('_', DIRECTORY_SEPARATOR, $moduleName);
    $passthru('cp -s ./ ./test-root/app/code/'.$moduleDir);
    
    //$passthru('php test-root/setup/index.php module-enable --modules='.$moduleName );
    
} else {
    fwrite(STDERR, "Version Identifier '$magentoTargetVersion' not recogniezed".PHP_EOL);
    die(1);
};
