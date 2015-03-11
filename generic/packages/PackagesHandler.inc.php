<?php

/**
 * @file PackagesHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.PackagesView
 * @class PackagesHandler
 *
 * Find the content and display the appropriate page
 *
 */

import('classes.handler.Handler');

class PackagesHandler extends Handler {

       function index(){
                     Request::redirect(null, null, 'PackagesPlugin', Request::getRequestedOp());
                     //  Request::redirect(null, null, 'view', Request::getRequestedOp());
       }

        function view($plugin){
//      $this->validate();
         if ( !$plugin ) {
             Request::redirect(null, 'index');
         }
        $this->setupTemplate(true);

        error_log('OJS - PackagesHandler: Jetzt sind wir hier');
        if ( is_object($plugin) ) {
        error_log('OJS - PackagesHandler: Plugin is set');
        $plugin->displayPackages();
        }
        }
}

?>