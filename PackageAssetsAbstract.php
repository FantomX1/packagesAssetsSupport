<?php


namespace fantomx1;


/**
 * Class PackageAssetsAbstract
 * @package fantomx1
 */
abstract class PackageAssetsAbstract
{


    /**
     * @param $messageCounter
     * @param $message
     * @param $params
     */
    protected function m(&$messageCounter, $message, $params)
    {
        extract($params);

        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \e[31m Creating dirs for symlinks and symlinking - \033[0m  ".$webDir." \n";

    }



    /**
     * @TODO: renamte to relative, also at package API usages
     * @param $rootDirOfPackageUsedIn
     * @param $packageORpathToAssetsDir
     * @return string
     */
    public static function getAssetsDir($rootDirOfPackageUsedIn, $package, $fromCli = false)
    {

        // though this seems to happen only when wanting to distribute packages of own project
        // which could be anyway then launched from the project directory itself, therefore to consider @TODO:
        $package = str_replace('../','', $package);
        // for relatively passed ownAssetsDir
        // $rootDirOfPackageUsedIn = dirname(__DIR__, 3);



        $path = static::getNonsymlinkedCwd();
        $dirCalled[1]= basename(dirname($path));
        $dirCalled[2]= basename($path);

        // if package goes from command line they have straight the format Word-Word-Word due to packagist
        if (!$fromCli) {
        // a checkni i zo skriptu ale zatial bez skriptu i nespravy dir
            $bt = debug_backtrace();

            $dir = __DIR__;
            $vendorDir = dirname($dir, 2);
            // dynamically find, find own when inline pass though could be in own script

            // replace vendor to handle cases when it's as an example in a non-vendor project or while type=path
            $vendorDir = str_replace("vendor/", "", $vendorDir);

            // still first dir first contact API when called from other script
            $caller = $bt[0]['file'] ;
            $caller = str_replace("vendor/", "", $caller);


            // this way found root, and trimmed out, so first dirs are vendor and name, whether the called is in vendor dir
            // or the project itself, still to do script inline cli, own script, symlinked, own script migrate too, but maybe deprecated
            $dirCalled = str_replace($vendorDir, "", $caller);

            $dirCalled = explode("/", $dirCalled);


            // if we happen to have it in own, not conforming composer standard name local directory
            $dirCalled[2] = preg_split('/(?=[A-Z])/', $dirCalled[2]);
            $dirCalled[2] = implode("-",$dirCalled[2]);
            $dirCalled[2] = strtolower($dirCalled[2]);
        }



        $prefix = $dirCalled[1].'/'.$dirCalled[2];


        $result = static::$packagesAssetsSubdir . '/' . $prefix;
        if ($package) {
            $result .= '/'.$package;
        }

        // workaround with local


        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $requestUri = $_SERVER['REQUEST_URI'];

        $rewriteModeScript = str_replace($scriptName, '', $requestUri);
        $rewriteModeScript = trim($rewriteModeScript, "/");
        $virSubdirCnt = substr_count($rewriteModeScript,'/');

        $subdir = str_repeat("../", $virSubdirCnt);

        // workaround with local
        $result = str_replace("datatabless/","datatables/", $result);
        $result = $subdir.$result;


        if (!$fromCli && !file_exists($result)) {
            // @TODO: ammend dir
            throw new \Exception(
                "The asset ".$result." does not exist. Did you issue the initAssets.php command listing web-dir
                 where to deploy
                 (cd vendor/fantomx1/datatables && php $(cd ../../../vendor/fantomx1/packages-assets-support && pwd)/initAssets.php -w ../../../public)                 
                 (, or is the library ".$package." listend in assetPackages.json in your package ".$prefix.") ?"
            );
        }


        return $result;
    }


    /**
     * @return mixed
     */
    public static function getNonsymlinkedCwd()
    {
        exec("pwd", $cwd );
        return $cwd[0];
    }

    /**
     * @param $dir
     * @return array|mixed|string
     */
    public static function getNonSymlinkedPath($dir)
    {
        return static::canonicalize(static::getNonsymlinkedCwd().'/'. $dir);
    }


    /**
     * @param $address
     * @return array|mixed|string
     */
    static function canonicalize($address)
    {
        $address = explode('/', $address);
        $keys = array_keys($address, '..');

        foreach($keys AS $keypos => $key)
        {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }

        $address = implode('/', $address);
        $address = str_replace('./', '', $address);

        return $address;
    }




//    /**
//     * @param $currentPackageDir
//     * @param $messageCounter
//     * @param $assetPackages
//     * @param $webDir
//     * @param $out
//     * @return array
//     */
//    private function deployPackagesAssets(
//        $currentPackageDir,
//        &$messageCounter,
//        $assetPackages,
//        $webDir,
//        $ownPackageAssetsDir
//    )
//    {
//        if (!is_dir($currentPackageDir . '/' . $webDir)) {
//
//            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . "  The web directory \e[31m \"" . $webDir . "\" \033[0m  does not exist! Supply a proper directory ,  \e[31m exiting ...\033[0m  \n";
//            die();
//        }
//
//
//        //$vendorDir = dirname($currentPackageDir, 2) . '/vendor';
//        $vendorDir = $currentPackageDir . '/vendor';
//
//        //$prefix='fcrons';
//
//
//        $foldersToCreateAndLink = $assetPackages;
//        // todo relative, absolute not advantage and makes problems for this, renameto folder to create
//        if ($ownPackageAssetsDir) {
//            $foldersToCreateAndLink[] = $ownPackageAssetsDir;
//        }
//
//
//        $this->precreateAssetsFolders($messageCounter, $webDir,$foldersToCreateAndLink, $currentPackageDir);
//
//        $messageCounter *=10;  echo "\n";
//
//        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Creating symlinks for \e[0m \"" . $webDir . "\" \e[31m  webdir \033[0m ... \n";
//
//        $messageCounter *=10;  echo "\n";
//
//        foreach ($foldersToCreateAndLink as $package) {
//
//
//            //$depDir = $vendorDir . '/components/jqueryui/';
//            $depDir = $vendorDir . '/' . $package;
//
//            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " Creating symlink  for \e[31m ! ".$package." !\e[0m  package     \n";
//
//            $relativePackageAssetsDir = dirname($this->getAssetsDir($currentPackageDir, $package));
//
//            if ($package == $ownPackageAssetsDir) {
//                // reuse for not duplicated logic and same output
//                // @TODO: perhaps multiple dirs for js, css, come later
//                // our own assets dir , so . dirname($package)  in it resolves to ./
//                // just direct
//                $depDir = $currentPackageDir . "/" .$ownPackageAssetsDir;
//            }
//
//            // example
//            //$command = 'cd ' . $webDir . '/packageAssets/' . $prefix . '/'.dirname($package).' && ln -s ' . $depDir . '  jqueryui' . "\n";
//            $command = 'cd ' . $webDir . '/' . $relativePackageAssetsDir  . ' && ln -sf ' . $depDir . '  ' . basename($package) . '' . "\n";
//            echo $command;
//
//            exec($command, $out);
//            var_dump($out);
//        }
//
//        $messageCounter /=10;
//        $messageCounter = floor($messageCounter)+1; echo "\n";
//
//        $messageCounter /=10;
//        $messageCounter = floor($messageCounter)+1; echo "\n";
//
//    }
//
//
//    /**
//     * @param $messageCounter
//     * @param $webDir
//     * @param $assetPackages
//     * @param $currentPackageDir
//     */
//    protected function precreateAssetsFolders(&$messageCounter, $webDir, $assetPackages, $currentPackageDir)
//    {
//
//
//        // or just pass similary a multiple webdirs with
////        @TODO:
////        if (basename(dirname($webDir, 3)) == "vendor") {
////            $webDir = "/../../".$webDir;
////        }
//
//        // @TODO: webdir check above when used in here?
//
//        // @TODO: add create dir and symlink dir, goes deep, separate to other class
//
//        $messageCounter *=10;  echo "\n";
//        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Precreating assets folders for \e[0m \"" . $webDir . "\" \e[31m  webdir \033[0m     \n";
//
//        $messageCounter *=10;  echo "\n";
//
//        foreach ($assetPackages as $package) {
//
//
//
//
//            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " Precreating assets folder for \e[31m ! ".$package." !\e[0m  package     \n";
//
//
//            $relativePackageAssetsDir = dirname($this->getAssetsDir($currentPackageDir, $package));
//
//            // singular for all using this technique, not to clutter , single assets dir, not for every vendor even though it might happen
//
//
//
//            // cd backend/web/    packageAssets         /fantomx1/datatables/   components (/jqueryui this one not yet)
//            // till here static his->packagesAssetsSubdir . '/' . $prefix . '/
//            exec(
//                'cd ' . $webDir . ' && mkdir -p ' . $relativePackageAssetsDir . ';',
//                $out
//            );
//            var_dump($out);
//        }
//        $messageCounter /=10;
//        $messageCounter = floor($messageCounter)+1; echo "\n";
//
//
//        $messageCounter /=10;
//        $messageCounter = floor($messageCounter)+1; echo "\n";
//
//    }

}
