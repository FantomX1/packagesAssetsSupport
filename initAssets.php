<?php

// @TODO: just pass further args when include though basically gloablly avalable, and used
// better in the realated directory than to put it elsewhere and pass dirctoyr imho



//$params = (new \fantomx1\CliParamsParser())->parse();
//$params = new \fantomx1\CliParametersParser\CliParamsParser();
//$params = new \fantomx1\CliParamsParser();


include "vendor/autoload.php";

//$params= new \fantomx1\CliParametersParser\CliParamsParser();

//$params= (new \fantomx1\CliParamsParser())->parse();


$packageAssetsSupport = new \fantomx1\PackagesAssetsSupport();
// run without params to be gotten via command line
$packageAssetsSupport->run();


