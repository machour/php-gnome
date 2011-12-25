<?php
/**
 * Zenity class definition file
 *
 * Mehdi Achour <machour@gmail.com>
 */

/**
 * Use ZenityWrapper for execution
 */
require_once 'ZenityWrapper.php';

/**
 * Zenity - A simple Zenity PHP wrapper
 *
 * @todo Add helper methods for global options settings
 * 
 * This class extends the ZenityWrapper class and implements a simplier interface
 * for the end user
 */
class Zenity extends ZenityWrapper 
{

    /**
     * Displays a notification in the notification bar
     *
     * @param string $text Set the dialog text
     *
     * @todo Add support for --listen option
     */
    public function showNotification($text) {
        return parent::notification(array('text' => $text));
    }

    /**
     * Displays a confirmation dialog
     *
     * @param string $text Set the dialog text
     * @param boolean $nowrap Do not enable text wrapping in the dialog
     * @return Returns TRUE if the user clicked the Yes button, FALSE otherwise
     */
    public function question($text, $nowrap = false) {
        $args = array('text' => $text);
        if ($nowrap) {
            $args['nowrap'] = true;
        } 
        return parent::question($args);
    }

    /**
     * Displays an information
     *
     * @param string $text Set the dialog text
     * @param boolean $nowrap Do not enable text wrapping in the dialog
     * @return boolean TRUE on success, FALSE on failure
     */
    public function info($text, $nowrap = false) {
        $args = array('text' => $text);
        if ($nowrap) {
            $args['nowrap'] = true;
        } 
        return parent::info($args);
    }

    /**
     * Displays a warning
     *
     * @param string $text Set the dialog text
     * @param boolean $nowrap Do not enable text wrapping in the dialog
     * @return boolean TRUE on success, FALSE on failure
     */
    public function warning($text, $nowrap = false) {
        $args = array('text' => $text);
        if ($nowrap) {
            $args['nowrap'] = true;
        }
        return parent::warning($args);
    }
    
    /**
     * Displays an error
     *
     * @param string $text Set the dialog text
     * @param boolean $nowrap Do not enable text wrapping in the dialog
     * @return boolean TRUE on success, FALSE on failure
     */
    public function error($text, $nowrap = false) {
        $args = array('text' => $text);
        if ($nowrap) {
            $args['nowrap'] = true;
        } 
        return parent::error($args);
    }

    /**
     * Displays a password entry dialog
     *
     * @param string $text Set the dialog text
     * @param string $default The default entry text
     * @return string The typed password
     */
    public function inputPassword($text, $default = '') {
        $args = array('text' => $text, 'hide-text' => true);
        if ($default) {
            $args['entry-text'] = $default;
        }
        return parent::entry($args);
    }
    
    /**
     * Displays a plain text entry dialog
     *
     * @param string $text Set the dialog text
     * @param string $default The default entry text
     * @return string The typed text
     */
    public function inputText($text, $default = '') {
        $args = array('text' => $text);
        if ($default) {
            $args['entry-text'] = $default;
        }
        return parent::entry($args);
    }

    /**
     * Displays a text info dialog
     *
     * @param boolean $editable Specify that the dialog contents are editable
     * @param string $filename Set the file to display in the dialog
     * @return string The dialog text
     */
    public function inputMultilineText($editable = false, $filename = false) {
        $args = array();
        if ($editable) {
            $args['editable'] = true;
        }
        if ($filename && is_file($filename) && is_readable($filename)) {
            $args['filename'] = $filename;
        }
        return parent::textInfo($args);
    }

    /**
     * @todo Finalize this
     */
    public function selectFiles($text) {
         return parent::fileSelection(array('text' => $text, 'multiple' => true));
    }

    /**
     * @todo Finalize this
     */
    public function selectFile($text) {
         return parent::fileSelection(array('text' => $text));
    }

    /**
     * @todo Finalize this
     */
    public function selectDirectories($text) {
         return parent::fileSelection(array('text' => $text, 'multiple' => true, 'directory' => true));
    }

    /**
     * @todo Finalize this
     */
    public function selectDirectory($text) {
         return parent::fileSelection(array('text' => $text, 'directory' => true));
    }

    /**
     * Displays a list dialog
     *
     * @todo Add support for --editable --hide-column -print-column
     * @param string $text Set the dialog text
     * @param array $fieldsNames An array of fields names
     * @param array $fieldsValues An array of fields values
     * @param boolean $multiple Allow multiple selections
     * @param array $defaults An array of default selection flags, one entry per row
     * @param string $extra_label The optional checkbox or radio column label
     * @param string $type One of 'checklist' or 'radiolist'
     * @return mixed A single or array of selected index(es) in $fieldsValues, or
     * FALSE if no rows were selected
     */
    public function selectList($text, $fieldsNames, $fieldsValues, $multiple = false, $defaults = array(), $extra_label = '', $type = '') {
         $args = array('text' => $text);
         
         // Add and hide the internal id column 
         array_unshift($fieldsNames, '@@@PHPZENITYID@@@');
         
         if ($type == 'checklist' || $type == 'radiolist') {
             $args[$type] = true;
             array_unshift($fieldsNames, $extra_label);
             $args['hide-column'] = 2;
         } else {
             $args['hide-column'] = 1;
         }
         $args['column'] = $fieldsNames;
         $i = 0;
         foreach ($fieldsValues as &$field) {
             array_unshift($field, $i);
             if (!empty($defaults)) {
                 if (isset($defaults[$i]) && $defaults[$i]) {
                     array_unshift($field, 'TRUE');
                 } else {
                     array_unshift($field, 'FALSE');
                 }
             }
             $i++;
         }

         $args['value']  = $fieldsValues;
         
         if ($multiple) {
             $args['multiple'] = true;
         }
         $args['separator'] = '@@@PHPZENITYSEP@@@';
         $ret = parent::doList($args);
         if ($multiple) {
             $ret = explode($args['separator'], $ret);
             array_walk($ret, 'intval');
             return $ret;
         } else {
             return (int) $ret;
         }
    }

    /**
     * Displays a radio list dialog
     *
     * @param string $text Set the dialog text
     * @param array $fieldsNames An array of fields names
     * @param array $fieldsValues An array of fields values
     * @param string $radioLabel The radio column label
     * @param array $defaults An array of default selection flags, one entry per row
     * @return mixed A single or array of selected index(es) in $fieldsValues, or
     * FALSE if no rows were selected
     */
    public function selectRadiolist($text, $fieldsNames, $fieldsValues, $radioLabel, $defaults = array()) {
        return $this->selectList($text, $fieldsNames, $fieldsValues, false, $defaults, $radioLabel, 'radiolist');
    }

    /**
     * Displays a list dialog
     *
     * @param string $text Set the dialog text
     * @param array $fieldsNames An array of fields names
     * @param array $fieldsValues An array of fields values
     * @param string $checkLabel The checkbox column label
     * @param boolean $multiple Allow multiple selections
     * @param array $defaults An array of default selection flags, one entry per row
     * @return mixed A single or array of selected index(es) in $fieldsValues, or
     * FALSE if no rows were selected
     */
    public function selectChecklist($text, $fieldsNames, $fieldsValues, $checkLabel, $multiple, $defaults = array()) {
        return $this->selectList($text, $fieldsNames, $fieldsValues, $multiple, $defaults, $checkLabel, 'checklist');
    }

    /**
     * Display a calendar dialog
     *
     * @param string $text Specifies the text that is displayed in the progress dialog
     * @param string $format The date format to be retrieved. See strftime() for syntax.
     * @param int $day Set the calendar day
     * @param int $month Set the calendar month
     * @param int $year Set the calendar year
     * @return string The selected date, formatted accordingly to $format
     */
    public function calendar($text, $format = "%d/%m/%Y", $day = false, $month = false, $year = false) {
        $args = array('text' => $text, 'date-format' => $format);
        if ($day !== false) {
            $args['day'] = $day;
        }
        if ($month !== false) {
            $args['month'] = $month;
        }
        if ($year !== false) {
            $args['year'] = $year;
        }
        return parent::calendar($args);
    }

    /**
     * Display a progress dialog
     *
     * @param string $text Specifies the text that is displayed in the progress dialog
     * @param int $initial Specifies the initial percentage that is set in the progress dialog
     * @param boolean $pulsate Specifies that the progress bar pulsates until an EOF character is read from standard input
     * @param boolean $autoclose Closes the progress dialog when 100% has been reached
     * @param boolean $input Specifies the path to the executable script that will send input to the Progress dialog
     * @param boolean $autokill Kill the parent process (the $input script execution) is the Cancel button is clicked
     */
    public function showProgress($text, $initial = 0, $pulsate = false, $autoclose = false, $input = false, $autokill = false) {
        $args = array('text' => $text);
        $args['percentage'] = $initial;
        if ($pulsate) {
            $args['pulsate'] = true;
        }
        if ($autokill) {
            $args['auto-kill'] = true;
        }
        if ($autoclose) {
            $args['auto-close'] = true;
        }
        return parent::progress($args, $input);
    }

    /**
     * Display a scale dialog
     *
     * @todo Add --print-partial and --hide-value
     * @param string $text Specifies the text that is displayed in the progress dialog
     * @param int $min Sets the minimum value of the scale
     * @param int $max Sets the maximum value of the scale
     * @param int $step Sets the step size of the scale
     * @param int $default Sets the initial value
     * @return int The selected value
     */
    public function scale($text, $min = 0, $max = 100, $step = 1, $default = false) {
        $args = array('text' => $text);
        $args['min-value'] = $min;
        $args['max-value'] = $max;
        $args['step'] = $step;
        if ($default !== false) {
            $args['value'] = $default;
        }
        return parent::scale($args);
    }
}
