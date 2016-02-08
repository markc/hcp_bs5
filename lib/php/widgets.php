<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// widgets.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Widgets
{
    public function a(
        string $href,
        string $label = '',
        string $class = '',
        string $extra = '') : string
    {
error_log(__METHOD__);

        $v = 'veto_a';
        if (method_exists($this, $v))
            extract($this->$v($href, $label, $class, $extra));

        $label = $label ?? $href;
        $class = $class ? ' class="'.$class.'"' : '';
        return '
          <a'.$class.' href="'.$href.'"'.$extra.'>'.$label.'</a>';
    }

    public function button(
        string $label,
        string $type = '',
        string $class = '',
        string $name = '',
        string $value = '',
        string $extra = '') : string
    {
error_log(__METHOD__);

        $v = 'veto_button';
        if (method_exists($this, $v))
            extract($this->$v($label, $type, $class, $name, $value, $extra));

        $class = $class ? ' class="'.$class.'"' : '';
        $type  = $type  ? ' type="'.$type.'"'   : '';
        $name  = $type  ? ' name="'.$name.'"'   : '';
        $value = $value ? ' value="'.$value.'"' : '';
        $extra = $extra ?? '';
        return '
          <button'.$class.$type.$name.$value.$extra.'>'.$label.'</button>';
    }

    public function title(
        string $label,
        string $icon  = '') : string
    {
error_log(__METHOD__);

        $v = 'veto_title';
        if (method_exists($this, $v))
            extract($this->$v($label, $icon));

        $icon  = $icon ? '<i class="' . $icon . '"></i> ' : '';
        return '
          <h2>' . $icon . $label . '</h2>';
    }

}
