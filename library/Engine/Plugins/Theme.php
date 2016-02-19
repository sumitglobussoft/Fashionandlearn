<?php
class Engine_Plugins_Theme extends Zend_Controller_Plugin_Abstract{
    
    /**
    * This function is called once after router shutdown. It automatically
    * sets the layout for the default MVC or a module-specific layout.
    *
    * @param Zend_Controller_Request_Abstract $request
    */
    public function routeShutdown(Zend_Controller_Request_Abstract $request){
        $layout = Zend_Layout::getMvcInstance();
        $module = strtolower($request->getModuleName());
        $controller = strtolower($request->getControllerName());
        
        $view = $layout->getView();
        
        $objCore = Engine_Core_Core::getInstance();
        $this->_appSetting = $objCore->getAppSetting();
        
        $objThemesModel = Application_Model_Themes::getInstance();
        $activeTheme = $objThemesModel->getActiveTheme();
        
        if($activeTheme){
            
            $filename = 'themes/'.$activeTheme['name'];
            
            if(is_dir($filename)){ // check theme exist or not
                $theme = $activeTheme['name'];
            }else{ throw new Exception('Selected Theme Not Exist'); }
            
        }else{
            $theme = $this->_appSetting->layout->theme;
        }
        
        
        
        if ($module != "admin") {
            
            $view->theme = $theme;
            // send theme basepath to the layout
            $view->theme_base_path = '/themes/' . $theme . '/skin';
            
            $layoutPath = 'themes/' . $theme . '/app/layouts/scripts/'; // set selected theme layout path
           
            $viewPath   = 'themes/' . $theme . '/app/views/'.$module.'/'.$controller;// set selected theme view
            
            $layout->setLayoutPath($layoutPath); // set theme layout
            $layout->getView()->setBasePath($viewPath);// set theme view
            
        }else{
                 
            $layout->setLayout('adminlayout');
        }
    }
}
?>
