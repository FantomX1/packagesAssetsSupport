<?php


namespace fantomx1;


/**
 * Class PackagesAssetsSupport
 * @package fantomx1
 */
class PackagesAssetsSupport
{


    /**
     * @var string
     */
    public $packagesAssetsSubdir = "packageAssets";


    /**
     *
     */
    private function listAvailableParams()
    {

        $paramsList =
            [
                'webdir'=>' public web directory document root, where to symlink the assets',
            ];


        echo "\n";
        echo " \033[31mList of parameters \033[0m     \n";
        foreach ($paramsList as $k => $listItem) {
            echo " \033[31m ". "-" . $k[0] . ", --" . $k ." \033[0m  " . " - " . $listItem."    \n";
            //echo "-" . $k[0] . ", --" . $k . " - " . $listItem . "\n";


        }

        echo "\n\n";
    }

    /**
     * @param $messageCounter
     */
    public function actionFailedWebdirCondition($messageCounter)
    {
        echo ++$messageCounter . " \033[31m -w - WebDir parameter not supplied, is mandatory, exiting ...\033[0m  \n";
        die();
    }

    /**
     * @param $packageDir
     * @param $ownPackageAssets
     */
    public function run($packageDir, $ownPackageAssetsDir)
    {

        $params = (new CliParamsParser())->parse();

        // @TODO: library name by default vendor name but where to allow different customization
        // @TODO: __DIR__ passed but if to customize though to allow pass by parameter script outside
        // not accomonding yet other applications only those using this system, others packages only asset part? we copy entire
        // but only symlink, does not copy entire, though could expose php to public

        // if define action, handle later
        if (empty($params)) {

            $this->listAvailableParams();
            // show always
//            die();
        }


        $assetPackages = file_get_contents($packageDir . "/assetPackages.json");

        $assetPackages = json_decode($assetPackages, true);

        $assetPackages = $assetPackages['packages'];

        $messageCounter = 0;

        $this->installAssetPackages($packageDir, $assetPackages, $messageCounter);

        // workaround for call func array
        $action = ["distributePackagesAssets", [$packageDir, $params, &$messageCounter, $assetPackages, $ownPackageAssetsDir]];

        function() {
            $this->distributePackagesAssets();
        };


        //private function distributePackagesAssets($packageDir, array $params, &$messageCounter, $assetPackages)
        if (empty($params['w'])) {
            $action= ["actionFailedWebdirCondition",[$messageCounter]];
            function() {
                $this->actionFailedWebdirCondition();
            };
        }

        call_user_func_array([$this,$action[0]], $action[1]);
        //$this->$action($messageCounter)
    }

    /**
     * @param $packageDir
     * @param $out
     * @return array
     */
    private function installAssetPackages($packageDir,$assetPackages, &$messageCounter)
    {
        $composer = file_get_contents($packageDir . "/composer.json");

        $composer = json_decode($composer, true);

        echo ++$messageCounter . " \033[31m Probing asset packages to install ...\033[0m  \n";

        $iAssetPackages = 0;
        $installed      = false;
        foreach ($assetPackages as $package) {

            $iAssetPackages++;

            echo $iAssetPackages . ".) - probing package \e[31m" . $package . "\e[0m \n";

            if (!isset($composer['require'][$package])) {

                $installed = true;
                exec('composer require ' . $package, $out);
                echo $out;
            }

        }


        if ($iAssetPackages && !$installed) {


            echo ++$messageCounter . " \033[31m All asset packages are already installed, continuing...\033[0m  \n";
        }
    }

    /**
     * @param $packageDir
     * @param array $params
     * @param $messageCounter
     * @param $assetPackages
     * @param $out
     * @return array
     */
    private function distributePackagesAssets(
        $packageDir,
        array $params,
        &$messageCounter,
        $assetPackages,
        $ownPackageAssetsDir
    )
    {
        $webDir = $params['w'];

        $webDirs = explode(",", $webDir);


        foreach ($webDirs as $webDir) {
            $messageCounterBp = $messageCounter;
            $messageCounter*=10;

            echo ++$messageCounter . " \e[31m Deploying to the web directory - \033[0m  ".$webDir." \n";
            $this->deployPackagesAssets($packageDir, $messageCounter, $assetPackages, $webDir, $ownPackageAssetsDir);
            $messageCounter = $messageCounterBp+1;

        }



    }

    /**
     * @param $packageDir
     * @param $messageCounter
     * @param $assetPackages
     * @param $webDir
     * @param $out
     * @return array
     */
    private function deployPackagesAssets($packageDir, &$messageCounter, $assetPackages, $webDir)
    {
        if (!is_dir(getcwd() . '/' . $webDir)) {

            echo ++$messageCounter . "  The web directory \e[31m \"" . $webDir . "\" \033[0m  does not exist! Supply a proper directory ,  \e[31m exiting ...\033[0m  \n";
            die();
        }


        $vendorDir = dirname($packageDir, 2) . '/vendor';

        //$prefix='fcrons';

        // dir location of asset per package based on vendor and package name
        $prefix = basename(dirname($packageDir)) . '/' . basename($packageDir);


        foreach ($assetPackages as $package) {

            //$depDir = $vendorDir . '/components/jqueryui/';
            $depDir = $vendorDir . '/' . $package;


            // singular for all using this technique, not to clutter , single assets dir, not for every vendor even though it might happen

            echo ++$messageCounter . " \033[31m Precreating assets folders \033[0m     \n";
            exec(
                'cd ' . $webDir . ' && mkdir -p ' . $this->packagesAssetsSubdir . '/' . $prefix . '/' . dirname($package) . ';',
                $out
            );
            var_dump($out);


            echo ++$messageCounter . " \033[31m Creating symlinks\033[0m ... \n";

            // example
            //$command = 'cd ' . $webDir . '/packageAssets/' . $prefix . '/'.dirname($package).' && ln -s ' . $depDir . '  jqueryui' . "\n";
            $command = 'cd ' . $webDir . '/' . $this->packagesAssetsSubdir . '/' . $prefix . '/' . dirname($package) . ' && ln -s ' . $depDir . '  ' . basename($package) . '' . "\n";
            echo $command;

            exec($command, $out);
            var_dump($out);
        }

    }

}
