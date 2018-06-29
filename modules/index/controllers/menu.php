<?php
/**
 * @filesource modules/index/controllers/menu.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

/**
 * รายการเมนู.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller
{
    /**
     * รายการเมนู.
     *
     * @var array
     */
    private $menus;

    /**
     * Controller สำหรับการโหลดเมนู.
     *
     * @param array $login
     *
     * @return \static
     */
    public static function init($login)
    {
        $obj = new static();
        // โหลดเมนู
        $obj->menus = \Index\Menu\Model::getMenus($login);

        return $obj;
    }

    /**
     * แสดงผลเมนู.
     *
     * @param string $select
     * @param array  $login
     *
     * @return string
     */
    public function render($select, $login)
    {
        return \Kotchasan\Menu::render($this->menus, $select);
    }

    /**
     * เมนูรายการแรก (หน้าหลัก).
     *
     * @return string
     */
    public function home()
    {
        $keys = array_keys($this->menus);

        return reset($keys);
    }
}
