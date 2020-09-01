<?php

// @TODO: just pass further args when include though basically gloablly avalable, and used
// better in the realated directory than to put it elsewhere and pass dirctoyr imho



//$params = (new \fantomx1\CliParamsParser())->parse();
//$params = new \fantomx1\CliParametersParser\CliParamsParser();
//$params = new \fantomx1\CliParamsParser();


// struggles being called not from own dr
//include "vendor/autoload.php";

// @TODO: must be installed also the package which needs autoload, or addressing relativelly when servering as a package, or symlinked or reworking as a composer command

include __DIR__."/vendor/autoload.php";

//$params= new \fantomx1\CliParametersParser\CliParamsParser();

//$params= (new \fantomx1\CliParamsParser())->parse();


$packageAssetsSupport = new \fantomx1\PackagesAssetsSupport();
// run without params to be gotten via command line
$packageAssetsSupport->run();


