<?php


namespace fantomx1;


/**
 * Class SymlinkDeployer
 * @package fantomx1
 */
class SymlinkDeployer
{


    /**
     * @param $currentPackageDir
     * @param $messageCounter
     * @param $assetPackages
     * @param $webDir
     * @param $ownPackageAssetsDir
     */
    public function run(

        $currentPackageDir,
        &$messageCounter,
        $assetPackages,
        $webDir,
        $ownPackageAssetsDir
    )
    {

        // as php auto translates symlinks what is odd
//        exec("pwd", $cwd );
//        $path = $this->canonicalize($cwd[0].'/'. $webDir);


        if (!is_dir(PackagesAssetsSupport::getNonSymlinkedPath($webDir))) {

            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . "  The web directory \e[31m \"" . $webDir . "\" \033[0m  does not exist! Supply a proper directory ,  \e[31m exiting ...\033[0m  \n";
            die();
        }


        //$vendorDir = dirname($currentPackageDir, 2) . '/vendor';
        $vendorDir = $currentPackageDir . '/vendor';

        //$prefix='fcrons';

        $ownPackageAssetsDir = explode(",", $ownPackageAssetsDir);


        $foldersToCreateAndLink = $assetPackages;
        // todo relative, absolute not advantage and makes problems for this, renameto folder to create


        foreach ($ownPackageAssetsDir  as $package) {


            $dir = PackagesAssetsSupport::getNonSymlinkedPath($package);
            //if (!is_dir($currentPackageDir .'/'.$ownPackageAssetsDir)) {
            // @TODO: share as error object as in datatables
            if (!is_dir($dir)) {
                throw new \Exception("", PackagesAssetsSupport::ERROR_MIN4);
            }

            $foldersToCreateAndLink[] = $package;

        //if ($ownPackageAssetsDir) {

        //}

        }

        $this->precreateAssetsFolders($messageCounter, $webDir,$foldersToCreateAndLink, $currentPackageDir);

        $messageCounter *=10;  echo "\n";

        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Creating symlinks for \e[0m \"" . $webDir . "\" \e[31m  webdir \033[0m ... \n";

        $messageCounter *=10;  echo "\n";

        foreach ($foldersToCreateAndLink as $package) {


            //$depDir = $vendorDir . '/components/jqueryui/';
            $depDir = $vendorDir . '/' . $package;

            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " Creating symlink  for \e[31m ! ".$package." !\e[0m  package     \n";

            $relativePackageAssetsDir = dirname(PackagesAssetsSupport::getAssetsDir($currentPackageDir, $package, 1));

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


        // or just pass similary a multiple webdirs with
//        @TODO:
//        if (basename(dirname($webDir, 3)) == "vendor") {
//            $webDir = "/../../".$webDir;
//        }

        // @TODO: webdir check above when used in here?

        // @TODO: add create dir and symlink dir, goes deep, separate to other class

        $messageCounter *=10;  echo "\n";
        echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " \033[31m Precreating assets folders for \e[0m \"" . $webDir . "\" \e[31m  webdir \033[0m     \n";

        $messageCounter *=10;  echo "\n";

        foreach ($assetPackages as $package) {




            echo   str_pad(" ", strlen($messageCounter)+1).++$messageCounter . " Precreating assets folder for \e[31m ! ".$package." !\e[0m  package     \n";


            $relativePackageAssetsDir = dirname(PackagesAssetsSupport::getAssetsDir($currentPackageDir, $package, 1));

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
