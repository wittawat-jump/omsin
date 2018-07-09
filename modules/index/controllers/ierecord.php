<?php
/**
 * @filesource modules/index/controllers/ierecord.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Ierecord;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Login;

/**
 * module=ierecord.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ฟอร์มบันทึกรายรับ-รายจ่าย.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // สมาชิก
        if ($login = Login::isMember()) {
            // ข้อความ title bar
            $this->title = Language::get('Recording').' '.Language::get('Income').'/'.Language::get('Expense');
            // เลือกเมนู
            $this->menu = 'ierecord';
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><a class="icon-home" href="index.php">{LNG_Home}</a></li>');
            $ul->appendChild('<li><span>{LNG_Recording}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-billing">'.$this->title.'</h2>',
            ));
            // แสดงฟอร์ม
            $section->appendChild(createClass('Index\Ierecord\View')->render($request, (object) $login));

            return $section->render();
        }
        // 404.html

        return \Index\Error\Controller::page404();
    }
}
