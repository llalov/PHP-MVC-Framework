<?php
declare(strict_types=1);

namespace Mvc;

class FormViewHelper
{
    private static $_instance = null;
    private $_elements = array();
    private $_assembledElements = array();
    private $_currentElementId = 0;
    private $_isInForm = false;
    protected $_additionalTokens = array();

    private function  __construct()
    {
    }

    public static function init() : FormViewHelper
    {
        if (self::$_instance == null) {
            self::$_instance = new FormViewHelper();
        }

        return self::$_instance;
    }

    public function initTextBox() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<input type="text"';
        $this->_elements[$this->_currentElementId]['closing tag'] = '>';

        return $this;
    }

    public function initForm(string $action, array $attributes = array(), string $method = 'post') : FormViewHelper
    {
        if ($this->_currentElementId != 0) {
            throw new \Exception('Cannot start form as not first element!', 500);
        }

        $this->_elements['formAttributes'] = $attributes;
        $this->_elements['form']['action'] = $action;
        $this->_elements['form']['method'] = $method;
        $this->_isInForm = true;
        if (strtolower($method) != 'post' && strtolower($method) != 'get') {
            $this->_additionalTokens[$method] = '<input type="hidden" name="_method" value="' . $method . '">';
            $this->_elements['form']['method'] = "post";
        }

        return $this;
    }

    public function initPasswordBox() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<input type="password"';
        $this->_elements[$this->_currentElementId]['closing tag'] = '>';

        return $this;
    }

    public function initTextArea(string $value = '') : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<textarea';
        $this->_elements[$this->_currentElementId]['closing tag'] = ">$value</textarea>";

        return $this;
    }

    public function initUploadFile() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<input type="file"';
        $this->_elements[$this->_currentElementId]['closing tag'] = '>';

        return $this;
    }

    public function initRadioBox() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<input type="radio"';
        $this->_elements[$this->_currentElementId]['closing tag'] = '>';

        return $this;
    }

    public function initSubmit() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<input type="submit"';
        $this->_elements[$this->_currentElementId]['closing tag'] = '>';

        return $this;
    }

    public function initLabel() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<label';
        $this->_elements[$this->_currentElementId]['closing tag'] = '</label>';

        return $this;
    }

    public function initCheckBox() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<input type="checkbox"';
        $this->_elements[$this->_currentElementId]['closing tag'] = '>';

        return $this;
    }

    public function initLink() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<a';
        $this->_elements[$this->_currentElementId]['closing tag'] = '</a>';

        return $this;
    }

    public function initDiv() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = '<div';
        $this->_elements[$this->_currentElementId]['closing tag'] = '</div>';

        return $this;
    }

    public function initBoostrapDropDown(string $value, string $type) : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['opening tag'] = ' <' . $type . ' class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $value . ' <span class="caret"></span></a>
          <ul class="dropdown-menu">';
        $this->_elements[$this->_currentElementId]['closing tag'] = ' </ul></' . $type . '>';

        return $this;
    }

    public function setDropDownLi(string $href, string $value) : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['attributes'][] = '<li><a href="' . $href . '">' . $value . '</a></li>';

        return $this;
    }

    public function setName(string $name) : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['name'] = 'name="' . $name . '"';

        return $this;
    }

    public function setValue(string $value) : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['value'] = '>' . $value;

        return $this;
    }

    public function setAttribute(string $attribute, string $value) : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['attributes'][] = $attribute . '="' . $value . '"';

        return $this;
    }

    public function setChecked() : FormViewHelper
    {
        $this->_elements[$this->_currentElementId]['checked'] = 'checked';

        return $this;
    }

    public function create() : FormViewHelper
    {
        $element = $this->_elements[$this->_currentElementId];
        $html = $element['opening tag'];
        if ($element['name']) {
            $html .= ' ' . $element['name'];
        }

        if ($element['attributes']) {
            foreach ($element['attributes'] as $attribute) {
                $html .= ' ' . $attribute;
            }
        }

        if ($element['checked']) {
            $html .= ' ' . $element['checked'];
        }

        if ($element['value']) {
            $html .= ' ' . $element['value'];
        }

        $html .= $element['closing tag'];

        $this->_assembledElements[$this->_currentElementId] = $html;
        unset($this->_elements[$this->_currentElementId]);
        $this->_currentElementId++;

        return $this;
    }

    public function render(bool $samePageToken = false)
    {
        if ($this->_isInForm) {
            $action = $this->_elements['form']['action'];
            $method = $this->_elements['form']['method'];
            echo '<form action="' . $action . '" method="' . $method . '"';
            $attributes = $this->_elements['formAttributes'];

            //var_dump($attributes);
            foreach ($attributes as $attribute => $value) {
                echo " " . $attribute . '="' . $value . '"';
            }

            echo '>';

        }

        foreach ($this->_assembledElements as $element) {
            echo $element;
        }

        if ($this->_isInForm) {
            Token::init()->render($samePageToken);
            if (count($this->_additionalTokens) != 0) {
                foreach ($this->_additionalTokens as $token) {
                    echo $token;
                }

            }
            echo '</form>';
        }

        $this->_elements = array();
        $this->_currentElementId = 0;
        $this->_isInForm = false;
        $this->_assembledElements = array();
        $this->_additionalTokens = array();
    }
}