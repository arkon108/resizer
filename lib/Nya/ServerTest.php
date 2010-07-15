<?php

/**
 * Bunch of methods for testing the server environment
 * @author Saša Tomislav Mataić <sasa.tomislav [ AT ] mataic.com>
 *
 */
class ServerTest
{
    /**
     * Is it possible to call exec()
     * return bool is exec enabled
     * @author <arharp [ AT ] gmail.com>
     * @author <sasa.tomislav [ AT ] mataic.com>
     */
    public static function isExecEnabled() {
      return !in_array('exec', explode(', ', ini_get('disable_functions')));
    }
}