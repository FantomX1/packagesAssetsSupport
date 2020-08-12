# packagesAssetsSupport
handles assets for generic(=non-framework specific) php packages



## Sample usage of installing the assets (via the library command initAssets):

There are 2 approaches

You must have 1) **an own script created** in your project, or 2) calling it **directly calling this original package command** .
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
root@4d5d53247275:/var/www/html/wapp/myproject.com/vendor/fantomx1/datatabless# php /var/www/html/fantomx1/packagesAssetsSupport/initAssets.php -w=../../../backend/web,../../../frontend/web -o=datatables/assets   
```
~~- where the "-p -package" - references relatively the curret package it is used in (toolmasterForeman)~~ -p is now automatic as a current working directory
- where the "-w -webdir" - comma separated references the directories where to distribute/publish assets using symlinks
- where the "-o --ownAssetsDir" - the directory of the package's own assets to  publish 

All the available command line parameters are listed inside [availableParams.php](availableParams.php) file.
   
(
then using inside a packages view file/class for accessing the symlinked assets directory
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


~~where the $rootDir is the root dir of the our package in regard , it is being used in~~ - as of now v0.91 rootdir calculated automatically

)
## Output example:

![Showcase](showcase.png)

Output:
