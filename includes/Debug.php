<?php

namespace RRZE\Legal;

defined('ABSPATH') || exit;

class Debug
{
    /**
     * Log errors by writing to the debug.log file.
     */
    public static function log($input, string $format = 'plain', string $level = 'i')
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        if (in_array(strtolower((string) WP_DEBUG_LOG), ['true', '1'], true)) {
            $logPath = WP_CONTENT_DIR . '/debug.log';
        } elseif (is_string(WP_DEBUG_LOG)) {
            $logPath = WP_DEBUG_LOG;
        } else {
            return;
        }
        if (is_array($input) || is_object($input)) {
            if ($format == 'html') {
                $input = Debug::get_dump_debug($input);
            } elseif ($format == 'vardump') {
                $input = Debug::get_var_dump($input);    
            } else {
                $input = print_r($input, true);
            }
            
        }
        switch (strtolower($level)) {
            case 'e':
            case 'error':
                $level = 'Error';
                break;
            case 'i':
            case 'info':
                $level = 'Info';
                break;
            case 'd':
            case 'debug':
                $level = 'Debug';
                break;
            default:
                $level = 'Info';
        }
        error_log(
            date("[d-M-Y H:i:s \U\T\C]")
                . " WP $level: "
                . basename(__FILE__) . ' '
                . $input
                . PHP_EOL,
            3,
            $logPath
        );
    }
    
    // prrints var dump as variable
    public static function get_var_dump($input) { 
        ob_start(); 
        var_dump($input);
        return "\n" . ob_get_clean();
    }
    
    /*
     * Better Dump.
     */
    public static function get_dump_debug($input, $collapse = false, $console = false, $htmlbreak = true) {
        
        
        $recursive = function($data, $level=0) use (&$recursive, $collapse, $console, $htmlbreak) {
            global $argv;
            $output = '';
            
                
            // $indent = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            $indent = '';  
            $isTerminal = isset($argv);
            if ($console) {
                $isTerminal = true;
            }
            
            $type = !is_string($data) && is_callable($data) ? "Callable" : ucfirst(gettype($data));
            $type_data = null;
            $type_color = null;
            $type_length = null;

            switch ($type) {
                case "String": 
                    $type_color = "";
                    $type_length = strlen($data);
                    $type_data = "\"" . htmlentities($data) . "\""; break;

                case "Double": 
                case "Float": 
                    $type = "Float";
                    $type_color = "#0099c5";
                    $type_length = strlen($data);
                    $type_data = htmlentities($data); break;

                case "Integer": 
                    $type_color = "darkgreen";
                    $type_length = strlen($data);
                    $type_data = htmlentities($data); break;

                case "Boolean": 
                    if ($data) {
                        $type_color = "green";
                    } else {
                        $type_color = "red";
                    }
                    $type_length = strlen($data);
                    $type_data = $data ? "TRUE" : "FALSE"; break;
                
                    

                case "NULL": 
                    $type_length = 0; 
                    break;

                case "Array": 
                    $type_length = count($data);
            }

            if (in_array($type, array("Object", "Array"))) {
                $notEmpty = false;

                foreach($data as $key => $value) {
                    if (!$notEmpty) {
                        $notEmpty = true;

                        if ($isTerminal) {
                            $output .=  $type . ($type_length !== null ? "(" . $type_length . ")" : "")."\n";

                        } else {
                            $id = substr(md5(rand().":".$key.":".$level), 0, 8);
                            $output .=  '<div class="dump_debug">';
                            $output .=  "<strong style='color:blue'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</strong>";
                            $output .=  "<span id=\"plus". $id ."\" style=\"color: red; display: " . ($collapse ? "inline" : "none") . ";\">&nbsp;&#10549;</span>";
                            if ($collapse) {
                                 $output .=  "<div id=\"container". $id ."\" class=\"dump_container\" style=\"display: none; \"><br>";
                            } else {
                                 $output .=  "<div id=\"container". $id ."\" class=\"dump_container\" style=\"display: block;\">";
                            }
                    //         $output .=  "\n";
                           
          
                        }

                        for ($i=0; $i <= $level; $i++) {
                            $output .=  $isTerminal ? "|    " : "$indent";
                        }

                        if ($isTerminal) {
                            $output .=  "\n";
                        }
    
                    }

                    for ($i=0; $i <= $level; $i++) {
                        $output .=  $isTerminal ? "|    " : "$indent";
                    }
                    
                    if ($isTerminal) {
                        $output .=  "[" . $key . "] => ";
                    } else {
                        $output .= "<span class=\"dump_fieldname_col\">";
                        if (is_numeric($key)) {
                            $output .= "[<code class=\"dump_fieldname dump_fieldname_numeric\"'>" . $key . "</code>]";
                        } else {
                            $output .= "[\"<code class=\"dump_fieldname\"'>" . $key . "</code>\"]";                   
                        }
                        $output .= '</span>';
                        $output .= " &#8658; ";
                    }

                    $output .= call_user_func($recursive, $value, $level+1);
                }

                if ($notEmpty) {
                    for ($i=0; $i <= $level; $i++) {
                        $output .=  $isTerminal ? "|    " : "$indent";
                    }

                    if (!$isTerminal) {
                        $output .=  "</div>";
                        $output .=  "</div>";
                    }

                } else {
                    $output .=  $isTerminal ? 
                            $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " : 
                            "<span class=\"dump_value dump_empty_array\">" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";
                }

            } else {
                if ($type_data != null) {
                    if (empty($type_color)) {
                         $output .=  $isTerminal ? $type_data : "<span class=\"dump_value\">" . $type_data . "</span>";
                    } else {
                         $output .=  $isTerminal ? $type_data : "<span class=\"dump_value\" style='color:" . $type_color . "'>" . $type_data . "</span>";
                    }
                    $output .=  $isTerminal ? 
                        $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " : 
                        " <span class=\"dump_typeinfo\">" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>";
                } else {
                    
                    if ($isTerminal) {
                         $output .=  "NULL"; 
                    } else {
                        $output .= '<span class="dump_nullvalue">NULL</span>';
                    }
                     
                }
              

                
            }

            if ($isTerminal) {
                 $output .=  "\n";
            } else {
                 if ($htmlbreak) {
                    $output .=  "<br />";   
                 }
            }
            return $output;
        };

        $output = call_user_func($recursive, $input);
        
        return $output;
    }
    
}
