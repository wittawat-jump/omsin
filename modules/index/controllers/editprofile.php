<?php
/**
 * @filesource modules/index/controllers/editprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Editprofile;

use \Kotchasan\Http\Request;
use \Gcms\Login;
use \Kotchasan\Html;
use \Kotchasan\Language;

/**
 * module=editprofile
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * แก้ไขข้อมูลส่วนตัวสมาชิก
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // ข้อความ title bar
    $this->title = Language::get('Edit profile');
    // เลือกเมนู
    $this->menu = 'tools';
    // สมาชิก
    if ($login = Login::isMember()) {
      // อ่านข้อมูลที่ id ถ้าไม่มีใช้คนที่ login
      $user = \Index\Member\Model::get($request->request('id', $login['id'])->toInt());
      // ตัวเอง
      if ($user && $user['id'] == $login['id']) {
        // แสดงผล
        $section = Html::create('section');
        // breadcrumbs
        $breadcrumbs = $section->add('div', array(
          'class' => 'breadcrumbs'
        ));
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><a class="icon-home" href="index.php">{LNG_Home}</a></li>');
        $ul->appendChild('<li><span>{LNG_Profile}</span></li>');
        $section->add('header', array(
          'innerHTML' => '<h2 class="icon-user">'.$this->title.'</h2>'
        ));
        $section->add('a', array(
          'id' => "ierecord",
          'href' => WEB_URL.'index.php?module=ierecord',
          'title' => "{LNG_Recording} {LNG_Income}/{LNG_Expense}",
          'class' => 'icon-edit notext'
        ));
        // แสดงฟอร์ม
        $section->appendChild(createClass('Index\Editprofile\View')->render($user, $login));
        return $section->render();
      }
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}
