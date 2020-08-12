<?php


namespace fantomx1;


/**
 * Class PackagesAssetsSupport
 * @package fantomx1
 */
class PackagesAssetsSupport extends PackageAssetsAbstract
{


    /**
     * @var string
     */
    public  static $packagesAssetsSubdir = "packageAssets";


    /**
     * @var array
     */
    private $errors = [
        -1 => " -w - WebDir parameter not supplied, is mandatory, exiting ...",
//        -2 => ' -p - current package dir not specified',
        //-3 => '-o - own assets dir not specified'
        PackagesAssetsSupport:: ERROR_MIN4 => ' The specified "ownAssetsDir" directory does not exist'
    ];


    /**
     *
     */
    const ERROR_MIN4 =-4;

    /**
     *
     */
    private function listAvailableParams()
    {

        $paramsList = include "availableParams.php";

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
    public function actionFailedWebdirCondition(&$messageCounter)
    {
        return -1;
        echo str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m -w - WebDir parameter not supplied, is mandatory, exiting ...\033[0m  \n";
        return false;
    }

    /**
     * run without params to be gotten via command line
     * @param $currentPackageDir
     * @param $ownPackageAssets
     */
    public function run($currentPackageDir = null, $ownPackageAssetsDir = null)
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

        $messageCounter = 0;

        try {

            $this->checks(
                $currentPackageDir,
                $ownPackageAssetsDir,
                $params,
                $messageCounter
            );


//            if ($params['p'] == "." || $params['p'] == "./") {
//
//                // it will be the dir, it was called from
//                $params['p'] = getcwd();
//            }

            // mandatory params , means, we are calling directly from this library, not a wrapping script
            if (!$currentPackageDir) {
                $currentPackageDir =  static::getNonsymlinkedCwd();
            }

            if (isset($params['o'])) {
                $ownPackageAssetsDir = $params['o'];

//                $dir = PackagesAssetsSupport::getNonSymlinkedPath($ownPackageAssetsDir);
//                //if (!is_dir($currentPackageDir .'/'.$ownPackageAssetsDir)) {
//                if (!is_dir($dir)) {
//                    throw new \Exception("", -4);
//                }

            }



            echo 'Current package dir     = "'.$currentPackageDir.'"'." \n";
            echo 'Own package assets dir  = "'.$ownPackageAssetsDir.'"'." \n";

            $assetPackages = [];

            if (file_exists($currentPackageDir . "/assetPackages.json")) {

                $assetPackages = file_get_contents($currentPackageDir . "/assetPackages.json");

                $assetPackages = json_decode($assetPackages, true);

                $assetPackages = $assetPackages['packages'];
            }

            $this->installAssetPackages($currentPackageDir, $assetPackages, $messageCounter);

            // workaround for call func array
            $action = ["distributePackagesAssets", [$currentPackageDir, $params, &$messageCounter, $assetPackages, $ownPackageAssetsDir]];

            function() {
                $this->distributePackagesAssets();
            };

            call_user_func_array([$this,$action[0]], $action[1]);

        } catch (\Exception $e) {

            echo $e->getCode(). ' - ' . $this->errors[$e->getCode()] ." \n";
            //echo $e->getCode();
            return;

        }

        //$this->$action($messageCounter)
    }


    /**
     * @param $currentPackageDir
     * @param $ownPackageAssetsDir
     * @param $params
     * @param $messageCounter
     * @throws \Exception
     */
    public function checks($currentPackageDir, $ownPackageAssetsDir, $params, &$messageCounter)
    {

        $error = '';
        // a throw a odchytit excepsn len na namapovanie error cisla na spravu, ale  throw -1, catch -1 = "error message", echo error message ale aj if not error
        if (empty($params['w'])) {
            $error =  $this->actionFailedWebdirCondition($messageCounter);
        }

        if ($error) {
            // map the error
            throw new \Exception('', $error);
            // if else if coudl nto connect a ife lse chyby else typy excepsnien davat i ked mohli rovno vypisat excepsn kontent
        }
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

        echo  str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Probing asset packages to install ...\033[0m  \n";

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
            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m All asset packages are already installed, continuing...\033[0m  \n";
        }
    }




    /**
     * @param $currentPackageDir
     * @param array $params
     * @param $messageCounter
     * @param $assetPackages
     * @param $out
     * @return array
     */
    private function distributePackagesAssets(
        $currentPackageDir,
        array $params,
        &$messageCounter,
        $assetPackages,
        $ownPackageAssetsDir
    )
    {
        $webDir = $params['w'];

        $webDirs = explode(",", $webDir);

        $this->m($messageCounter, " \e[31m Creating dirs for symlinks and symlinking - \033[0m  ".$webDir." \n", [ 'webDir' => $webDir ]);

        $messageCounter*=10; echo "\n";
        foreach ($webDirs as $webDir) {
            $messageCounterBp = $messageCounter;


            $this->m($messageCounter, " \e[31m Deploying to the web directory - \033[0m  ".$webDir." \n", [ 'webDir' => $webDir ]);

            $h = new SymlinkDeployer();
            $h->run($currentPackageDir, $messageCounter, $assetPackages, $webDir, $ownPackageAssetsDir);
            // if it went over
        }

        $messageCounter = floor($messageCounter/10)+1; echo "\n";
    }

}
