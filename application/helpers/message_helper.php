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


function debug_popup( $message )
{
    if ( !is_string($message) )
    {
        $message = print_r($message, True);
    }

    $txt  = '<div style="font-size: small;">';
    $txt .= '<pre>'.strtr($message, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/')).'</pre>';
    $txt .= '</div>';
    
    $js = '
        <script type="text/javascript">
    //    <![CDATA[
            _console = window.open("","Debug Console","width=680,height=600,resizable,scrollbars=yes");
            _console.document.write(\''.$txt.'\');
            _console.document.close();
    //      ]]>
        </script>
    ';
    
    echo $js;
}

?>
