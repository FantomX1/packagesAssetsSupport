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
        echo str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m -w - WebDir parameter not supplied, is mandatory, exiting ...\033[0m  \n";
        die();
    }

    /**
     * @param $currentPackageDir
     * @param $ownPackageAssets
     */
    public function run($currentPackageDir, $ownPackageAssetsDir)
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


        $assetPackages = file_get_contents($currentPackageDir . "/assetPackages.json");

        $assetPackages = json_decode($assetPackages, true);

        $assetPackages = $assetPackages['packages'];

        $messageCounter = 0;

        $this->installAssetPackages($currentPackageDir, $assetPackages, $messageCounter);

        // workaround for call func array
        $action = ["distributePackagesAssets", [$currentPackageDir, $params, &$messageCounter, $assetPackages, $ownPackageAssetsDir]];

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

        // @TODO: documeent roo? probably not as assets dir is next to assets so if its document root,
        // or no, the path fits, regards web_dir, though if include file, adjust to app, not a task of, just cause of coping
        // as with depth
        // dir location of asset per package based on vendor and package name, for current package
        $prefix = basename(dirname($rootDirOfPackageUsedIn)) . '/' . basename($rootDirOfPackageUsedIn);

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
        if (!is_dir(getcwd() . '/' . $webDir)) {

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

}
