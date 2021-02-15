<?php

class Name2AgeCommand {

    private static string $FILE_PATH = './nat2019.csv';
    private int $param_nb = 0;
    private string $cmd_name;
    private array $argv = [];

    public function __construct($argv) {
        $this->param_nb = count($argv);
        $this->cmd_name = $argv[0];
        $this->argv = $argv;
    }

    // Runs the command 
    public function run() : bool {
        // If the script is not executed in a CLI, do nothing
        if(php_sapi_name() !== 'cli') {
            echo $this->colorText('Error', 'red') . ": this script is a command line tool, it should be used in a CLI\n\r";
            return false;
        }

        // If there are no params specified 
        if($this->param_nb <= 1 || $this->param_nb > 2) {
            echo $this->colorText('Error', 'red') . ": Incorrect number of params\n\r";
            $this->printHelp($this->cmd_name);
            return false;
        }

        // If there are no params specified 
        if($this->param_nb === 2) {
            $second_param = $this->argv[1];
            switch($second_param) {
                case '-h':
                case '--help':
                    $this->printHelp($this->cmd_name, true);
                    break;
                default: 
                    return  $this->getAgeFromName($second_param);
                    break;
            }
        }

        return true;
    }

    // Determines an age from a given name
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
        $current_year = (new DateTime())->format('Y');
        $age = (int) $current_year - (int) $most_likely_year;

        echo $this->colorText('Success', 'green') . ": '$name' is most likely to be $age years old.\n\r";
    
        return true;
    }

    // Prints the "help" text to the CLI
    private function printHelp(string $cmd_name, bool $full_help = false) : void {
        if($full_help) {
            echo "\n\rThis command line tool is written in PHP.\n\r";
            echo "It determines the age a person is most likely to be based on a given name.\n\r\n\r";
            
            echo "Usage: $cmd_name [-h, --help] name\n\r";
            echo "   --help, -h : displays this text\n\r";
            echo "   name : the name of the person the age is to be determined\n\r\n\r"; 
        } else {
            echo "Usage: $cmd_name name\n\r";
            echo "For more info, please use '$cmd_name -h' or '$cmd_name --help' \n\n";
        }
    }

    // Takes a string and adds colors to it
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

    // Strips accents from a string
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
$name2age_command = new Name2AgeCommand($argv);
$result = $name2age_command->run();

exit((int) $result);