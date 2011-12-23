<?php
/**
 * This file is part of the php-gnome project
 *
 * @author Mehdi Achour <machour@gmail.com>
 * @package Nautilus
 */

/**
 * NautilusScript is meant to be extended by your nautilus scripts
 *
 */
class NautilusScript {

    private $_selectedFiles = array();
    private $_selectedDirs  = array();
    private $_currentDirectory = null;

    /**
     * Constructor.
     *
     */
    public function __construct() {
        if ($this->isCli()) {
            $this->_currentDirectory = 'file://' . getcwd();
            if ($_SERVER['argc'] > 1) {
                $argv = $_SERVER['argv'];
                array_shift($argv);
                foreach ($argv as $path) {
                    if (is_dir($path)) {
                        $this->_selectedDirs[] = 'file://' . $path;
                    } elseif (is_file($path)) {
                        $this->_selectedFiles[] = 'file://' . $path;
                    }
                }
                sort($this->_selectedDirs);
                sort($this->_selectedFiles);
            }
        } else {
            $this->_currentDirectory = $_ENV['NAUTILUS_SCRIPT_CURRENT_URI'];
            $value = trim($_ENV['NAUTILUS_SCRIPT_SELECTED_URIS']);
            if ($value != '') {
                foreach (explode("\n", $value) as $path) {
                    if (is_dir($path)) {
                        $this->_selectedDirs[] = $path;
                    } else {
                        $this->_selectedFiles[] = $path;
                    }
                }
            }
        }
    }

    /**
     * Gets the current directory, i.e. the directory from which the script
     * is executed
     *
     * @return string The current directory
     */
    public function getCurrentDirectory() {
        return $this->_currentDirectory;
    }

    /**
     * Tells if the script is being executed in cli mode
     *
     * @return boolean TRUE if the script is used in php cli mode, FALSE
     * otherwise
     */
    public function isCli() {
        return !isset($_ENV['NAUTILUS_SCRIPT_SELECTED_URIS']);
    }

    /**
     * Gets the selected files when invoking the script
     *
     * @return array An array of selected files
     */
    public function getSelectedFiles() {
        return $this->_selectedFiles;
    }

    /**
     * Tells if the script was called with selected files
     *
     * @return boolean TRUE if there were selected files, FALSE otherwise
     */
    public function haveSelectedFiles() {
        return count($this->getSelectedFiles()) > 0;
    }

    /**
     * Gets the selected folders when invoking the script
     *
     * @return array An array of selected folders
     */
    public function getSelectedFolders() {
        return $this->_selectedDirs;
    }

    /**
     * Tells if the script was called with selected folders
     *
     * @return boolean TRUE if there were selected folders, FALSE otherwise
     */
    public function haveSelectedFolders() {
        return count($this->getSelectedFolders()) > 0;
    }

}
