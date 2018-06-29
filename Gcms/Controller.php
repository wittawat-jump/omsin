<?php
/**
 * @filesource Gcms/Controller.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
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
     * ข้อความไตเติลบาร์.
     *
     * @var string
     */
    protected $title;
    /**
     * เก็บคลาสของเมนูที่เลือก
     *
     * @var string
     */
    protected $menu;
    /**
     * View.
     *
     * @var \Gcms\View
     */
    public static $view;
    /**
     * Menu Controller.
     *
     * @var \Index\Menu\Controller
     */
    protected static $menus;

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
     * ข้อความ title bar.
     *
     * @return string
     */
    public function title()
    {
        return $this->title;
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
}
