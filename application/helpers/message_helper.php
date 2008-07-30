<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function msg_forward($heading, $message, $destination, $delay = 5, $template = 'error_general')
{
    $CI =& get_instance();

    $message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';
    $message .= '<p>Click <a href="'.$destination.'">Here</a> to continue.</p>';
    header("Refresh:{$delay};url=".$destination);
    include(APPPATH.'errors/'.$template.EXT);
    return;
}


?>
