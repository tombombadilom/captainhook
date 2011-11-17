<?php  //  -*- mode:php; tab-width:2; c-basic-offset:2; -*-

namespace mod\field;

class Main {

  public static function smartyFunction_Field($params, $template) {
		$fieldform = $template->smarty->tpl_vars->fieldform->value;
		if (!isset($params['phpclass']))
			throw new \Exception("Field must have a phpclass");
		$classname=$params['phpclass'];
		if (isset($fieldform->_curfieldgroup) && !isset($params['name']))
			$params['name']=$fieldform->_curfieldgroup->params['name'];
		$field = new $classname($params);
		$fieldform->addField($field);
		return $field->render();
  }
	
  public static function smartyFunction_FieldValidator($params, $template) {
		if (!isset($params['name']))
			throw new \Exception("FieldValidator must have a 'name' parameter");
		if (!isset($params['message']))
			throw new \Exception("FieldValidator must have a 'message' parameter");

		if (isset($params['regexp'])) {
			$regexp=$params['regexp'];
			$inverted=false;
		} else if (isset($params['iregexp'])) {
			$regexp=$params['iregexp'];
			$inverted=true;
		} else throw new \Exception("FieldVerification must have at least regexp nor iregexp argument");

		FieldForm::_addValidator(new Validator($params['name'], $regexp, $params['message'], $inverted));
	}

	public static function smartyBlock_FieldGroup($params, $content, $smarty, &$repeat) {
		$fieldform = $smarty->tpl_vars->fieldform->value;
		if ($content === NULL) { // open tag
			if (!isset($params['phpclass']))
				throw new \Exception("FieldGroup must have a phpclass");
			$classname=$params['phpclass'];
			$fieldform->_curfieldgroup=new $classname($params);
		} else {
			$field=$fieldform->_curfieldgroup;
			$fieldform->addField($field);
			unset($fieldform->_curfieldgroup);
			return $content.$field->render();
		}
	}

}

