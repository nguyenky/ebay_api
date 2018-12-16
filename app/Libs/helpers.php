<?php

/**
 * Helper functions
 * @author leewanvu@gmail.com
 */

if (!function_exists('randomPassword')) {
    /**
      * Random password
      * @return string
      */
    function randomPassword()
    {
        return str_shuffle(substr(str_shuffle("~!@#$%^&*()_+"), 0, 3) . substr(str_shuffle("qwertyuiopasdfghjklzxcvbnm"), 0, 3) . substr(str_shuffle("QWERTYUIOPASDFGHJKLZXCVBNM"), 0, 3));
    }
}

if (!function_exists('infolog')) {
  /**
   * Make old server domain
   * @param string $clientUrl
   */
  function infolog($lbl,$var=NULL)
  {
    if(env("APP_DEBUG", true) && !request("debug",true)===FALSE){
        if($var===NULL){
            dump($lbl);
        }else{
            dump($lbl,$var);
        }
    }
    if($var===NULL){
        info($lbl);
    }else{
        info($lbl,[$var]);
    }
  }
}

if (!function_exists('dump_err')) {
    /**
     * Make old server domain
     * @param string $clientUrl
     */
    function dump_err($lbl,$var=NULL)
    {
        if(env("APP_DEBUG", true) && !request("debug",true)===FALSE){
            print("<div style='font: 12px Menlo, Monaco, Consolas, monospace;padding:5px;border:1px solid #f5c6cb;color:#721c24;background-color: #f8d7da'>$lbl</div>");
            if($var!==NULL){
                dump($var);
            }
        }
    }
}

if (!function_exists('dump_warn')) {
    /**
     * Make old server domain
     * @param string $clientUrl
     */
    function dump_warn($lbl,$var=NULL)
    {
        if(env("APP_DEBUG", true) && !request("debug",true)===FALSE){
            print("<div style='font: 12px Menlo, Monaco, Consolas, monospace;padding:5px;border:1px solid #ffeeba;color:#856404;background-color: #fff3cd'>$lbl</div>");
            if($var!==NULL){
                dump($var);
            }
        }
    }
}