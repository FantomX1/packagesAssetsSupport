1) WIP (check origi script call dtables) - allow to call simply by a command without a wrapper script like

 List of parameters
  -w, --webdir    -  public web directory document root, where to symlink the assets
  -p, --package    -  current package working directory
  -o, --ownAssetsDir    -  Own assets directory


-1 -  -w - WebDir parameter not supplied, is mandatory, exiting ...
root@4d5d53247275:/var/www/html/fantomx1/ToolMasterForeman# PHP_IDE_CONFIG="serverName=server_name" php vendor/fantomx1/packages-assets-support/initAssets.php  -w=examples/assets -p=./ -o=./testAssets

2 ) done, error codes handling

3 ) find dir reversely from shortcut to the long name -0 => -ownPackageDir


4 ) (Note: In future can be added functionality of publishing only the specific asset part of the other library,
however this library is not intended to solve intricacies of other libraries assets, since they can share
just this library to solve their issues, though it might be added later)

5 ) automatically regard the current package - remove param p?, only if it in our library so not passed parameter , done

6 ) add unknown parameter supplied

7 ) separate nubered indents by dots

8 ) refactor other object plus inner loop and closures and eventually exceptions flow of condtions and exxceptions types else clauses without negative
interuptions


old readme

```
/var/www/html/fantomx1/ToolMasterForeman# php vendor/fantomx1/packages-assets-support/initAssets.php  -w=examples/assets -p=./ -o=./testAssets
```
- where the "-p -package" - references relatively the curret package it is used in (toolmasterForeman)
- where the "-w -webdir" - comma separated references the directories where to distribute/publish assets using symlinks
- -- where the "-o -ownPackageDir" - defines own relative assets directory if we wish to publish this way also our assets directory, 
this is almost exclusively used if we use own project as a library (obsolete, now automatically takes getcwd) --
