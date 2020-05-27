<?php

/**
 * retrieve info from session
 *
 * @param string $key if null, all session data are available
 * @param mixed $default
 * @return mixed
 */
function session(string $key = null,$default = null){
    return ($key !== null)
        ?app()->session->get($key,$default)
        :app()->session->all();
}

/**
 * Set session value
 *
 * @param string $key
 * @param [type] $value
 * @return void
 */
function setSession(string $key,$value){
    return app()->session->set($key,$value);
}

/**
 * Clean app session session
 *
 * @return void
 */
function sessionEmpty(){
    app()->session->clera();
}

/**
 * Remove a key from session
 *
 * @return void
 */
function sessionRemove(string $key){
    app()->session->delete($key);
}
