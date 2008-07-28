<?php

/**
 *  classname:    Blog
 *  scope:        PUBLIC
 *  author:       Claus Beerta <claus@beerta.de>
**/

class Blog extends Controller
{
    /**
     * This is just an empty controller, '/blog/' is actually wordpress
     */
    function __construct ()
    {
        parent::Controller();
    }
    
    function index()
    {
        redirect('/blog/');
    }
}

?>
