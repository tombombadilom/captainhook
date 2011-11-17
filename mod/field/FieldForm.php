<?php  //  -*- mode:php; tab-width:2; c-basic-offset:2; -*-

namespace mod\field;

class FieldForm {
	private $smarty;
	private $uniquename;
	private $fields=array();
	private static $validators=array();
	private $html;
	
	public function __construct($uniquename, $tpl) {
		$this->uniquename=$uniquename;
		$this->smarty=\mod\smarty\Main::newSmarty();
		$this->smarty->assign('fieldform', $this);
		$this->html=$this->smarty->fetch($tpl);
		/*
		if ($this->isPosted()) {
			foreach($this->fields as $field) $field->value=$field->getValue();
		}
		*/
	}

	// used internaly by smarty
	public static function _addValidator($validator) {
		self::$validators[$validator->name]=$validator;
	}

	// used internaly by smarty
	public static function getValidator($name) {
		if (isset(self::$validators[$name])) return self::$validators[$name];
		else throw new \Exception("Validator '".$name."' not found");
	}

	public function get_html($webpage) {
		\mod\cssjs\Main::addJs($webpage, '/mod/cssjs/js/mootools.js');
		\mod\cssjs\Main::addJs($webpage, '/mod/cssjs/js/mootools.more.js');
		//\mod\cssjs\Main::addJs($webpage, '/mod/field/js/field.js');
		$js="<script>";
		$js.="myForm=document.id('niclotest');";
		$js.="new Form.Validator.Inline(myForm, { evaluateFieldsOnChange: true, useTitles: true, warningPrefix: '', errorPrefix: '' });";
		foreach(self::$validators as $validator) {
			$js.=$validator->get_mootools_js();
		}
		//$js.="myForm.validate();";
		$js.="</script>";
		return "<form id='niclotest' method='POST'><input type='hidden' name='field_fieldform_uniquename' value='".$this->uniquename."'/>".$this->html."</form> $js";
	}

	public function isPosted() {
		return isset($_POST) && isset($_POST['field_fieldform_uniquename']) && $_POST['field_fieldform_uniquename'] == $this->uniquename;
	}

	public function isValid() {
		foreach($this->fields as $field) {
			$res=$field->validate($field->getValue());
			if (count($res)) return false;
		}
		return true;
	}

	public function getValue($fieldname) {
		foreach($this->fields as $field)
			if ($field->name == $fieldname) return $field->getValue();
		throw new \Exception("Field '".$fieldname."'not found");
	}

	public function addField($field) {
		$this->fields[]=$field;
	}

	public function sqlinsert() {
		$querys=array();
		foreach($this->fields as $field) {
			if (!isset($field->params['sqltable'])) continue;
			$sqltable=$field->params['sqltable'];
			if (!isset($querys[$sqltable]))
				$querys[$sqltable]=array('vals' => array(), 'a' => '', 'b' => '');
			$query=&$querys[$sqltable];

			$query['a'].=($query['a'] == '' ? '' : ',').'`'.$field->name.'`';
			$query['b'].=($query['b'] == '' ? '' : ',').'?';
			$query['vals'][]=$field->getValue();
		}

		if (!count($querys)) throw new \Exception("No columns found to insert, maybe you have omited to add sqltable args to you're fields");

		foreach($querys as $sqltable => $query) {
			$q="INSERT INTO `".$sqltable."` (".$query['a'].") VALUES (".$query['b'].")";
			\core\Core::$db->query($q, $query['vals']);
		}

		return \core\Core::$db->Insert_ID();
	}

	public function sqlupdate($id) {
		$vals=array();
		$a='';
		foreach($this->fields as $field) {
			$a.=($a == '' ? '' : ',').'`'.$field->name.'`=?';
			$vals[]=$field->getValue();
		}

		$q="UPDATE `$table` SET $a WHERE `id`=?";
		$vals[]=$id;
		\core\Core::$db->query($q, $vals);

		return \core\Core::$db->Insert_ID();
	}
}

class Validator {
	public $name;
	public $regexp;
	public $message;
	public $inverted;
	public function __construct($name, $regexp, $message, $inverted) {
		$this->name=$name;
		$this->regexp=$regexp;
		$this->message=$message;
		$this->inverted=$inverted;
	}

	public function get_mootools_string() {
		return $this->name;
	}

	public function get_mootools_js() {
		return '
Form.Validator.add("'.addslashes($this->name).'", {
    errorMsg: "'.addslashes($this->message).'",
    test: function(field) {
	return '.($this->inverted ? '' : '!').'field.get("value").test('.$this->regexp.');
    }
});
';
	}
}

class Element {
	public $name;
	public $value;
	public $params;
	private $validators=array();

	public function __construct($params) {
		if (!isset($params['name']))
			throw new \Exception("Element must have a name parameter");
		$this->name=$params['name'];
		$this->value=isset($this->value) ? $this->value : '';
		$this->params=$params;
		foreach($this->params as $paramname => $param) {
			switch($paramname) {
			case 'validators':
				foreach(explode(',',$param) as $vname)
					$this->addValidator(FieldForm::getValidator(trim($vname)));
				break;
			}
		}
	}

	public function addValidator($validator) {
		$this->validators[$validator->name]=$validator;
	}

	public function validate($value) {
		$result=array();
		foreach($this->validators as $validator) {
			$res=preg_match($validator->regexp, $value);
			if (($res && !$validator->inverted) || (!$res && $validator->inverted)) {
				$result[]=$validator->message;
			}
		}
		return $result;
	}

	public function validatetohtml($value) {
		$validators=$this->validate($value);
		$res='';
		foreach($validators as $validator)
			$res.="<span class='field_error'>$validator</span>";
		return $res;
	}

	public function getValue() {
		if (isset($_POST[$this->name])) return $_POST[$this->name];
		return $this->params['value'];
	}

	public function get_mootools_validators_string() {
		$str='';
		foreach($this->validators as $validator) {
			$tmp=$validator->get_mootools_string();
			if ($tmp) $str.=($str ? ' ' : '').$tmp;
		}
		if ($str) $str='data-validators="'.$str.'"';
		return $str;
	}

	public function getParamsStr($exclude=array()) {
		$str='';
		foreach($this->params as $k => $v)
			if (!in_array($k, $exclude) && !in_array($k, array('phpclass', 'sqltable', 'validators')))
				$str.=($str ? ' ' : '').$k."='$v'";
		return $str;
	}
}

class Text extends Element {
	public function render() {
		return sprintf("<input %s type='text' name='%s' value='%s' ".$this->get_mootools_validators_string()."/>",
									 $this->getParamsStr(array('name','value','type')),
									 $this->name, $this->getValue()
									 );
	}
}

class Hidden extends Element {
	public function render() {
		return sprintf("<input %s type='hidden' name='%s' value='%s'/>",
									 $this->getParamsStr(array('name','value','type')),
									 $this->name, $this->getValue()
									 );
	}
}

class RadioGroup extends Element {
	private $radios=array();
	
	public function addRadio($radio) {
		$this->radios[]=$radio;
	}

	public function render() {
	}
}

class Radio extends Element {
	public function render() {
		return sprintf("<input %s type='radio' name='%s' value='%s'%s/>",
									 $this->getParamsStr(array('name','value','type')),
									 $this->name, $this->params['value'], $this->is_checked() ? ' checked' : ''
									 );
	}
	public function is_checked() {
		if (isset($_POST[$this->name]) && ($_POST[$this->name] == $this->params['value']))
			return true;
		if (isset($this->params['checked'])) return true;
		return false;
	}
}

class Checkbox extends Element {
}

class Password extends Element {
	public function render() {
		return sprintf("<input %s type='password' name='%s' value='%s' ".$this->get_mootools_validators_string()."/>",
									 $this->getParamsStr(array('name','value','type')),
									 $this->name, $this->getValue()
									 );
	}
}

class Textarea extends Element {
}

class Select extends Element {
}

class Submit extends Element {
	public function render() {
		return sprintf("<input %s type='submit' name='%s' value='%s'/>",
									 $this->getParamsStr(array('name','value','type')),
									 $this->name, $this->getValue()
									 );
	}
}

