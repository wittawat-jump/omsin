<?php
/**
 * @filesource modules/index/controllers/database.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Database;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Login;

/**
 * module=database.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * เครื่องมือในการจัดการฐานข้อมูล.
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
            $this->title = Language::get('Import').'/'.Language::get('Export');
            // เลือกเมนู
            $this->menu = 'tools';
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><a class="icon-home" href="index.php">{LNG_Home}</a></li>');
            $ul->appendChild('<li><span>{LNG_Tools}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-database">'.$this->title.'</h2>',
            ));
            $section->add('a', array(
                'id' => 'ierecord',
                'href' => WEB_URL.'index.php?module=ierecord',
                'title' => '{LNG_Recording} {LNG_Income}/{LNG_Expense}',
                'class' => 'icon-edit notext',
            ));
            // แสดงตาราง
            $section->appendChild(createClass('Index\Database\View')->render($request));

            return $section->render();
        }
        // 404.html
        return \Index\Error\Controller::page404();
    }
}
