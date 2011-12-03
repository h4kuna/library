<?php

/**
 * Description of SqlPaginator
 *
 * @author Milan Matějček
 */
class SqlPaginator extends Paginator
{
    protected $itemsOnPage = 50;

    /**
     *
     * @var Session
     */
    protected static $session = null;

    protected $nameSession = null;

    public function  __construct($nameSession)
    {
        $this->setPage(Environment::getHttpRequest()->getQuery(VisualPaginator::$param, 1));
        $this->setItemsPerPage($this->itemsOnPage);
        self::$session = Environment::getSession(__CLASS__);
        $this->nameSession = $nameSession;
        $this->setItemCount(-1);
    }

    public function getLimit()
    {
        return 'LIMIT '. ($this->getPage()-1) * $this->itemsOnPage .', '. $this->itemsOnPage;
    }
    public function init(){return true;}

	public function setPage($page)
	{
		parent::setPage(max($this->getFirstPage(), (int) $page));
		return $this;
	}

    public function setItemCount($itemCount)
    {
        $sesCount = (int)self::$session->{$this->nameSession};
        if($itemCount === -1)
        {
            if($sesCount < 1)
                parent::setItemCount($this->itemsOnPage);
            else
                parent::setItemCount($sesCount);
        }
        elseif($sesCount != $itemCount)
        {
            self::$session->{$this->nameSession} = $itemCount;
        }

        return $this;
    }
}

class NoPaginator extends Paginator
{
    public function  __construct($page=0) {}
    public function getLimit() {return '';}
    public function init(){return false;}
    public function render(){return '';}
}
