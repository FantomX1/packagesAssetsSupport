# packagesAssetsSupport
handles assets for generic(=non-framework specific) php packages



## Sample usage of installing the assets (via the library command initAssets):

```
root@a3c3ba4d7b46:/var/www/html/myproject.com# PHP_IDE_CONFIG="serverName=server_name"  php vendor/fantomx1/datatabless/initAssets.php -w backend/web/,frontend/web
```
various symlink locations for redistributions must be comma separated like 'backend/web, frontend/web'
The list of asset packages in regard must be listed in a json file named 'assetPackages.json' in your project. Those will be handled by this asset manager library.

You must have 1) an own script  created in your project, or 2) calling it directly via a command line.
1) using own script (and calling it the way above mentioned)
```
// fantomx1/datatables/initAssets.php
include "vendor/autoload.php";

$packageAssetsSupport = new \fantomx1\PackagesAssetsSupport();
// the 2nd voluntary parameter is the relative path of own assets to handle
$packageAssetsSupport->run(__DIR__, "assets");
```

2) calling it directly from a specific library - 
```
/var/www/html/fantomx1/ToolMasterForeman# php vendor/fantomx1/packages-assets-support/initAssets.php  -w=examples/assets -o=./testAssets
```
~~- where the "-p -package" - references relatively the curret package it is used in (toolmasterForeman)~~ -p is now automatic as a current working directory
- where the "-w -webdir" - comma separated references the directories where to distribute/publish assets using symlinks
- where the "-o --ownAssetsDir" - the directory of the package's own assets to  publish 

All the available command line parameters are listed inside [availableParams.php](availableParams.php) file.
   
(
then using inside a packages view like for other library assets
```
<?php
$packageAssetsSupport = new \fantomx1\PackagesAssetsSupport();
$pathToAssets 
?>

<script type="text/javascript" src="<?php echo $assetsHandler->getAssetsDir($rootDir, "components/jqueryui/).'/js/jsfile.js'; ?>">
</script>

// for own library assets not passing the vendor assets library name

<script type="text/javascript" src="<?php echo $assetsHandler->getAssetsDir($rootDir, '').'/assets/js/jsfile.js'; ?>">
</script>
```


where the $rootDir is the root dir of the our package in regard , it is being used in

)
## Output example:

![Showcase](showcase.jpg)

Output:
```
root@a3c3ba4d7b46:/var/www/html/myproject.com# PHP_IDE_CONFIG="serverName=server_name"  php vendor/fantomx1/datatabless/initAssets.php -w backend/web/,frontend/web
  1  Probing asset packages to install ...
1.) - probing package components/jqueryui
  2  All asset packages are already installed, continuing...
  3  Creating dirs for symlinks and symlinking -   backend/web/,frontend/web

   31  Deploying to the web directory -   backend/web/

    311  Precreating assets folders for  "backend/web/"   webdir

     3111 Precreating assets folder for  ! components/jqueryui !  package
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:251:
array(0) {
}
     3112 Precreating assets folder for  ! assets !  package
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:251:
array(0) {
}



    321  Creating symlinks for  "backend/web/"   webdir  ...

     3211 Creating symlink  for  ! components/jqueryui !  package
cd backend/web//packageAssets/fantomx1/datatables/components && ln -s /var/www/html/vendor/assets  jqueryui
ln: failed to create symbolic link 'jqueryui': File exists
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:288:
array(0) {
}
     3212 Creating symlink  for  ! assets !  package
cd backend/web//packageAssets/fantomx1/datatables/. && ln -s /var/www/html/fantomx1/datatables/assets  assets
ln: failed to create symbolic link 'assets/assets': File exists
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:288:
array(0) {
}


   34  Deploying to the web directory -   frontend/web

    341  Precreating assets folders for  "frontend/web"   webdir

     3411 Precreating assets folder for  ! components/jqueryui !  package
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:251:
array(0) {
}
     3412 Precreating assets folder for  ! assets !  package
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:251:
array(0) {
}



    351  Creating symlinks for  "frontend/web"   webdir  ...

     3511 Creating symlink  for  ! components/jqueryui !  package
cd frontend/web/packageAssets/fantomx1/datatables/components && ln -s /var/www/html/vendor/assets  jqueryui
ln: failed to create symbolic link 'jqueryui': File exists
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:288:
array(0) {
}
     3512 Creating symlink  for  ! assets !  package
cd frontend/web/packageAssets/fantomx1/datatables/. && ln -s /var/www/html/fantomx1/datatables/assets  assets
ln: failed to create symbolic link 'assets/assets': File exists
/var/www/html/fantomx1/PackagesAssetsSupport/PackagesAssetsSupport.php:288:
array(0) {
}
```

