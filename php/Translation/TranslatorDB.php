<?php

namespace Translation;

/**
 *
 * @author Matejcek Milan
 * trida rozsiruje dedenou tridu o nacitani slovniku z db
 *
 */

require_once __DIR__ .'ITranslatorDb.php';

class TranslatorDb extends Translation implements ITranslatorDB
{
	/**
	 * @var DibiConnection
	 */
	protected $db;

	protected $languages = array();

	public function __construct($lang='lang', $connection=0)
	{
	    $this->db  =dibi::getConnection($connection);
	    parent::__construct($this->getTemp(), $lang);
	}

	public function getId($webLang = false)
	{
	    return array_search($webLang? $this->webLang: $this->defLang, $this->getLanguages());
	}

   /**
     * vytahne vsechny dostupne jazyky z db
     * @return array
     */
    public function getLanguages()
    {
        if( empty($this->languages) )
            $this->languages = $this->db
                        ->select(self::L_ID_LANG .', '. self::L_LANG)
                        ->from(self::T_LANG)
                        //->test();
                        ->fetchPairs();
        return $this->languages;
    }

    /**
     * overeni zda existuje jazyk
     * @param $lang
     * @return DibiFluent
     */
	protected function query4lang($lang)
	{
		return $this->db->select(self::L_ID_LANG .', '. self::L_DECLENSION)
                        ->from(self::T_LANG)
                        ->where(self::L_LANG ."='$lang'");
	}

	/**
	 * obnovy, nacte nove slovnik z db i kdyz je uz vytvoreny
	 * @param $lang
	 * @return string  -nalezeny nazev slovniku
	 */
	protected function loadCache(&$lang)
	{
        $value      =$this->query4lang($lang)->fetch();

        if($value === false)
        {
        	$lang      = $this->defLang;
        }

        if(!file_exists($this->getCache($lang)) || !$this->isProduction())
        {

            $result    =$this->db->select('k.'. self::D_KEY .',
                       t.'. self::O_WORD .', g.'. self::G_GROUP)
                       ->from(self::T_TRANS, 't')
                       ->leftJoin(self::T_LANG, 'l')
                       ->on('l.', self::L_ID_LANG.'=t.'.self::O_ID_LANG)
                       ->leftJoin(self::T_TRANS_KEYS, 'k')
                       ->on('k.'.self::D_ID_DIC.'=t.'.self::O_ID_DIC)
                       ->leftJoin(self::T_MERGE_GROUP, 'm')
                       ->on('m.'.self::M_ID_DIC.'=t.'.self::O_ID_DIC)
                       ->leftJoin(self::T_GROUP, 'g')
                       ->on('g.'.self::G_ID_GROUP.'=m.'.self::M_ID_GROUP)
                       ->where('l.'.self::L_LANG."='$lang'")
                       //->test();
                       ->execute();

            $class  =array();
            foreach($result as $row)
            {
                if($row->{self::G_GROUP} == null)
                    $row->{self::G_GROUP} =self::DEF_GROUP;

                if(preg_match('~%s~', $row->{self::O_WORD}))
                {
                    $row->{self::O_WORD}  =explode('|', $row->{self::O_WORD});
                }

    		    $class[ $row->{self::G_GROUP} ][ $row->{self::D_KEY} ]    =$row->{self::O_WORD};
    		}

            $this->saveClass($lang, $this->makeClass($lang, $class,
            (empty($value[self::L_DECLENSION]))? $this->defaultDeclension: $value[self::L_DECLENSION]  ));
        }
	}


	/**
	 *
	 * @param string $className
	 * @param string $class
	 * @return void
	 */
	protected function saveClass($className, $class)
	{
	    $path      =$this->getCache($className);
        $safeSave  =new SafeSave($path);
        $safeSave->stream_write($class);
	}

    protected function cacheExists($lang)
    {
        $l      =$lang;
        $lang   =parent::cacheExists($l);

        if($lang === null)
        {
            return $this->reloadDictionary($l);
        }

        return $lang;
    }

    public function checkDictionary()
    {
        $langs  = $this->getLanguages();
        $result = $this->db->select('k.'.self::D_KEY.', t.'.self::O_WORD.', l.'.self::L_LANG)
                            ->from(self::T_TRANS_KEYS, 'k')
                            ->leftJoin(self::T_TRANS, 't')
                            ->on('k.'.self::D_ID_DIC.'=t.'.self::O_ID_DIC)
                            ->leftJoin(self::T_LANG, 'l')
                            ->on('l.'.self::L_ID_LANG.'=t.'.self::O_ID_LANG)
                            ->orderBy('k.'.self::D_ID_DIC.' ASC, t.'.self::O_ID_LANG.' ASC')
                            //->test();
                            ->execute();

        $key = null;
        $i   = 0;
        $count = count($langs);
        foreach($result as $val)
        {
            if($i === 0)
            {
                $key = $val->{self::D_KEY};
            }
            elseif($key != $val->{self::D_KEY} || $langs[$i] != $val->{self::L_LANG})
            {
                throw new RuntimeException('Translate for key "'.$val->{self::D_KEY}.'" is missing for language '.$langs[$i]);
            }

            $i++;

            if($i === $count)
                $i = 0;
        }

        if($i !== 0)
        {
            throw new RuntimeException('Translate for key "'.$val->{self::D_KEY}.'" is missing for language '.$langs[$i]);
        }
    }

    public function deleteCache()
    {
        $lang = $this->getLanguages();
        foreach($lang as $val)
        {
            @unlink($this->getCache($val));
        }
    }
}
