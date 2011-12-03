<?php
/**
 * na funkce v php id3 se neda moc spolehat
 * @author matejcekm
 *
 */
class Mp3 extends Object
{
    static public $countFile  =0;
    private $folder;
    private $fileName;
    private $tagVersion;
    static private $genreList  =false;
    private $tags  =false;

    public function __construct($file)
    {
        $this->fileName =basename($file);
        $this->folder   =dirname($file);
        $this->tagVersion   =self::getId3Version($file);

        if($this->tagVersion === false)
        {
            die('Nerozpoznany ID3 TAG '. $file);
        }

        $type   =FileTools::fileType($file, false, null);

        if($type != 'mp3')
        {
            die('nepodporovany format');
        }

        $this->getGenreList();

        $this->getTag2();
    }

    public function __toString()
    {
        Debug::dump($this->folder);
        Debug::dump($this->fileName);
        Debug::dump($this->tagVersion);
        Debug::dump($this->tags);
    }

    public function getTag()
    {//vraci divny znaky
        if($this->tags === false)
        {
            $this->tags  =id3_get_tag($this->folder .'/'. $this->fileName, $this->tagVersion);
            /*
            foreach($this->tags as $key => $value)
            {
                //$value  =FileTools::autoUTF($value);
                $this->tags[$key]  =preg_replace('~[^a-ž0-9 :.,/]~i', '', $value);
            }
            */
        }
        return $this->tags;
    }

    public function getTag2()
    {
        if($this->tags === false)
        {
            $this->tags  =self::getId3Tags($this->folder .'/'. $this->fileName);
        }
        return $this->tags;
    }

    /**
     * naplni statickou promenou zanrama, avsak neni spolehliva
     * @return unknown_type
     */
    public static function getGenreList()
    {
        if(self::$genreList === false)
        {
            self::$genreList  =id3_get_genre_list();
            //neni spolehlivy, pac je vice zanru napr Dance Pop
        }

        return self::$genreList;
    }

    /**
     * nacte informace o mp3 souboru
     * @param $filename
     * @return unknown_type
     */
    public static function getId3Tags(SplFileInfo $filename)
    {
        $file   =$filename->openFile('r');
        //$file=new SplFileObject();
        //nastaveni ukazatele na pozici 128 bytu pred koncem souboru

        $tagData  =$file->fgets();

        if ($file->fseek(-128, SEEK_END))
        {
            throw new InvalidStateException('Nepovedlo se posunout ukazatel v souboru, nejspise vadny soubor.');
        }

        //nacteni poslednich 128 bytu souboru (ID3v1 tag)
        $tagData  =$file->fgets();

        //kontrola jestli prvni tri pismena techto dat odpovidaji ID3 tagu
        if(substr($tagData, 0, 3) != "TAG")
        {
            throw new IOException('This is not ID3 TAG version 1 file: '. $filename);
        }

        //prevod zanru na slovo
        $genre  =hexdec(bin2hex(substr($tagData, 127, 1)));
        self::getGenreList();

        if(isset(self::$genreList[$genre]))
        {
             $genre =self::$genreList[$genre];
        }


        //cteni id3tagu
        $id3tag   =array(
          'title'   =>self::readTag($tagData, 3, 30),
          'artist'  =>self::readTag($tagData, 33, 30),
          'album'   =>self::readTag($tagData, 63, 30),
          'year'    =>(int)self::readTag($tagData, 93, 4),
          'comment' =>self::readTag($tagData, 97, 29),
          'genre'   =>$genre
        );

        return $id3tag;
    }

    /**
     * knihovna id3 neni moc aktualni proto to je v rozahu
     */
    static public function getId3Version($fileName)
    {
        $version  =(int)id3_get_version($fileName);

        if($version == 0)
        {
            $return  =false;
        }
        else if($version <= 2)
        {
            $return  =ID3_V1_0;
        }
        else if($version == 3)
        {
            $return  =ID3_V1_1;
        }
        else if($version <= 11)
        {
            $return  =ID3_V2_1;
        }
        else if($version <= 27)
        {
            $return  =ID3_V2_2;
        }
        else if($version < 60)
        {
            $return  =ID3_V2_3;
        }
        else if($version >= 60)
        {
            $return  =ID3_V2_4;
        }

        return $return;
    }

    static private function readTag($text, $from, $to)
    {
        return trim(substr($text, $from, $to));
    }

}
