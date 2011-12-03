<?php

class c_zip extends FileTools
{
    /**
     *
     * @var ZipArchive
     */
    public $archive;
    public function __construct($fileName)
    {
        try
        {
            $this->archive    =new ZipArchive();
            $this->archive    ->open($fileName);
        }
        catch (Exception $mes)
        {
            $mes->getMessage('spatna cesta k souboru nebo nemas prava');
        }
    }

    public function __destruct()
    {
        $this->archive    ->close();
    }

    public function extractTo($dir, $files=null)
    {
        $this->archive    ->extractTo($dir, $files);
    }

    public function filesOfArchive($regString=false, $inDir=false, $maxSize=0)
    {
        $files    =array();
        for ($i=0; $i<$this->archive->numFiles; $i++)
        {
            $array =$this->archive->statIndex($i);

            if($maxSize == 0)
                 $maxSize    =$array['size'];

            if( $inDir === true && preg_match('~/~', $array['name']) ||
                 $maxSize <  $array['size'])
                 continue;

            if( $regString === false || preg_match('~'.$regString.'~', $array['name']) )
            {
                $files[]    =$array['name'];
            }
        }

        return $files;
    }

}
