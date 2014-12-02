<?php

use \Milkyway\SS\FocusArea\DBField;

class FocusAreaField extends FormField {
	public $object;
	public $link;

	protected $children = [];

	public function __construct($name, $title = null, $value = null, $object = null, $form = null) {
		$this->object = $object;

		foreach(DBField::config()->composite_db as $point => $type) {
			$this->children[$point] = \HiddenField::create($name . '[' . $point . ']')->addExtraClass('focusarea-point focusarea-point--' . $point)->setForm($form);
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

	public function setForm($form) {
		foreach($this->children as $field)
			$field->setForm($form);

		return parent::setForm($form);
	}

	public function setObject($object) {
		$this->object = $object;
		return $this;
	}

	public function getObject() {
		return $this->object;
	}

	public function setLink($link) {
		$this->link = $link;
		return $this;
	}

	public function getLink() {
		return $this->link;
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

	protected function getHtmlForObjectField($object = null) {
		if($this->link)
			return '<iframe class="focusarea--frame" src="' . $this->link . '"></iframe>';
		elseif(!$object)
			return '<div class="focusarea--default"></div>';
		elseif($object instanceof \Milkyway\SS\FocusArea\Contracts\HasPreviewForFocusArea_Object)
			return $this->getHtmlForObjectField($object->previewObjectForFocusArea());
		elseif($object instanceof \Milkyway\SS\FocusArea\Contracts\HasPreviewForFocusArea)
			return '<div class="focusarea--page">' . $object->previewHtmlForFocusArea() . '</div>';
		elseif($object instanceof \Milkyway\SS\FocusArea\Contracts\HasPreviewForFocusArea_Link)
			return '<iframe class="focusarea--frame" src="' . $object->previewLinkForFocusArea() . '"></iframe>';
		elseif($object instanceof CMSPreviewable)
			return '<iframe class="focusarea--frame" src="' . $object->Link() . '"></iframe>';
		elseif($object instanceof Image) {
			if($object->hasMethod('CroppedFocusedImage'))
				return '<img class="focusarea--image" src="' . $object->CroppedFocusedImage(360,225) . '" />';
			else
				return '<img class="focusarea--image" src="' . $object->CroppedImage(360,225) . '" />';
		}

		return '<div class="focusarea--default"></div>';
	}
}