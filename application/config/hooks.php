<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
    /*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['pre_controller'] = array(
        'class'    => 'MZ_Commonfunction_hook',
        'function' => 'hook',
        'filename' => 'MZ_Commonfunction_hook.php',
        'filepath' => 'hooks'
); 
    
$hook['post_controller'] = array(
        'class' => 'MZ_Render_hook', 
        'function' => 'render', 
        'filename' => 'MZ_Render_hook.php', 
        'filepath' => 'hooks'
);

$hook['post_controller_constructor'] = array(
        'class' => 'MZ_Autologin_hook',
        'function' => 'login',
        'filename' => 'MZ_Autologin_hook.php',
        'filepath' => 'hooks'
);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
