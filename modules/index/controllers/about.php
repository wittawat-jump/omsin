<?php
/**
 * @filesource modules/index/controllers/about.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\About;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Login;

/**
 * module=dashboard.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * Dashboard.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // สมาชิก
        if (Login::isMember()) {
            // ข้อความ title bar
            $this->title = Language::get('About');
            // เลือกเมนู
            $this->menu = 'about';
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><a class="icon-home" href="index.php">{LNG_Home}</a></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-info">'.$this->title.'</h2>',
            ));
            $section->add('a', array(
                'id' => 'ierecord',
                'href' => WEB_URL.'index.php?module=ierecord',
                'title' => '{LNG_Recording} {LNG_Income}/{LNG_Expense}',
                'class' => 'icon-edit notext',
            ));
            // แสดงฟอร์ม
            $section->appendChild(createClass('Index\About\View')->render($request));

            return $section->render();
        }
        // 404.html

        return \Index\Error\Controller::page404();
    }
}
