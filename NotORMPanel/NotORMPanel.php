<?php
use Nette\IDebugPanel;

class NotORMPanel implements IDebugPanel {
	
	protected $queries;
	
	function addQuery($query, $parameters) {
		$this->queries[] = $query;
	}
	
	// IDebugPanel implementation
	
	function getTab() {
		$return = '';
		$return .= '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC" width="16" height="16" alt="" />'; // copied from Dibi
		$return .= count($this->queries) . ' queries';
		return $return;
	}
	
	function getPanel() {
		$return = "";
		if ($this->queries) {
			$return .= "<table>\n";
			foreach ($this->queries as $query) {
				$return .= "<tr><td><code class='language-sql'>" . htmlspecialchars($query) . "</code></td></tr>\n";
			}
			$return .= "</table>\n";
			$return .= "<script type='text/javascript'>
var script = document.createElement('script');
script.src = 'http://jush.sourceforge.net/jush.js';
script.onload = function () {
	jush.style('http://jush.sourceforge.net/jush.css');
	jush.highlight_tag('code');
}
document.body.appendChild(script);
</script>";
		}
		return $return;
	}
	
	function getId() {
		return "NotORM";
	}
	
}
