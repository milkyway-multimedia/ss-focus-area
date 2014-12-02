<?php
/**
 * Milkyway Multimedia
 * FocusArea.php
 *
 * @package milkywaymultimedia.com.au
 * @author  Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\FocusArea;

class DBField extends \DBField implements \CompositeDBField
{
	protected $isChanged = false;

	protected $coordinates = [
		'X1'     => 0,
		'Y1'     => 0,
		'X2'     => 0,
		'Y2'     => 0,

		'Width'  => 0,
		'Height' => 0,

		'FromTop'    => 0,
		'FromLeft'   => 0,
		'FromRight'  => 0,
		'FromBottom' => 0,
	];

	private static $composite_db = [
		'X1'     => 'Float',
		'Y1'     => 'Float',
		'X2'     => 'Float',
		'Y2'     => 'Float',

		'Width'  => 'Float',
		'Height' => 'Float',

		'FromTop'    => 'Percentage',
		'FromLeft'   => 'Percentage',
		'FromRight'  => 'Percentage',
		'FromBottom' => 'Percentage',
	];

	private static $casting = [
		'X1'     => 'Float',
		'Y1'     => 'Float',
		'X2'     => 'Float',
		'Y2'     => 'Float',

		'Width'  => 'Float',
		'Height' => 'Float',

		'FromTop'    => 'Percentage',
		'FromLeft'   => 'Percentage',
		'FromRight'  => 'Percentage',
		'FromBottom' => 'Percentage',
	];

	public function compositeDatabaseFields()
	{
		return self::$composite_db;
	}

	public function requireField()
	{
		$fields = $this->compositeDatabaseFields();
		if ($fields) foreach ($fields as $name => $type) {
			\DB::requireField($this->tableName, $this->name . $name, $type);
		}
	}

	public function isChanged()
	{
		return $this->isChanged;
	}

	public function setValue($value, $record = null, $markChanged = true)
	{
		if ($value instanceof DBField) {
			foreach (array_keys($this->config()->composite_db) as $point) {
				$this->{'set'.$point}($this->$point, $markChanged);
			}

			return;
		} elseif ($record) {
			if ($record instanceof \DataObject)
				$record = $record->getQueriedDatabaseFields();

			foreach (array_keys($this->config()->composite_db) as $point) {
				$this->{$point} = isset($record[$this->name . $point]) ? $record[$this->name . $point] : null;
			}
		} elseif (is_array($value)) {
			foreach (array_keys($this->config()->composite_db) as $point) {
				if (isset($record[$point]))
					$this->{$point} = isset($record[$point]);
			}
		}
	}

	public function writeToManipulation(&$manipulation)
	{
		foreach ($this->config()->composite_db as $point => $type) {
			if ($this->$point)
				$manipulation['fields'][$this->name . $point] = \DBField::create_field($type, $this->$point)->prepValueForDB($this->$point);
			else
				$manipulation['fields'][$this->name . $point] = \DBField::create_field($type, $this->$point)->nullValue();
		}
	}

	public function scaffoldFormField($title = null)
	{
		return \FocusAreaField::create($this->name);
	}

	public function __toString()
	{
		$string = [];

		foreach (array_keys($this->config()->composite_db) as $point) {
			$string[] = $point . ': ' . $this->$point;
		}

		return implode(', ', $string);
	}

	public function toArray()
	{
		$points = [];

		foreach (array_keys($this->config()->composite_db) as $point) {
			$points[$point] = $this->$point;
		}

		return $points;
	}

	public function exists()
	{
		return count(array_filter($this->coordinates));
	}

	public function __set($property, $value)
	{
		if ($this->hasMethod($method = "set$property")) {
			$this->$method($value);
		} elseif (isset($this->coordinates[$property])) {
			$args = func_get_args();
			$markChanged = array_key_exists(1, $args) ? $args[1] : true;
			$this->coordinates[$property] = $value;

			if ($markChanged)
				$this->isChanged = true;
		} else
			parent::__set($property, $value);
	}

	public function __get($property)
	{
		if ($this->hasMethod($method = "get$property")) {
			return $this->$method();
		} elseif (isset($this->coordinates[$property]))
			return $this->coordinates[$property];
		else
			return parent::__get($property);
	}

	public function __call($method, $arguments) {
		if(strpos($method, 'set') === 0) {
			$property = substr($method, 3);

			if(isset($this->coordinates[$property])) {
				return call_user_func_array([$this, '__set'], array_merge([$property], $arguments));
			}
		}

		return parent::__call($method, $arguments);
	}
} 