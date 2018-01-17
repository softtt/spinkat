<?php

class Media extends MediaCore
{
    public static $jquery_ui_dependencies = array(
        'ui.core' => array('fileName' => 'jquery.ui.core.min.js', 'dependencies' => array(), 'theme' => true),
        'ui.widget' => array('fileName' => 'jquery.ui.widget.min.js', 'dependencies' => array(), 'theme' => false),
        'ui.mouse' => array('fileName' => 'jquery.ui.mouse.min.js', 'dependencies' => array('ui.core', 'ui.widget'), 'theme' => false),
        'ui.position' => array('fileName' => 'jquery.ui.position.min.js', 'dependencies' => array(), 'theme' => false),
        'ui.draggable' => array('fileName' => 'jquery.ui.draggable.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.mouse'), 'theme' => false),
        'ui.droppable' => array('fileName' => 'jquery.ui.droppable.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.mouse', 'ui.draggable'), 'theme' => false),
        'ui.resizable' => array('fileName' => 'jquery.ui.resizable.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.mouse'), 'theme' => true),
        'ui.selectable' => array('fileName' => 'jquery.ui.selectable.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.mouse'), 'theme' => true),
        'ui.sortable' => array('fileName' => 'jquery.ui.sortable.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.mouse'), 'theme' => true),
        'ui.autocomplete' => array('fileName' => 'jquery.ui.autocomplete.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.position', 'ui.menu'), 'theme' => true),
        'ui.button' => array('fileName' => 'jquery.ui.button.min.js', 'dependencies' => array('ui.core', 'ui.widget'), 'theme' => true),
        'ui.dialog' => array('fileName' => 'jquery.ui.dialog.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.position','ui.button'), 'theme' => true),
        'ui.menu' => array('fileName' => 'jquery.ui.menu.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.position'), 'theme' => true),
        'ui.slider' => array('fileName' => 'jquery.ui.slider.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.mouse'), 'theme' => true),
        'ui.spinner' => array('fileName' => 'jquery.ui.spinner.min.js', 'dependencies' => array('ui.core', 'ui.widget', 'ui.button'), 'theme' => true),
        'ui.tabs' => array('fileName' => 'jquery.ui.tabs.min.js', 'dependencies' => array('ui.core', 'ui.widget'), 'theme' => true),
        'ui.datepicker' => array('fileName' => 'jquery.ui.datepicker.min.js', 'dependencies' => array('ui.core'), 'theme' => true),
        'ui.progressbar' => array('fileName' => 'jquery.ui.progressbar.min.js', 'dependencies' => array('ui.core', 'ui.widget'), 'theme' => true),
        'ui.tooltip' => array('fileName' => 'jquery.ui.tooltip.min.js', 'dependencies' => array('ui.core', 'ui.widget','ui.position','effects.core'), 'theme' => true),
        'ui.accordion' => array('fileName' => 'jquery.ui.accordion.min.js', 'dependencies' => array('ui.core', 'ui.widget','effects.core'), 'theme' => true),
        'effects.core' => array('fileName' => 'jquery.effects.core.min.js', 'dependencies' => array(), 'theme' => false),
        'effects.blind' => array('fileName' => 'jquery.effects.blind.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.bounce' => array('fileName' => 'jquery.effects.bounce.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.clip' => array('fileName' => 'jquery.effects.clip.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.drop' => array('fileName' => 'jquery.effects.drop.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.explode' => array('fileName' => 'jquery.effects.explode.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.fade' => array('fileName' => 'jquery.effects.fade.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.fold' => array('fileName' => 'jquery.effects.fold.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.highlight' => array('fileName' => 'jquery.effects.highlight.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.pulsate' => array('fileName' => 'jquery.effects.pulsate.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.scale' => array('fileName' => 'jquery.effects.scale.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.shake' => array('fileName' => 'jquery.effects.shake.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.slide' => array('fileName' => 'jquery.effects.slide.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'effects.transfer' => array('fileName' => 'jquery.effects.transfer.min.js', 'dependencies' => array('effects.core'), 'theme' => false),
        'ui.touch-punch' => array('fileName' => 'jquery.ui.touch-punch.min.js', 'dependencies' => array('ui.mouse'), 'theme' => false),

    );
}
