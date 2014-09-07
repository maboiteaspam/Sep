<?php
/**
 * Created by PhpStorm.
 * User: clement
 * Date: 12/07/14
 * Time: 13:51
 */

namespace Sep\View;


class SlimView extends \Slim\View {

    /**
     * Path's to templates base directory (without trailing slash)
     * @var array
     */
    protected $templatesDirectories;

    /********************************************************************************
     * Resolve template paths
     *******************************************************************************/

    /**
     * Set the base directory that contains view templates
     * @param   string $directory
     * @throws  \InvalidArgumentException If directory is not a directory
     */
    public function setTemplatesDirectory($directory)
    {
        $directory = is_string($directory)?[$directory]:$directory;
        foreach($directory as $i=>$t ){
            $directory[$i] = rtrim($t, DIRECTORY_SEPARATOR);
        }
        $this->templatesDirectories = $directory;
        if(isset($directory[0]) ) $this->templatesDirectory = $directory[0];
    }

    /**
     * Get fully qualified path to template file using templates base directory
     * @param  string $file The template file pathname relative to templates base directory
     * @return string
     */
    public function getTemplatePathname($file)
    {
        foreach($this->templatesDirectories as $t ){
            $f = $t . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
            if( file_exists($f) ) return $f;
        }
        return false;
    }
} 