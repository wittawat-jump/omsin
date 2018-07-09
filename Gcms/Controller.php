<?php
/**
 * @filesource Gcms/Controller.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Gcms;

/**
 * Controller base class.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
    /**
     * View.
     *
     * @var \Gcms\View
     */
    public static $view;

    /**
     * เก็บคลาสของเมนูที่เลือก
     *
     * @var string
     */
    protected $menu;

    /**
     * Menu Controller.
     *
     * @var \Index\Menu\Controller
     */
    protected static $menus;

    /**
     * ข้อความไตเติลบาร์.
     *
     * @var string
     */
    protected $title;

    /**
     * init Class.
     */
    public function __construct()
    {
        // ค่าเริ่มต้นของ Controller
        $this->title = strip_tags(self::$cfg->web_title);
        $this->menu = 'home';
    }

    /**
     * ชื่อเมนูที่เลือก
     *
     * @return string
     */
    public function menu()
    {
        return $this->menu;
    }

    /**
     * ข้อความ title bar.
     *
     * @return string
     */
    public function title()
    {
        return $this->title;
    }
}
