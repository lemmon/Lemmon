<?php
/**
* 
*/
abstract class Lemmon_Model extends Lemmon_MySQL_Query_Builder
{
	private static $___cache;
	
	protected $_connectionName;
	protected $_table;
	protected $_name = 'name';
	protected $_primary = 'id';
	protected $_collectionRemoveEmpty = true;
	
	private $_id;
	private $_row = array();
	private $_rowState;
	private $_restrict = array();
	private $_required = array();
	private $_unique = array();
	private $_fieldTypes = array();
	private $_files = array();
	private $_many = array();
	private $_mustReload;
	private $_timeStampable;
	private $_belongsTo;
	private $_hasOne;
	private $_hasMany;
	private $_hasAndBelongsToMany;
	private $_childrenFields = array();
	private $_children;
	private $_childrenAppended = array();
	
	protected function onValidate() {}
	protected function onBeforeCreate() {}
	protected function onBeforeUpdate() {}
	protected function onFileUpload() {}
	protected function onAfterCreate() {}
	protected function onAfterUpdate() {}
	protected function onBeforeDelete() {}
	protected function onAfterDelete() {}
	
	final public function __construct($_item=null)
	{
		parent::__construct($this->_connectionName);

		if (!$this->_table)
		{
			$this->_table = Lemmon\String::classToTableName(get_class($this));
		}
		
		$this->define();
		
		if ($_item)
		{
			if (is_numeric($_item))
			{
				// defined PRIMARY
				$this->_setPrimary($_item);
				$this->_mustReload = true;
			}
			elseif (is_array($_item) or is_object($_item))
			{
				// create from Object/Array
				self::prepare((object)$_item);
			}
			elseif (is_string($_item))
			{
				// find by __NAME__
				throw new Lemmon_Exception('Support for find by __NAME__ depreciated');
				$this->findByName($_item);
			}
		}
	}
	
	final public static function factory($model, $id=null)
	{
		return $model::make($id);
	}
	
	final public static function make($id=null)
	{
		$class_name = get_called_class();
		/*
		dump($class_name);
		dump($id);
		if ($id and !is_numeric($id)) throw new Lemmon_Exception('FUCK!');
		echo '<hr>';
		*/
		return new $class_name($id);
	}
	
	public static function returnCache()
	{
		return self::$___cache;
	}
	
	final public function __isset($key)
	{
		$this->reload();
		return array_key_exists($key, $this->_row)
			or array_key_exists($key, $this->_childrenFields)
			or array_key_exists(Lemmon\String::pl($key), $this->_childrenFields);
	}
	
	final public function __get($key)
	{
		$this->reload();
		if (method_exists($this, 'get' . $key))
		{
			return $this->{'get' . $key}();
		}
		else
		{
			if (array_key_exists($key, $this->_row))
			{
				return $this->_row[$key];
			}
			elseif (is_array($this->_children) and array_key_exists($key, $this->_children))
			{
				return $this->_children[$key];
			}
			elseif (array_key_exists($key, $this->_childrenFields))
			{
				if ($_model=$this->{$this->_childrenFields[$key]}[$key]['model'])
				{
					return Lemmon_Model::factory($_model)->find(null);
				}
				/*
				$collection = $this->getCollectionFor($key);
				return $collection[null];
				*/
			}
			elseif (array_key_exists($key_pl=Lemmon\String::pl($key), $this->_childrenFields)
				and ($this->_childrenFields[$key_pl]=='_hasMany' or $this->_childrenFields[$key_pl]=='_hasAndBelongsToMany'))
			{
				$res = $this->_children[$key_pl]->first();
 				return $res ? $res : Lemmon_Model::factory(get_class($this->_children[$key_pl]));
			}
		}
	}
	
	final public function __set($key, $val)
	{
		$this->reload();

		if ($this->_childrenFields[$key])
		{
			$this->_children[$key] = $this->_recognizeField($key, $val);
		}
		else
		{
			$this->_row[$key] = $val;
		}
	}
	
	final public function getField($key)
	{
		return $this->_row[$key];
	}

	final public function getRowState()
	{
		return $this->_rowState;
	}

	final public function appendChild($child_name, $val)
	{
		$this->reload();

		switch ($this->_childrenFields[$child_name])
		{
			case '_belongsTo':
				break;

			case '_hasOne':
				break;

			case '_hasMany':
				$params = $this->_hasMany[$child_name];
				$appended = Lemmon_Model::factory($params['model'], $val);
				$appended->{$params['field']} = $this->getPrimaryVal();
				$this->_childrenAppended[$child_name][] = $appended;
				break;

			case '_hasAndBelongsToMany':
				$params = $this->_hasAndBelongsToMany[$child_name];
				dump($val->reload());
				die;
				if (is_object($val) and $val instanceof Lemmon_Model)
				{
					$this->_childrenAppended[$child_name][] = $val;
				}
				dump($this);
				die('here!1');
				break;

			default:
				throw new Lemmon_Exception('Unknown child name ' . $child_name);
				break;
		}

		return $this;
	}
	
	final public function __toString()
	{
		return (string)$this->{$this->_name};
	}
	
	final public function toArray($depth=null, $include=array(), $level=1, $skip=array())
	{
		if ($depth===null or $depth)
		{
			if ($id=$this->getPrimaryVal())
			{
				$cue = get_class($this) . ':' . (is_array($id) ? join('+', $id) : $id);
				if (empty($skip[$cue]))
				{
					$skip[$cue] = true;
					$this->reload();
					$res = $this->_row;
					if ($_include=$include[get_class($this)])
					{
						foreach ($_include as $_field) $res[$_field] = $this->{$_field};
					}
					if (is_array($this->_children)) foreach ($this->_children as $child_name => $child)
					{
						$res[$child_name] = $child->toArray($depth?$depth-1:$depth, $include, $level+1, $skip);
						if (is_array($this->_childrenAppended) and array_key_exists($child_name, $this->_childrenAppended) and $appended=$this->_childrenAppended[$child_name])
						{
							foreach ($appended as $_appended)
							{
								$res[$child_name][] = $_appended->toArray($depth?$depth-1:$depth, $include);
							}
						}
					}
				}
				else
				{
					$res = '** RECURSION **';
				}
			}
			else
			{
				foreach ($this->all() as $row)
				{
					$res[] = $row->toArray($depth?$depth-1:$depth, $include, $level+1, $skip);
				}
			}
		}
		return isset($res) ? $res : null;
	}
	
	protected function define() { }
	
	final public function getPrimaryVal()
	{
		$this->reload();
		return $this->_id;
	}
	
	final public function getPrimaryValStr()
	{
		$this->reload();
		return is_array($this->_id) ? join(':', $this->_id) : $this->_id;
	}
	
	private function _assignPrimary()
	{
		if (($this->_row
				and
					(
						(is_array($this->_primary)
						and $id=array_intersect_key($this->_row, array_flip($this->_primary)))
					or
						(!is_array($this->_primary)
						and array_key_exists($this->_primary, $this->_row)
						and $id=$this->_row[$this->_primary])))
			or $id=$this->getInsertId()
			or $id=$this->getPrimaryVal())
		{
			$this->_setPrimary($id);
			return $id;
		}
		else
		{
			return false;
		}
	}
	
	private function _setPrimary($primary)
	{
		if (is_array($primary))
		{
			foreach ($primary as $key => $val)
			{
				$this->_row[$key] = $val;
				$this->_restrict[$key] = $val;
			}
		}
		else
		{
			$this->_row[$this->_primary] = $primary;
			$this->_restrict[$this->_primary] = $primary;
		}
		$this->_id = $primary;
		$this->findByPrimary($primary);
	}
	
	private function _assignRow($row)
	{
		if ($row)
		{
			$this->_validateRestricted($row);
			$this->_row = $row;
		}
		else
		{
			return $this->_assignRestricted();
		}
	}
	
	private function _assignRestricted()
	{
		$this->_row = $this->_restrict;
	}
	
	private function _validateRestricted($row)
	{
		if ($restrict=$this->_restrict)
		{
			foreach ($restrict as $field => $val)
			{
				if ($row[$field]!=$val)
				{
					throw new Lemmon_Exception("Ivalid value on restricted field `{$field}`");
				}
			}
		}
	}
	
	final public function getRow()
	{
		$this->reload();
		return array_merge($this->_row, $this->_children);
	}
	
	final public function findByName($val)
	{
		$this->findLike($this->_name, $val);
		if ($row=$this->first(false))
		{
			$this->_isolateChildren($row, $children);
			$this->_assignRow($row);
			$this->_children = $children;
		}
		else
		{
			$this->_row[$this->_name] = $val;
		}
		$this->_assignPrimary();
		return $this;
	}
	
	final public function restrict($key, $val)
	{
		$this->find($key, $val);
		$this->_restrict[$key] = $val;
		$this->_row[$key] = $val;
		return $this;
	}
	
	final public function required($field)
	{
		$required = func_get_args();
		$this->_required = array_merge($this->_required, array_combine($required, $required));
		return $this;
	}
	
	final public function unique($field)
	{
		$unique = func_get_args();
		$this->_unique = array_merge($this->_unique, array_combine($unique, $unique));
		return $this;
	}
	
	private function _parsePath($path)
	{
		return $path;
	}
	
	final public function fieldType($field, $type)
	{
		$this->_fieldTypes[$field] = $type;
		return $this;
	}
	
	final public function fieldFile($field, $path, $args=array())
	{
		$params = array(
			'path' => $this->_parsePath($path),
		);
		$this->_files[$field] = array_merge($params, $args);
		return $this;
	}
	
	final public function belongsTo($child_sg, $args=array())
	{
		if ($child_sg{0}=='@')
		{
			$child_sg = substr($child_sg, 1);
			$child_pl = Lemmon\String::pl($child_sg);
			$model = Lemmon\String::tableToClassName($this->getTable() . '_' . $child_pl);
		}
		else
		{
			$child_pl = Lemmon\String::pl($child_sg);
			$model = Lemmon\String::tableToClassName($child_pl);
		}
		$params = array(
			'model' => $model,
			'field' => $child_sg . '_id',
		);
		$this->_belongsTo[$child_sg] = array_merge($params, $args);
		$this->_childrenFields[$child_sg] = '_belongsTo';
		return $this;
	}
	
	final public function hasOne($child_sg, $args=array())
	{
		if ($child_sg{0}=='@')
		{
			$child_sg = substr($child_sg, 1);
			$child_pl = $this->getTable() . '_' . Lemmon\String::pl($child_sg);
		}
		else
		{
			$child_pl = Lemmon\String::pl($child_sg);
		}
		$params = array(
			'model' => Lemmon\String::tableToClassName($child_pl),
			'field' => $child_sg . '_id',
		);
		$this->_hasOne[$child_sg] = array_merge($params, $args);
		$this->_childrenFields[$child_sg] = '_hasOne';
		return $this;
	}
	
	final public function hasMany($child_pl, $args=array())
	{
		$params = array(
			'model' => Lemmon\String::tableToClassName($child_pl, $this->getTable()),
			#'field' => Lemmon\String::sg($this->getTable()) . '_id',
			'field' => Lemmon\String::sg(Lemmon\String::classToTableName(get_class($this))) . '_id',
		);
		if ($child_pl{0}=='@') $child_pl = substr($child_pl, 1);
		$this->_hasMany[$child_pl] = array_merge($params, $args);
		$this->_childrenFields[$child_pl] = '_hasMany';
		return $this;
	}
	
	final public function hasAndBelongsToMany($child_pl, $args=array())
	{
		if ($child_pl{0}=='>')
		{
			$child_pl = substr($child_pl, 1);
			$intersect = $child_pl . '_to_' . $this->getTable();
		}
		else
		{
			$intersect = $this->getTable() . '_to_' . $child_pl;
		}
		$child_sg = Lemmon\String::sg($child_pl);
		$params = array(
			'model' => Lemmon\String::tableToClassName($child_pl),
			'intersect' => $intersect,
			'field_my' => Lemmon\String::sg($this->getTable()) . '_id',
			'field_foreign' => $child_sg . '_id',
		);
		$this->_hasAndBelongsToMany[$child_pl] = array_merge($params, $args);
		$this->_childrenFields[$child_pl] = '_hasAndBelongsToMany';
		return $this;
	}
	
	final public static function collection()
	{
		return self::make()->skipEmpty()->pairs();
	}
	
	final public function skipEmpty($field=null)
	{
		if ($this->_collectionRemoveEmpty)
		{
			$this->excludeNull($field ? $field : $this->_name);
		}
		return $this;
	}
	
	final public function getCollectionFor($section)
	{
		if ($param=$this->{$this->_childrenFields[$section]}[$section])
		{
			$class = $param['model'];
			return $class::collection();
		}
	}
	
	final public function getOptionsFor($section)
	{
		if ($param=$this->{$this->_childrenFields[$section]}[$section])
		{
			return Lemmon_Model::factory($param['model'])->all();
		}
	}
	
	final public function getSelectionFor($section, $selection=null)
	{
		return self::getCollectionFor($section);
	}
	
	final public function fetch($object=false)
	{
		$row = parent::fetch();
		$row = $row ? (array)$row : array();

		return self::fetchAfter($row, $object);
	}
	
	final public function fetchAfter($row, $object=false)
	{
		if ($this->_primary=='id' and $row['id'] and !$this->_select)
		{
			self::$___cache[ $this->_table ][ $row['id'] ] = $row;
		}
		if ($object)
		{
			if ($row)
			{
				$model_name = get_class($this);
				$row = new $model_name($row);
			}
		}
		else
		{
			$this->_fetchChildren($row);
		}
		
		return $row;
	}
	
	final public function first($object=true)
	{
		if ($this->_primary=='id' and $this->_id and $row=self::$___cache[ $this->_table ][ $this->_id ])
		{
			$row = self::fetchAfter($row, $object);
			#dump('--> ' . $this->_table . ' : ' . $this->_id . '  __  ' . var_export((bool)self::$___cache[ $this->_table ][ $this->_id ], 1));
		}
		else
		{
			$this->prepareResult();
			$row = $this->fetch($object);
			$this->freeRes();
		}
		return $row;
	}
	
	private function _fetchChildren(&$row)
	{
		if ($children=$this->_belongsTo)
		{
			foreach ($children as $name => $params) if ($_id=$row[$params['field']])
			{
				$row[$name] = Lemmon_Model::factory($params['model'], $_id);
			}
		}
		if ($children=$this->_hasOne)
		{
			foreach ($children as $name => $params) if ($_id=$row[$params['field']])
			{
				$row[$name] = Lemmon_Model::factory($params['model'], $_id);
			}
		}
		if ($children=$this->_hasMany)
		{
			foreach ($children as $name => $params) if ($_id=$row[$this->getPrimary()])
			{
				$row[$name] = Lemmon_Model::factory($params['model'])->find($params['field'], $_id);
			}
		}
		if ($children=$this->_hasAndBelongsToMany)
		{
			foreach ($children as $name => $params) if ($_id=$row[$this->getPrimary()])
			{
				$row[$name] = Lemmon_Model::factory($params['model'])->join($params['intersect'], array( $params['field_foreign'] => '$' . $this->getPrimary(), $params['field_my'] => $_id ));
			}
		}
	}
	
	final public function reload($force=false)
	{
		if ($force)
		{
			$this->mustReload();
		}
		if ($this->_mustReload)
		{
			if ($row=$this->first(false))
			{
				$this->_isolateChildren($row, $children);
				$this->_assignRow($row);
				$this->_children = $children;
				$this->_rowState = 'loaded';
			}
			else
			{
				$this->_assignRestricted();
				$this->_rowState = 'n/a';
			}
			$this->_mustReload = false;
		}
		return $this;
	}
	
	final public function load()
	{
		return $this->reload();
	}
	
	final public function has($child, $id=null)
	{
		if ($field=$this->_childrenFields[$child] or ($child=Lemmon\String::pl($child) and $field=$this->_childrenFields[$child]))
		{
			$params = $this->{$field}[$child];
			if (func_num_args()==1)
			{
				switch ($field)
				{
					case '_belongsTo':
						$this->join(Lemmon\String::classToTableName($params['model']), array(
							array('%t.%n=$_table.%n', Lemmon\String::classToTableName($params['model']), 'id', $params['field'])
						));
						break;
					case '_hasMany':
						$this->join(Lemmon\String::classToTableName($params['model']), array(
							array('%t.%n=$_table.%n', Lemmon\String::classToTableName($params['model']), $params['field'], 'id')
						));
						break;
					case '_hasAndBelongsToMany':
						$this->join($params['intersect'], $a=array(
							$params['field_my'] => '$' . $this->getPrimary(),
						));
						$this->groupByPrimary();
						#dump($this);die;
						break;
					default:
						throw new Lemmon_Exception('Unknown condition');
						break;
				}
			}
			elseif (func_num_args()==2)
			{
				switch ($field)
				{
					case '_hasAndBelongsToMany':
						if (is_array($id))
						{
							$this->join($params['intersect'], $a=array(
								$params['field_my'] => '$' . $this->getPrimary(),
								array('%t.%n IN (%_a)', $params['intersect'], $params['field_foreign'], $id),
							));
						}
						else
						{
							$this->join($params['intersect'], $a=array(
								$params['field_my'] => '$' . $this->getPrimary(),
								$params['field_foreign'] => $id,
							));
						}
						break;
					default:
						throw new Lemmon_Exception('Unknown condition');
						break;
				}
			}
			elseif (func_num_args()==3)
			{
				$args = func_get_args();
				switch ($field)
				{
					case '_hasMany':
						$this->join(Lemmon\String::classToTableName($params['model']), array(
							array('%t.%n=$_table.%n', Lemmon\String::classToTableName($params['model']), $params['field'], 'id'),
							$args[1] => $args[2],
						));
						break;
					case '_hasAndBelongsToMany':
						$this->join($params['intersect'], array(
							$params['field_my'] => '$id',
						));
						$this->join(Lemmon\String::classToTableName($params['model']), array(
							array('%t.`id`=%t.%n', Lemmon\String::classToTableName($params['model']), $params['intersect'], $params['field_foreign']),
							$args[1] => $args[2],
						));
						break;
					default:
						throw new Lemmon_Exception('Unknown condition');
						break;
				}
			}
			else
			{
				throw new Lemmon_Exception('Haz more arguments?!');
			}
		}
		return $this;
	}
	
	private function _pushRestricted(&$row=array())
	{
		foreach ($this->_restrict as $key => $val)
		{
			$this->_row[$key] = $row[$key] = $val;
		}
	}
	
	final public function validate($row)
	{
		return $this->_validate($row);
	}
	
	final private function _validate(&$row=array())
	{
		$row = (array)$row;
		$ok = true;
		$controller = Lemmon\Framework::getInstance();
		// required
		foreach ($this->_required as $field)
		{
			if ((($row and !$row[$field]) or (!$row and (!$this->_row or !array_key_exists($field, $this->_row) or !$this->_row[$field])))
				 and (!$this->_children or !array_key_exists($field, $this->_children) or !$this->_children[$field])
				 and (!isset($this->_childrenFields[$field])
				 	or !$this->_row[$this->{$this->_childrenFields[$field]}[$field]['field']]))
			{
				if ($controller)
					Lemmon\Framework::getInstance()->flash->error('Missing field %s', Lemmon_I18n::t(Lemmon\String::human($field)))->flash->errorField($field);
				else
					throw new Lemmon_Exception('Missing field ' . $field);
				$ok = false;
			}
		}
		// unique
		foreach ($this->_unique as $field)
		{
			if ($row and isset($row[$field]) and self::make()->findLike($field, $row[$field])->exclude($this->getPrimaryVal())->count())
			{
				Lemmon\Framework::getInstance()->flash->error('Duplicate entry for field %s', Lemmon_I18n::t(Lemmon\String::human($field)))->flash->errorField($field);
				$ok = false;
			}
		}
		// user validateion
		if ($this->onValidate($row)===false)
		{
			$ok = false;
		}
		//
		return $ok;
	}
	
	final public function createOrUpdate($row=null)
	{
		if ($row)
		{
			$this->_isolateChildren($row, $children);
			#$this->_row = $row;
			$this->_children = $children;
			$this->_saveAllChildren();
			$this->_pushRestricted($row);
		}
		else
		{
			$row = $this->_row;
			$this->_saveAllChildren();
			$this->_pushRestricted($row);
		}
		// validate & save
		if ($this->_validate($row)!==false)
		{
			parent::createOrUpdate($row);
			$this->_assignPrimary();
			$this->_rowState = 'created/updated';
			$this->mustReload();
			return $this;
		}
		else
		{
			return false;
		}
	}
	
	final public function prepare($row=null)
	{
		if ($row)
		{
			$row = (array)$row;
			$this->_fetchChildren($row);
			$this->_isolateChildren($row, $children);
			$this->_pushRestricted($row);
			$this->_assignRow($row);
			$this->_children = $children;
		}
		$this->_assignPrimary();
		$this->_rowState = 'prepared';
		$this->_mustReload = false;
		return $this;
	}
	
	private function _sanitize(&$row)
	{
		foreach ($this->_fieldTypes as $field => $type)
		{
			switch ($type)
			{
				case 'number':
					$row[$field] = (float)str_replace(array(',', ' '), array('.', ''), $row[$field]);
					if (!$row[$field]) $row[$field] = null;
					break;
			}
		}
	}
	
	private function _uploadFiles(&$row)
	{
		$ok = true;
		$time = time();
		foreach ($_FILES as $field => $file) if ($args=$this->_files[$field])
		{
			if ($file['error']==UPLOAD_ERR_OK)
			{
				$upload_base = Lemmon_Route::getInstance()->getUploadDir();
				$upload_dir = strftime(rtrim($args['path'], '/')) . '/';
				// create directory
				if (!file_exists($upload_base.$upload_dir))
				{
					if (is_writable($upload_base))
					{
						if (!@mkdir($upload_base.$upload_dir, 0777, true))
						{
							throw new Lemmon_Exception('Error creating directory for uploaded file');
						}
					}
					else
					{
						throw new Lemmon_Exception('Upload directory not writable');
					}
				}
				// remove previous upload
				elseif ($_file_full=$this->{$field} and file_exists($upload_base))
				{
					@unlink($upload_base . $_file_full);
				}
				// upload file
				$file_name = Lemmon\String::asciize(substr($file['name'], 0, strrpos($file['name'], '.')), '_');
				$file_ext = substr($file['name'], strrpos($file['name'], '.'));
				$file_full = $upload_dir . $file_name . '.' . $time . $file_ext;
				if (move_uploaded_file($file['tmp_name'], $upload_base . $file_full))
				{
					$this->onFileUpload($file, $row);
					$row[$field] = $file_full;
				}
				else
				{
					throw new Lemmon_Exception('Error moving uploaded file');
				}
			}
			elseif ($file['error']==UPLOAD_ERR_NO_FILE)
			{
				$row[$field] = $this->{$field};
			}
			else
			{
				Lemmon\Framework::getInstance()->flash->error('Error uploading %s (errno. %i)', Lemmon\String::human($field), $file['error'])->flash->errorField($field);
				$ok = false;
			}
		}
		return $ok;
	}
	
	private function _isolateChildren(&$row, &$children=array())
	{
		if ($row)
		{
			foreach ($row as $key => $val)
			{
				if (is_array($this->_childrenFields) and array_key_exists($key, $this->_childrenFields))
				{
					$children[$key] = $this->_recognizeField($key, $val);
					unset($row[$key]);
				}
			}
		}
	}
	
	private function _recognizeField($key, $val)
	{
		switch ($this->_childrenFields[$key])
		{
			case '_belongsTo':
			case '_hasOne':
				if ($param=$this->_belongsTo[$key] or $param=$this->_hasOne[$key])
				{
					if (is_object($val) and ($val instanceof self))
					{
						return $val;
					}
					elseif (is_numeric($val) or is_array($val) or is_object($val))
					{
						return Lemmon_Model::factory($param['model'], $val);
					}
					elseif (is_string($val))
					{
						return Lemmon_Model::factory($param['model'])->findByName($val);
					}
					/*
					elseif ($val===null)
					{
						return Lemmon_Model::factory($param['model']);
					}
					*/
					else
					{
						throw new Lemmon_Exception('Dunno what to do here');
					}
				}
				break;
				
			case '_hasMany':
				if (is_object($val) and ($val instanceof self))
				{
					return $val;
				}
				elseif (is_array($val))
				{
					return $val;
				}
				else
				{
					throw new Lemmon_Exception('Dunno what to do here');
				}
				break;
			case '_hasAndBelongsToMany':
				if (is_object($val) and ($val instanceof self))
				{
					return $val;
				}
				elseif (is_array($val))
				{
					return $val;
				}
				else
				{
					throw new Lemmon_Exception('Dunno what to do here');
				}
				break;
		}
		return false;
	}
	
	private function _saveAllParents(&$row)
	{
		if (is_array($this->_children))
		{
			foreach ($this->_children as $field => $child)
			{
				if ($param=$this->_belongsTo[$field] or $param=$this->_hasOne[$field])
				{
					$child->save();
					$row[ $param['field'] ] = $child->getPrimaryVal();
				}
			}
		}
	}
	
	private function _saveAllChildren()
	{
		if (is_array($this->_children))
		{
			foreach ($this->_children as $field => $children)
			{
				$childrenren_to_db = array();
				
				if ($param=$this->_hasMany[$field])
				{
					if (is_array($children))
					{
						$children_ids = array();
						foreach ($children as $child) if ($child['id'])
						{
							$children_ids[] = $child['id'];
						}
						$child_model = self::factory($param['model']);
						if ($children_ids)
						{
							$this->query('DELETE FROM %t WHERE %n=%i AND `id` NOT IN (%_a)', $child_model->getTable(), $param['field'], $this->getPrimaryVal(), $children_ids);
						}
						foreach ($children as $child)
						{
							$child_model = self::factory($param['model'], $child);
							$child_model->{$param['field']} = $this->getPrimaryVal();
							$child_model->save();
						}
					}
					elseif ($children instanceof Lemmon_Model and $children->getPrimaryVal())
					{
						throw new Lemmon_Exception('Dunno what to do here');
					}
				}
				elseif ($param=$this->_hasAndBelongsToMany[$field])
				{
					if ((is_array($children) and $children_ids=$children) or (is_object($children) and $children instanceof Lemmon_Model and $children_ids=$children->distinct('id')))
					{
						foreach ($children_ids as $_i => $_id)
						{
							if (!is_numeric($_id) and $_id instanceof Lemmon_Model) $_id = $children_ids[$_i] = $_id->getPrimaryVal();
							elseif (!is_numeric($_id)) throw new Lemmon_Exception('Wait, what?!');
							$childrenren_to_db[] = array('(%i, %i)', $this->getPrimaryVal(), $_id);
						}
						$this->query('DELETE FROM %t WHERE %n=%i AND %n NOT IN (%_a)', $param['intersect'], $param['field_my'], $this->getPrimaryVal(), $param['field_foreign'], $children_ids);
						$this->query('REPLACE INTO %t (%n, %n) VALUES %_a', $param['intersect'], $param['field_my'], $param['field_foreign'], $childrenren_to_db);
					}
					else
					{
						$this->query('DELETE FROM %t WHERE %n=%i', $param['intersect'], $param['field_my'], $this->getPrimaryVal());
					}
				}
			}
		}
	}
	
	final public function mustReload()
	{
		$this->_mustReload = true;
		if ($this->_primary=='id' and $id=$this->_id)
		{
			unset(self::$___cache[ $this->_table ][ $id ]);
		}
		return $this;
	}
	
	final public function save($force=false)
	{
		$this->reload();
		
		$row = $this->_row;
		$this->_pushRestricted($row);
		
		// validates row
		if ($force or $this->_validate($row)!==false)
		{
			// save all chldren (_hasOne, _belongsTo)
			$this->_saveAllParents(/*$row*/$_means_nothing);
			
			// create or update entry
			parent::createOrUpdate($row);
			$this->_assignPrimary();
			$this->_rowState = 'created/updated';
			$this->mustReload();
			
			// save all chldren (_hasMany, _hasAndBelongsToMany)
			$this->_saveAllChildren();
		}
		else
		{
			return false;
		}

		// return self
		return $this;
	}

	final public function create($row=null, $force=null)
	{
		if ($row)
		{
			$this->_pushRestricted($row);
		}
		if ($this->_sanitize($row)!==false and $this->_validate($row)!==false and $this->_uploadFiles($row)!==false and $this->onBeforeCreate($row)!==false)
		{
			// prepare
			if ($row)
			{
				$this->_isolateChildren($row, $children);
				#$this->_row = $row;
				$this->_children = $children;
			}
			// create
			parent::create($row);
			$this->_assignPrimary();
			$this->_saveAllChildren();
			$this->_rowState = 'created';
			$this->mustReload();
			$this->onAfterCreate($row);
			return $this;
		}
		else
		{
			return false;
		}
	}
	
	/*
	final public function update($row=null, $force=false)
	{
		// process
		if ($row)
		{
			dump($row);
			dump($this);die('foo');
			$this->_isolateChildren($row, $children);
			#$this->_row = $row;
			$this->_children = $children;
			$this->_saveAllChildren();
			$this->_pushRestricted($row);
		}
		else
		{
			self::reload();
		}
		// validate & save
		if ($force or ($this->_validate($row)!==false and $this->_uploadFiles($row)!==false and $this->onBeforeUpdate($row)!==false))
		{
			parent::update($row);
			$this->_rowState = 'updated';
			$this->mustReload();
		}
		//
		return $this;
	}
	*/
	
	final public function update($row=null, $force=false)
	{
		if ($row)
		{
			$this->_pushRestricted($row);
		}
		
		// sanitize & validate & upload files & save
		if (($this->_sanitize($row)!==false and $this->_validate($row)!==false and $this->_uploadFiles($row)!==false and $this->onBeforeUpdate($row)!==false) or $force)
		{
			// prepare
			if ($row)
			{
				$this->_isolateChildren($row, $children);
				#$this->_row = $row;
				$this->_children = $children;
			}
			// update
			parent::update($row);
			$this->_assignPrimary();
			$this->_saveAllChildren();
			$this->_rowState = 'updated';
			$this->mustReload();
			$this->onAfterUpdate($row);
			return $this;
		}
		else
		{
			return false;
		}
	}
		
	final public function replace($row=null)
	{
		if ($row)
		{
			$this->_pushRestricted($row);
		}
		// sanitize & validate & upload files & save
		if ($this->_sanitize($row)!==false and $this->_validate($row)!==false and $this->_uploadFiles($row)!==false and $this->onBeforeUpdate($row)!==false)
		{
			// prepare
			if ($row)
			{
				$this->_isolateChildren($row, $children);
				#$this->_row = $row;
				$this->_children = $children;
			}
			// replace
			parent::replace($row);
			$this->_assignPrimary();
			$this->_saveAllChildren();
			$this->_rowState = 'replaced';
			$this->mustReload();
			$this->onAfterUpdate($row);
			return $this;
		}
		else
		{
			return false;
		}
	}
		
	final public function delete($where=null)
	{
		if ($this->onBeforeDelete()!==false)
		{
			parent::delete($where);
			if ($this->getAffected())
			{
				foreach (array_keys($this->_files) as $field) if ($file=$this->{$field})
				{
					if (file_exists(Lemmon_Route::getInstance()->getUploadDir() . $file))
					{
						@unlink(Lemmon_Route::getInstance()->getUploadDir() . $file);
					}
				}
			}
			$this->_id = null;
			$this->_rowState = 'deleted';
			$this->_mustReload = false;
			$this->onAfterDelete();
			return $this;
		}
		else
		{
			return false;
		}
	}
}
