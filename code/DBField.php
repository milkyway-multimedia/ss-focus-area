<?php
/**
 * Milkyway Multimedia
 * FocusArea.php
 *
 * @package milkywaymultimedia.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

namespace Milkyway\SS\FocusArea;

class DBField extends \DBField implements \CompositeDBField {
	protected $isChanged = false;

	protected $coordinates = [
		'X1' => 0,
		'Y1' => 0,
		'X2' => 0,
		'Y2' => 0,
	];

	private static $composite_db = [
		'X1' => 'Percentage',
		'Y1' => 'Percentage',
		'X2' => 'Percentage',
		'Y2' => 'Percentage',
	];

	public function compositeDatabaseFields() {
		return self::$composite_db;
	}

	public function requireField() {
		$fields = $this->compositeDatabaseFields();
		if($fields) foreach($fields as $name => $type){
			\DB::requireField($this->tableName, $this->name.$name, $type);
		}
	}

	public function isChanged() {
		return $this->isChanged;
	}

	public function setX1($x1, $markChanged = true) {
		$this->coordinates['X1'] = $x1;
		if($markChanged) $this->isChanged = true;
	}

	public function setX2($x2, $markChanged = true) {
		$this->coordinates['X2'] = $x2;
		if($markChanged) $this->isChanged = true;
	}

	public function setY1($y1, $markChanged = true) {
		$this->coordinates['Y1'] = $y1;
		if($markChanged) $this->isChanged = true;
	}

	public function setY2($y2, $markChanged = true) {
		$this->coordinates['Y2'] = $y2;
		if($markChanged) $this->isChanged = true;
	}

	public function getX1() {
		return isset($this->coordinates['X1']) ? $this->coordinates['X1'] : 0.00;
	}

	public function getX2() {
		return isset($this->coordinates['X2']) ? $this->coordinates['X2'] : 0.00;
	}

	public function getY1() {
		return isset($this->coordinates['Y1']) ? $this->coordinates['Y1'] : 0.00;
	}

	public function getY2() {
		return isset($this->coordinates['Y2']) ? $this->coordinates['Y2'] : 0.00;
	}

	public function setValue($value, $record = null, $markChanged = true) {
		if($value instanceof DBField) {
			$this->setX1($value->X1, $markChanged);
			$this->setY1($value->Y1, $markChanged);
			$this->setX2($value->X2, $markChanged);
			$this->setY2($value->Y2, $markChanged);
			return;
		}
		elseif($record) {
			if($record instanceof \DataObject)
				$record = $record->getQueriedDatabaseFields();

			foreach(array_keys(static::$composite_db) as $point) {
				$this->{$point} = isset($record[$this->name . $point]) ? $record[$this->name . $point] : null;
			}
		}
		elseif(is_array($value)) {
			foreach(array_keys(static::$composite_db) as $point) {
				if(isset($record[$point]))
					$this->{$point} = isset($record[$point]);
			}
		}
	}

	public function writeToManipulation(&$manipulation) {
		foreach(static::$composite_db as $point => $type) {
			if($this->$point)
				$manipulation['fields'][$this->name.$point] = $this->prepValueForDB($this->$point);
			else
				$manipulation['fields'][$this->name.$point] = \DBField::create_field($type, $this->$point)->nullValue();
		}
	}

	public function scaffoldFormField($title = null) {
		return \FocusAreaField::create($this->name);
	}

	public function __toString() {
		$string = [];

		foreach(array_keys(static::$composite_db) as $point) {
			$string[] = $point . ': ' . (float)$this->$point;
		}

		return implode(', ', $string);
	}

	public function toArray() {
		$points = [];

		foreach(array_keys(static::$composite_db) as $point) {
			$points[$point] = $this->$point;
		}

		return $points;
	}

	public function exists() {
		return count(array_filter($this->coordinates));
	}
} 