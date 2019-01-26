<?php
/**
 * @filesource modules/index/views/database.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Database;

use Kotchasan\Html;
use Kotchasan\Http\Request;

/**
 * module=database.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * ฟอร์ม Import/Export.
   *
   * @param Request $request
   *
   * @return string
   */
  public function render(Request $request)
  {
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/index/model/database/submit',
        'onsubmit' => 'doFormSubmit',
        'token' => true,
        'ajax' => true,
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Import}',
    ));
    // import
    $fieldset->add('file', array(
      'id' => 'csv',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Browse file}',
      'placeholder' => 'omsin.csv',
      'comment' => '{LNG_Select a file for importing, omsin.csv only (<a href="index.php/index/model/database/demo" target=_blank><em>download sample here</em></a>)}',
      'accept' => array('csv'),
    ));
    $div = $fieldset->add('div', array(
      'class' => 'item',
    ));
    // submit
    $div->add('submit', array(
      'class' => 'button ok large icon-import',
      'value' => '{LNG_Import}',
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Export}',
    ));
    $div = $fieldset->add('div', array(
      'class' => 'item',
    ));
    $div->add('div', array(
      'class' => 'message',
      'innerHTML' => '{LNG_Download <em>omsin.csv</em> file for database backup}',
    ));
    // export
    $div->add('a', array(
      'class' => 'button ok large icon-export',
      'innerHTML' => '{LNG_Export}',
      'href' => WEB_URL.'index.php/index/model/database/export',
      'target' => '_blank',
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'item',
    ));
    $div = $fieldset->add('div', array(
      'class' => 'warning',
      'innerHTML' => '<p>{LNG_press Reset button to delete all user data}</p>',
    ));
    // submit
    $div->add('button', array(
      'id' => 'database_reset',
      'class' => 'button red large icon-delete',
      'value' => '{LNG_Reset}',
    ));
    // Javascript
    $form->script('callClick("database_reset", doDatabaseReset);');
    // คืนค่าฟอร์ม

    return $form->render();
  }
}