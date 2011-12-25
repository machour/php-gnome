<?php
/**
 * Zenity wrapper definition file
 *
 * @author Mehdi Achour <machour@gmail.com>
 */

/**
 * The ZenityWrapper is a PHP wrapper for the Zenity application
 */
class ZenityWrapper {
    /**
     * Executable zenity file path
     *
     * @var string
     */
    private $_zenity_bin = '/usr/bin/zenity';
    /**
     * Zenity error state
     *
     * @var boolean
     */
    private $_zenity_error = false;
    /**
     * Debug mode
     *
     * @var boolean
     */
    private $_debug = true;
    /**
     * Allowed commands
     *
     * @var array
     */
    private $_commands = array(
        'calendar'       => true,
        'entry'          => true,
        'error'          => true,
        'file-selection' => true,
        'info'           => true,
        'list'           => true,
        'notification'   => true,
        'progress'       => true,
        'question'       => true,
        'text-info'      => true,
        'warning'        => true,
        'scale'          => true,
        'version'        => true,
        'help'           => true,
    );
    /**
     * Command aliases, to respect PHP syntax in user land
     *
     * @var array
     */
    private $_aliases = array(
        'textInfo' => 'text-info',
        'fileSelection' => 'file-selection',
        'doList' => 'list',
    );
    /**
     * General options for Zenity
     *
     * @var array
     */
    private $_generalOptions = array(
        'title' => '',
        'window-icon' => '',
        'width' => '',
        'height' => '',
        'timeout' => '',
    );
    /**
     * Magic __call method, which catches all calls to the wrapper
     *
     * @param string $method The method called
     * @param array $params The parameters given for the $method call
     *
     * @return boolean The call status
     */
    public function __call($method, $params) {
        $input = false;
        if (!isset($params[0])) {
            $params = array();
        } else {
            if (isset($params[1]) && is_executable($params[1])) {
                $input = $params[1];
            }
            $params = $params[0];
        }
        if (isset($this->_aliases[$method])) {
            $method = $this->_aliases[$method];
        }
        if (isset($this->_commands[$method])) {
            $cmd = $this->_getCommand($method, $params, $input);
            if ($this->_debug) {
                echo 'Executing: ' . $cmd;
            }
            exec($cmd, $output, $retval);
            $output = implode("\n", $output);
            if ($retval == 0) {
                return $this->_parseResponse($method, $retval, $output);
            } else {
                trigger_error(sprintf('Zenity error %d: %s', $retval, $output)); 
            }
        } else {
            trigger_error(sprintf('%s is not a valid Zenity command', $method));
        }
        $this->_zenity_error = true;
        return false;
    }
    /**
     * Tells if the last call to zenity was successfull or not
     *
     * @return boolean TRUE if the call was successfull, FALSE otherwise
     */
    public function wasSuccessful() {
        return !$this->_zenity_error;
    }
    /**
     * Sets a general option
     *
     * @param string $name The option name
     * @param string $value The option value
     * @return boolean TRUE if the option exists and was set, FALSE otherwise
     */
    public function setGeneralOption($name, $value) {
        if (isset($this->_generalOptions[$name])) {
            $this->_generalOptions[$name] = $value;
            return true;
        }
        return false;
    }
    /**
     * Unsets a general option
     *
     * @param string $name The option name
     * @return boolean TRUE if the option exists and was unset, FALSE otherwise
     */
    public function unsetGeneralOption($name) {
        if (isset($this->_generalOptions[$name])) {
            $this->_generalOptions[$name] = '';
            return true;
        }
        return false;
    }
    /**
     * Parses Zenity response
     *
     * @param string $command The Zenity command
     * @param int $retval The Zenity binary return value
     * @param string $output The Zenity output string
     * @return mixed The parsed response
     */
    private function _parseResponse($command, $retval, $output) {
        if ($this->_debug) echo <<<EOD

Return value : $retval
Output       :
$output

EOD;
        switch ($command) {
            case 'question':
                return $retval == 0;

            default:
                return $output;
        }
    }
    /**
     * Builds the Zenity command line
     *
     * @param string $command The Zenity command
     * @param array $params The $command parameters
     * @param string $input The input script
     * @return string The command line to be exec()uted
     */
    private function _getCommand($command, $params = array(), $input = false) {
        $args = '--' . $command;
        foreach ($this->_generalOptions as $k => $v) {
            if ($v !== '') {
                $args .= sprintf(' --%s=%s', $k, escapeshellarg($v));
            }
        }
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $vv) {
                    if ($command == 'list' && $k == 'value') {
                        foreach ($vv as $vvv) {
                            $args .= sprintf(' %s', escapeshellarg($vvv));
                        }
                    } else { 
                        $args .= sprintf(' --%s=%s', $k, escapeshellarg($vv));
                    }
                }
            } else {
                $args .= sprintf(' --%s=%s', $k, escapeshellarg($v));
            }
        } 
        return sprintf('%s %s %s 2>&1', ($input ? $input . ' | ' : ''), $this->_zenity_bin, $args);
    }   
}
