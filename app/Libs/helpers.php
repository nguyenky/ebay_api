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
    if(env("APP_DEBUG", true)){
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