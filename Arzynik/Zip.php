<?php namespace Arzynik;
use Exception;
use ZipArchive;
class Zip {
    /**
     *
     * @param array $files
     * @param array $params
     * @return string[]
     * @throws Exception
     */
    public function create($files = array(),$params = array()) {

        $this->_destination = $params['destination'];
        $this->_name = $params['name'];
        $this->_path = $this->_destination . $this->_name;

        if(!file_exists($this->_destination)) {
            throw new Exception('Destination directory "' . $this->_destination . '" does not exist.');
        }

        $valid_files = array();

        if(is_array($files)) {
            foreach($files as $filename => $file) {
                if(file_exists($file)) {
                    $valid_files[$filename] = $file;
                }
            }
        }

        if(count($valid_files)) {

            $zip = new ZipArchive();

            if($zip->open($this->_path,ZIPARCHIVE::OVERWRITE) !== true) {
                return array(
                    'error' => 'failed to open',
                    'file' => $this->_path
                );
            }

            foreach($valid_files as $filename => $file) {
                $zip->addFile($file,$filename);
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            //close the zip -- done!
            $zip->close();
            chmod($this->_path,0777);

            //check to make sure the file exists
            return array(
                'error' => file_exists($this->_path)?'':'failed to create',
                'file' => $this->_path
            );
        } else {
            return array(
                'error' => 'no valid files',
                'file' => $this->_path
            );
        }
    }
}