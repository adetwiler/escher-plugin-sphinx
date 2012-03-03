<?php Load::HelperClass('datasource'); Load::lib(array('sphinx','sphinxapi.php'));
class Plugin_sphinx_Helper_datasource extends Helper_datasource {
	protected $sphinx;
	protected $prefix='';
	
	function __construct($args) {
		parent::__construct($args);
		$this->sphinx = new SphinxClient();
		$this->sphinx->setServer($args['host'], (int)$args['port']);
		// Set the default prefix.
		if (!empty($args['prefix'])) { $this->prefix = $args['prefix']; }
	}
	
	function get($model,$data=array(),$options=array()) {
		// Clean up the options
		$options = array_merge(
			array(
				'select' => '*',
				'limit'  => 1,
				'order'  => '',
				'group'  => '',
				'joins'   => array(),
				'index'  => '',
				// Match Modes: SPH_MATCH_ALL, SPH_MATCH_ANY, SPH_MATCH_PHRASE, SPH_MATCH_BOOLEAN,
				// SPH_MATCH_EXTENDED, SPH_MATCH_FULLSCAN, SPH_MATCH_EXTENDED2
				'matchmode' => SPH_MATCH_EXTENDED2,
			),
			$options
		);
		
		if (is_object($model)) {
			$m = $model->_m();
			$data = get_object_vars($model);
		} elseif (is_string($model)) {
			$m = $model;
		} else { return false; }
		
		// If an index isn't set, set it to the model name.
		if (empty($options['index'])) {
			$options['index'] = $this->prefix.$m;
		} else if (!empty($options['index']) && $options['index']==='all') {
			$options['index'] = '';
		} else {
			// If an index is provided, add the prefix to it.
			$options['index'] = $this->prefix.$options['index'];
		}
		
		// Limit: int $offset , int $limit [, int $max_matches = 0 [, int $cutoff = 0 ]]
		if (empty($options['limit'])) {
			$options['limit']=array(0,1,0,0);
		} elseif (!is_array($options['limit'])) {
			$options['limit']=array(0,$options['limit'],0,0);
		} elseif (is_array($options['limit']) && sizeof($options['limit']) != 4) {
			$k = sizeof($options['limit']);
			for ($x = $k+1; $x <= 4;$x++) {
				if ($x == 2) { $v=1; } else { $v=0; }
				$options['limit'][] = $v;
			}
		}
		
		// Optional search terms
		if (!empty($options['idrange'])) {
			// int $min , int $max 
			$this->sphinx->setIDRange($options['idrange'][0],$options['idrange'][1]);
		}
		if (!empty($options['sortmode']) && !empty($options['sortby'])) {
			// Sort Modes: SPH_SORT_RELEVANCE, SPH_SORT_ATTR_DESC, SPH_SORT_ATTR_ASC, SPH_SORT_TIME_SEGMENTS,
			// SPH_SORT_EXTENDED, SPH_SORT_EXPR
			$this->sphinx->setSortMode($options['sortmode'],$options['sortby']);
		}
		if (!empty($options['ranking']) && $options['matchmode'] === SPH_MATCH_EXTENDED2) {
			// Modes: SPH_RANK_PROXIMITY_BM25, SPH_RANK_BM25, SPH_RANK_NONE
			$this->sphinx->setRankingMode($options['ranking']);
		}
		if (!empty($options['weights'])) {
			// An associative array mapping string index names to integer weights. Default is empty array, i.e. weighting summing is disabled.
			$this->sphinx->setFieldWeights($options['weights']);	
		}
		if (!empty($options['group'])) {
			// Group: string $attribute , int $func [, string $groupsort = "@group desc" ] 
			$this->sphinx->setGroupBy($options['group'][0],$options['group'][1],$options['group'][2]);
		}
		
		// Set the query.
		$query = '';
		if (!empty($data)) {
			foreach ($data as $key => $value) {
				if (preg_match('/^[^(].*,.*[^)]$/',$key)) { $key = "($key)"; }
				$query .= '@'.$key.' '.$value;
			}
		}

		// Set the Match Mode, SPH_MATCH_EXTENDED2 by default.
		$this->sphinx->setMatchMode($options['matchmode']);
		// Set the Limits, Limit 1 by default.
		$this->sphinx->setLimits((int)$options['limit'][0],(int)$options['limit'][1],(int)$options['limit'][2],(int)$options['limit'][3]);
		// Run the query.
		$result = $this->sphinx->query($query, $options['index']);
		// If we find an error or no matches, return false.
		$err = $this->sphinx->getLastError();
		if (!empty($err) || empty($result['matches'])) { return false; }
	
		foreach ($result['matches'] as $key => $match) {
			$results[] = Load::Model($m,$key);
		}
		
		return $results;
	}
	
	function set($model,$attrs=array(),$values=NULL) { return false; }
	function delete($model,$key) { return false; }
	function getSchema($model) { return false; }
}