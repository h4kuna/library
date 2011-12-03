<?php

/**
 * zajistuje praci se statickymi strankami
 * seznam stranek se preda metode getRouter();
 * vytvoreni url pomoci metody, ktera se registruje jako filter
 * <a href="{!$a='o-firme'|url}">o Firmě</a>
 * @author Milan Matějček
 *
 */
class StaticRoute extends NonObject implements IPage
{
    static protected $route = false;

    /**
     * @param string $page  -example: 'aboutUs|history|refereces'
     * @return Route
     */
    public static function getRoute()
    {
        if(self::$route !== false)
            throw new Exception('This method can call onetime.');
        $page = self::getPage();
        self::$route    =array_flip($page);
        if(empty($page))
            $page = array('xxx');
        return new Route('<id '. implode('|', $page) .'>[/<action>].html', array(
            Route::PRESENTER_KEY => 'Front:Static',
            'action' => 'show',
        ));
    }

    public static function register(Template $obj)
    {
        $obj->registerHelper('url', __CLASS__.'::url');
    }

    private static function getPage()
    {
        return dibi::query('SELECT %n FROM %n WHERE 1',
                           self::ROUTE, self::T_S_PAGE)->fetchPairs();
    }

    /**
     * tvorba url statickych stranek
     * @param string $str
     * @return string
     */
    public static function url($str, $action=NULL)
    {
        ///@TODO jak resit ted chci odeslat https
        ///@TODO absolutni cesty?

        if(!isset(self::$route[$str]))
        {
            if(!Environment::isProduction())
                throw new RuntimeException('Url "'. $str .'" is undeclared.');

            ///@TODO dodelat vypisovani chybnych linku na produkcnim serveru
            throw new RuntimeException('Url "'. $str .'" is undeclared.');
        }

        if($action !== NULL)
        {
            if($action == 'edit' || $action == 'delete')
                $str    .='/'. $action;
            else
                throw new RuntimeException('Bad action "'. $action .'".');
        }

        return Environment::getVariable('baseUri') . $str . '.html';
    }

    public static function getContent($route, $dbCol=null)
    {
        if($dbCol == null)
        {
            $dbCol = self::C_CONTENT;
        }
        return dibi::query('SELECT ['. $dbCol .']
        FROM ['. self::T_S_PAGE .']
        WHERE route=\''. $route .'\'
        LIMIT 1')
        ->fetchSingle();
    }

    ////dibi::query("UPDATE staticPage SET convertedContent='".$texy->process($a)."' WHERE route='". $val['id'] ."'");
}
