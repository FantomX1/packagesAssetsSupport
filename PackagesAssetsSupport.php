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
     * @var array
     */
    private $errors = [
        -1 => " -w - WebDir parameter not supplied, is mandatory, exiting ...",
//        -2 => ' -p - current package dir not specified',
        //-3 => '-o - own assets dir not specified'
        -4 => ' The specified "ownAssetsDir" directory does not exist'
    ];


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
                $currentPackageDir =  getcwd();;
            }

            if (isset($params['o'])) {
                $ownPackageAssetsDir = $params['o'];


                if (!is_dir($currentPackageDir .'/'.$ownPackageAssetsDir)) {
                    throw new \Exception("", -4);
                }

            }

        } catch (\Exception $e) {

            echo $e->getCode(). ' - ' . $this->errors[$e->getCode()] ." \n";
            //echo $e->getCode();
            return;

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


        //private function distributePackagesAssets($packageDir, array $params, &$messageCounter, $assetPackages)
//        if (empty($params['w'])) {
//            $action= ["actionFailedWebdirCondition",[$messageCounter]];
//            function() {
//                $this->actionFailedWebdirCondition();
//            };
//        }


//        if (!$this->checks()) {
//            // return value is not descriptive, but waas populated to view, we decide what is error or logged, no need exception or error object then
//            return false;
//        }




        call_user_func_array([$this,$action[0]], $action[1]);
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



        //if (!$error && empty($params['p']) && !$currentPackageDir) {
//        if (!$error && !$currentPackageDir && empty($params['p']) ) {
//            $error = -2;
//        }


//        if (!$error && !$ownPackageAssetsDir && empty($params['o']) ) {
//            $error = -3;
//        }




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

        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \e[31m Creating dirs for symlinks and symlinking - \033[0m  ".$webDir." \n";

        $messageCounter*=10; echo "\n";
        foreach ($webDirs as $webDir) {
            $messageCounterBp = $messageCounter;


            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \e[31m Deploying to the web directory - \033[0m  ".$webDir." \n";
            $this->deployPackagesAssets($currentPackageDir, $messageCounter, $assetPackages, $webDir, $ownPackageAssetsDir);
            // if it went over


        }

        $messageCounter = floor($messageCounter/10)+1; echo "\n";
    }


    /**
     * @param $rootDirOfPackageUsedIn
     * @param $packageORpathToAssetsDir
     * @return string
     */
    public function getAssetsDir($rootDirOfPackageUsedIn, $package)
    {

        // $rootDirOfPackageUsedIn = dirname(__DIR__, 3);

        $bt = debug_backtrace();


        $vendorDir = dirname(__DIR__, 2);

        // replace vendor to handle cases when it's as an example in a non-vendor project or while type=path
        $vendorDir = str_replace("vendor/", "", $vendorDir);

        $caller = $bt[0]['file'] ;
        $caller = str_replace("vendor/", "", $caller);

        $dirCalled = str_replace($vendorDir, "", $caller);
        $dirCalled = explode("/", $dirCalled);

        $prefix = $dirCalled[1].'/'.$dirCalled[2];

        $result = $this->packagesAssetsSubdir . '/' . $prefix;
        if ($package) {
            $result .= '/'.$package;
        }

        return $result;
    }



    /**
     * @param $currentPackageDir
     * @param $messageCounter
     * @param $assetPackages
     * @param $webDir
     * @param $out
     * @return array
     */
    private function deployPackagesAssets(
        $currentPackageDir,
        &$messageCounter,
        $assetPackages,
        $webDir,
        $ownPackageAssetsDir
    )
    {
        if (!is_dir($currentPackageDir . '/' . $webDir)) {

            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . "  The web directory \e[31m \"" . $webDir . "\" \033[0m  does not exist! Supply a proper directory ,  \e[31m exiting ...\033[0m  \n";
            die();
        }


        //$vendorDir = dirname($currentPackageDir, 2) . '/vendor';
        $vendorDir = $currentPackageDir . '/vendor';

        //$prefix='fcrons';


        // todo relative, absolute not advantage and makes problems for this
        if ($ownPackageAssetsDir) {
            $assetPackages[] = $ownPackageAssetsDir;
        }


        $this->precreateAssetsFolders($messageCounter, $webDir,$assetPackages, $currentPackageDir);

        $messageCounter *=10;  echo "\n";

        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Creating symlinks for \e[0m \"" . $webDir . "\" \e[31m  webdir \033[0m ... \n";

        $messageCounter *=10;  echo "\n";

        foreach ($assetPackages as $package) {


            //$depDir = $vendorDir . '/components/jqueryui/';
            $depDir = $vendorDir . '/' . $package;

            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " Creating symlink  for \e[31m ! ".$package." !\e[0m  package     \n";

            $relativePackageAssetsDir = dirname($this->getAssetsDir($currentPackageDir, $package));

            if ($package == $ownPackageAssetsDir) {
                // reuse for not duplicated logic and same output
                // @TODO: perhaps multiple dirs for js, css, come later
                // our own assets dir , so . dirname($package)  in it resolves to ./
                // just direct
                $depDir = $currentPackageDir . "/" .$ownPackageAssetsDir;
            }

            // example
            //$command = 'cd ' . $webDir . '/packageAssets/' . $prefix . '/'.dirname($package).' && ln -s ' . $depDir . '  jqueryui' . "\n";
            $command = 'cd ' . $webDir . '/' . $relativePackageAssetsDir  . ' && ln -sf ' . $depDir . '  ' . basename($package) . '' . "\n";
            echo $command;

            exec($command, $out);
            var_dump($out);
        }

        $messageCounter /=10;
        $messageCounter = floor($messageCounter)+1; echo "\n";

        $messageCounter /=10;
        $messageCounter = floor($messageCounter)+1; echo "\n";

    }


    /**
     * @param $messageCounter
     * @param $webDir
     * @param $assetPackages
     * @param $currentPackageDir
     */
    protected function precreateAssetsFolders(&$messageCounter, $webDir, $assetPackages, $currentPackageDir)
    {

        // @TODO: add create dir and symlink dir, goes deep, separate to other class

        $messageCounter *=10;  echo "\n";
        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Precreating assets folders for \e[0m \"" . $webDir . "\" \e[31m  webdir \033[0m     \n";

        $messageCounter *=10;  echo "\n";

        foreach ($assetPackages as $package) {




            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " Precreating assets folder for \e[31m ! ".$package." !\e[0m  package     \n";


            $relativePackageAssetsDir = dirname($this->getAssetsDir($currentPackageDir, $package));

            // singular for all using this technique, not to clutter , single assets dir, not for every vendor even though it might happen



            // cd backend/web/    packageAssets         /fantomx1/datatables/   components (/jqueryui this one not yet)
            // till here static his->packagesAssetsSubdir . '/' . $prefix . '/
            exec(
                'cd ' . $webDir . ' && mkdir -p ' . $relativePackageAssetsDir . ';',
                $out
            );
            var_dump($out);
        }
        $messageCounter /=10;
        $messageCounter = floor($messageCounter)+1; echo "\n";


        $messageCounter /=10;
        $messageCounter = floor($messageCounter)+1; echo "\n";

    }

}
