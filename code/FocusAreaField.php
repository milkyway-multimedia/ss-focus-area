<?php

use \Milkyway\SS\FocusArea\DBField;

class FocusAreaField extends FormField {
	public $object;

	protected $children = [];

	public function __construct($name, $title = null, $value = null, $object = null, $form = null) {
		$this->object = $object;

		foreach(DBField::config()->composite_db as $point => $type) {
			$this->children[$point] = \HiddenField::create($name . '[' . $point . ']')->addExtraClass('focus-area-field-point focus-area-field-point--' . $point)->setForm($form);
		}

		parent::__construct($name, $title, $value, $form);
	}
	
	public function Field($properties = array()) {
	
		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery/jquery.js');
		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');

		Requirements::javascript(SS_FOCUS_AREA_DIR . '/thirdparty/jcrop/js/jquery.jcrop.js');
		Requirements::css(SS_FOCUS_AREA_DIR . '/thirdparty/jcrop/css/jquery.jcrop.min.css');

		Requirements::javascript(SS_FOCUS_AREA_DIR . '/javascript/mwm.focus-area.js');
		Requirements::css(SS_FOCUS_AREA_DIR . '/css/mwm.focus-area.css');
		
		return parent::Field($properties);
	}

	public function PointFields() {
		$fields = '';

		foreach($this->children as $field)
			$fields .= $field->Field();

		return $fields;
	}

	public function ObjectField() {
		$object = $this->object;

		if(!$this->object && $this->form && $this->form->Record)
			$object = $this->form->Record;

		return $this->getHtmlForObjectField($object);
	}

	protected function getHtmlForObjectField($object = null) {
		if(!$object)
			return '<div class="focus-area-field--default"></div>';

		if($object instanceof \Milkyway\SS\FocusArea\Contracts\HasPreviewForFocusArea)
			return '<div class="focus-area-field--page">' . $object->previewHtmlForFocusArea() . '</div>';
		elseif($object instanceof \Milkyway\SS\FocusArea\Contracts\HasPreviewForFocusArea_Link)
			return '<iframe class="focus-area-field--frame" src="' . $object->previewLinkForFocusArea() . '"></iframe>';
		elseif($object instanceof CMSPreviewable)
			return '<iframe class="focus-area-field--frame" src="' . $object->Link() . '"></iframe>';
		elseif($object instanceof Image) {
			if($object->hasMethod('CroppedFocusedImage'))
				return '<img class="focus-area-field--image" src="' . $object->CroppedFocusedImage(300,240) . '" />';
			else
				return '<img class="focus-area-field--image" src="' . $object->CroppedImage(300,240) . '" />';
		}

		return '<div class="focus-area-field--default"></div>';
	}

	public function setForm($form) {
		foreach($this->children as $field)
			$field->setForm($form);

		return parent::setForm($form);
	}

	public function setObject($object) {
		$this->object = $object;
	}

	public function getObject() {
		return $this->object;
	}

	public function setPoint($point, $value = null) {
		if(isset($this->children[$point]))
			$this->children[$point]->Value = $value;
	}

	public function setValue($value) {
		$this->value = $value;

		if($value instanceof DBField)
			$value = $value->toArray();
		elseif(is_string($value))
			$value = explode(',', $value);

		if(is_array($value)) {
			foreach(DBField::config()->composite_db as $point => $type) {
				if(isset($value[$point]) && isset($this->children[$point]))
					$this->children[$point]->Value = $value[$point];
			}
		}

		return $this;
	}

	public function saveInto(DataObjectInterface $record) {
		if($record->hasMethod("set{$this->name}")) {
			$values = [];

			foreach($this->children as $point => $field) {
				$values[$point] = $field[$point]->Value();
			}

			$record->{$this->name} = DBField::create_field('Milkyway\SS\FocusArea\DBField', $values);
		} else {
			foreach($this->children as $point => $field) {
				$record->{$this->name}->$point = $field->Value();
			}
		}
	}
}