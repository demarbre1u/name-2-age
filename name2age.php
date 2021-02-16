<?php

namespace Name2Age\Command;

/**
 * A command that determines how old a person is likely to be based on a given name
 * 
 * How to use:
 * $name2age = new \Name2Age\Command\Name2AgeCommand();
 * $result = $name2age->run();
 * 
 * @package Name2Age\Command
 * @author Allan DEMARBRE <demarbreallan.dev@gmail.com>
 * @version 1.0
 */
class Name2AgeCommand {
    /**
     * Path to the dataset
     * 
     * @var string $FILE_PATH
     */
    private static string $FILE_PATH = './nat2019.csv';

    /**
     * Number of params passed to the command
     * 
     * @var int $param_nb
     */
    private int $param_nb;

    /**
     * Name of the command
     * 
     * @var string $cmd_name
     */
    private string $cmd_name;

    /**
     * Array of params passed to the command
     * 
     * @var array $argv
     */
    private array $argv;

    /**
     * Constructor of the command class
     * 
     * @param array $argv
     *      Array containing the arguments passed to the command
     */
    public function __construct(array $argv) {
        $this->param_nb = count($argv);
        $this->cmd_name = $argv[0];
        $this->argv = $argv;
    }

    /**
     * Runs the command
     * 
     * @return bool 
     *      Whether the command was successful or not 
     */
    public function run() : bool {
        // If the script is not executed in a CLI, do nothing
        if(! $this->isCLI()) {
            echo $this->colorText('Error', 'red') . ": this script is a command line tool, it should be used in a CLI\n\r";
            return false;
        }

        // If there are no params specified 
        if($this->param_nb <= 1 || $this->param_nb > 2) {
            echo $this->colorText('Error', 'red') . ": Incorrect number of params\n\r";
            $this->printHelp();
            return false;
        }

        // If there are no params specified 
        if($this->param_nb === 2) {
            $second_param = $this->argv[1];
            switch($second_param) {
                case '-h':
                case '--help':
                    $this->printHelp(true);
                    break;
                default: 
                    return  $this->getAgeFromName($second_param);
                    break;
            }
        }

        return true;
    }

    /**
     * Determines if the command is running in a CLI
     * 
     * @return bool
     *      Whether the command is running in a CLI or not
     */
    private function isCLI() : bool {
        return php_sapi_name() === 'cli';
    }

    /**
     * Determines an age from a given name
     * 
     * @param string $name
     *      The name of the person whose age is to be determined
     * 
     * @return bool
     *      Whether an error occured or not 
     */
    // 
    private function getAgeFromName(string $name) : bool {
        $formated_name = strtoupper($this->stripAccents($name));

        $number_by_year = [];
        $handle = fopen(self::$FILE_PATH, "r");
        while (($row = fgetcsv($handle, 0, ";")) !== FALSE) {
            $current_name = $row[1];

            if($current_name !== $formated_name) {
                continue;
            }

            $current_year = $row[2];
            $current_number = $row[3];
            $number_by_year[$current_year] = $current_number;
        }
        fclose($handle);

        if(count($number_by_year) === 0) {
            echo $this->colorText('Success', 'green') . ": No data found for the name '$formated_name'.\n\r";
            return true;
        }

        arsort($number_by_year);

        $most_likely_year = array_key_first($number_by_year);
        $current_year = (new \DateTime())->format('Y');
        $age = (int) $current_year - (int) $most_likely_year;

        echo $this->colorText('Success', 'green') . ": '$name' is most likely to be $age years old.\n\r";
    
        return true;
    }

    /**
     * Prints the "help" text to the CLI
     * 
     * @param bool $full_help
     *      Whether to display the full help text or just a part of it
     * 
     * @return void
     */
    private function printHelp(bool $full_help = false) : void {
        if($full_help) {
            echo "\n\rThis command line tool is written in PHP.\n\r";
            echo "It determines how old a person is most likely to be based on a given name.\n\r\n\r";
            
            echo "Usage:\n\r";
            echo "  $this->cmd_name [--help, -h]\n\r";
            echo "      --help, -h : displays this text\n\r";
            echo "\n\r";
            echo "  $this->cmd_name name\n\r";
            echo "      name : the name of the person\n\r\n\r"; 
        } else {
            echo "Usage: $this->cmd_name name\n\r";
            echo "For more info, please use '$this->cmd_name -h' or '$this->cmd_name --help' \n\n";
        }
    }

    /**
     * Takes a string and adds colors to it
     * 
     * @param string $text
     *      The text to add color to
     * @param string $color
     *      The color to add to the text
     * @param string $default_color
     *      The color the non-colored part of the text should be
     * 
     * @return string 
     *      The colored text
     */
    // 
    private function colorText(string $text, string $color, ?string $default_color = 'white') : string {
        $color_to_code = [
            'white' => "\e[97m", 
            'green' => "\e[32m", 
            'light_green' => "\e[92m", 
            'red' => "\e[31m", 
            'yellow' => "\e[33m", 
            'cyan' => "\e[36m",
        ];

        return $color_to_code[$color] . $text . $color_to_code[$default_color];
    }

    /**
     * Strips accents from a string
     * 
     * @param string $str 
     *      The string to strip accents from
     * 
     * @return string
     *      The string with accents stripped from it
     */
    private function stripAccents(string $str) : string {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 
            'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 
            'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 
            'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 
            'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' 
        ];
        return strtr($str, $unwanted_array);
    }
}

// Running the command
$name2age_command = new \Name2Age\Command\Name2AgeCommand($argv);
$result = $name2age_command->run();

exit((int) $result);