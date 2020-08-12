<?php



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
