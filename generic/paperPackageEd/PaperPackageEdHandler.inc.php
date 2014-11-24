<?php

/**
 * @file PaperPackageEdHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.paperPackageEd
 * @class PaperPackageEdHandler
 *
 * Find the content and display the appropriate page
 *
 */

import('classes.handler.Handler');

class PaperPackageEdHandler extends Handler {

       function index(){
                     Request::redirect(null, null, 'PaperPackageEdPlugin', Request::getRequestedOp());
                     //  Request::redirect(null, null, 'view', Request::getRequestedOp());
       }

        function view($plugin,$args){
//      $this->validate();
         if ( !$plugin ) {
             Request::redirect(null, 'index');
         }
        $this->setupTemplate(true);

        error_log('OJS - PaperPackageEdHandler: Jetzt sind wir hier');
        if ( is_object($plugin) ) {
        error_log('OJS - PaperPackageEdHandler: Plugin is set');
        $plugin->display($args);
        }
        }
}

?>		