<?php
/**
 * @filesource Gcms/Login.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Gcms;

use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Password;
use Kotchasan\Text;

/**
 * คลาสสำหรับตรวจสอบการ Login.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Kotchasan\Login implements \Kotchasan\LoginInterface
{
    /**
     * ตรวจสอบความสามารถในการเข้าระบบแอดมิน
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าสามารถเข้าระบบแอดมินได้ ไม่ใช่คืนค่า null.
     *
     * @return array|null
     */
    public static function adminAccess()
    {
        $login = self::isMember();

        return isset($login['status']) && $login['status'] == 1 ? $login : null;
    }

    /**
     * ฟังก์ชั่นตรวจสอบการ login และบันทึกการเข้าระบบ
     * เข้าระบบสำเร็จคืนค่าแอเรย์ข้อมูลสมาชิก, ไม่สำเร็จ คืนค่าข้อความผิดพลาด.
     *
     * @param array $params ข้อมูลการ login ที่ส่งมา $params = array('username' => '', 'password' => '');
     *
     * @return string|array
     */
    public function checkLogin($params)
    {
        // ตรวจสอบสมาชิกกับฐานข้อมูล
        $login_result = self::checkMember($params);
        if (is_string($login_result)) {
            return $login_result;
        } else {
            // ip ที่ login
            $ip = self::$request->getClientIp();
            // current session
            $session_id = session_id();
            // อัปเดทการเยี่ยมชม
            if ($session_id != $login_result['session_id']) {
                ++$login_result['visited'];
                \Kotchasan\Model::createQuery()
                    ->update('user')
                    ->set(array(
                        'session_id' => $session_id,
                        'visited' => $login_result['visited'],
                        'lastvisited' => time(),
                        'ip' => $ip,
                    ))
                    ->where((int) $login_result['id'])
                    ->execute();
            }
        }

        return $login_result;
    }

    /**
     * ฟังก์ชั่นตรวจสอบสมาชิกกับฐานข้อมูล
     * คืนค่าข้อมูลสมาชิก (array) ไม่พบคืนค่าข้อความผิดพลาด (string).
     *
     * @param array $params
     *
     * @return array|string
     */
    public static function checkMember($params)
    {
        // query Where
        $where = array();
        foreach (self::$cfg->login_fields as $field) {
            $where[] = array("U.{$field}", $params['username']);
        }
        $query = \Kotchasan\Model::createQuery()
            ->select('U.*', 'U.id account_id')
            ->from('user U')
            ->where($where, 'OR')
            ->order('U.status DESC')
            ->toArray();
        $login_result = null;
        foreach ($query->execute() as $item) {
            if ($item['password'] == sha1($params['password'].$item['salt'])) {
                $login_result = $item;
                break;
            }
        }
        if ($login_result === null) {
            // user หรือ password ไม่ถูกต้อง
            self::$login_input = isset($item) ? 'password' : 'username';

            return isset($item) ? Language::replace('Incorrect :name', array(':name' => Language::get('Password'))) : Language::get('not a registered user');
        } elseif (!empty($login_result['ban'])) {
            // ติดแบน
            self::$login_input = 'username';

            return Language::get('Members were suspended');
        } else {
            return $login_result;
        }
    }

    /**
     * ตรวจสอบการ login เมื่อมีการเรียกใช้ class new Login
     * action=logout ออกจากระบบ
     * มาจากการ submit ตรวจสอบการ login
     * ถ้าไม่มีทั้งสองส่วนด้านบน จะตรวจสอบการ login จาก session และ cookie ตามลำดับ.
     *
     * @return \static
     */
    public static function create($check = false)
    {
        // create class
        $login = new static();
        // การเข้ารหัส
        $pw = new Password(self::$cfg->password_key);
        // ชื่อฟิลด์สำหรับการรับค่าเป็นรายการแรกของ login_fields
        $field_name = reset(self::$cfg->login_fields);
        // อ่านข้อมูลจากฟอร์ม login ฟิลด์ login_username
        self::$login_params['username'] = self::$request->post('login_username')->toString();
        if (empty(self::$login_params['username'])) {
            if (isset($_SESSION['login']) && isset($_SESSION['login'][$field_name])) {
                // from session
                self::$login_params['username'] = $_SESSION['login'][$field_name];
            } else {
                // from cookie
                $datas = self::$request->getCookieParams();
                self::$login_params['username'] = isset($datas['login_username']) ? $pw->decode($datas['login_username']) : null;
            }
            self::$from_submit = false;
        } else {
            self::$from_submit = true;
        }
        self::$login_params['username'] = Text::username(self::$login_params['username']);
        self::$login_params['password'] = self::get('password', $pw);
        $login_remember = self::get('remember') == 1 ? 1 : 0;
        // ตรวจสอบการ login
        if (self::$request->get('action')->toString() === 'logout' && !self::$from_submit) {
            // logout ลบ session และ cookie
            unset($_SESSION['login']);
            $time = time();
            setcookie('login_username', '', $time, '/', null, null, true);
            setcookie('login_password', '', $time, '/', null, null, true);
            self::$login_message = Language::get('Logout successful');
        } elseif (!$check && self::$request->post('action')->toString() === 'forgot') {
            // ขอรหัสผ่านใหม่
            return $login->forgot(self::$request);
        } elseif (!$check && !self::$from_submit && isset($_SESSION['login'])) {
            // login อยู่แล้ว

            return $_SESSION['login'];
        } else {
            // ตรวจสอบค่าที่ส่งมา
            if (self::$login_params['username'] == '') {
                if (self::$from_submit) {
                    self::$login_message = Language::get('Please fill in');
                    self::$login_input = 'login_username';
                }
            } elseif (self::$login_params['password'] == '') {
                if (self::$from_submit) {
                    self::$login_message = Language::get('Please fill in');
                    self::$login_input = 'login_password';
                }
            } elseif (!self::$from_submit || (self::$from_submit && self::$request->isReferer())) {
                // ตรวจสอบการ login กับฐานข้อมูล
                $login_result = $login->checkLogin(self::$login_params);
                if (is_array($login_result)) {
                    // save login session
                    $login_result['password'] = self::$login_params['password'];
                    $login_result['login_id'] = $login_result['id'];
                    $_SESSION['login'] = $login_result;
                    // save login cookie
                    $time = time() + 2592000;
                    if ($login_remember == 1) {
                        setcookie('login_username', $pw->encode(self::$login_params['username']), $time, '/', null, null, true);
                        setcookie('login_password', $pw->encode(self::$login_params['password']), $time, '/', null, null, true);
                        setcookie('login_remember', $login_remember, $time, '/', null, null, true);
                    }
                } else {
                    if (is_string($login_result)) {
                        // ข้อความผิดพลาด
                        self::$login_input = self::$login_input == 'password' ? 'login_password' : 'login_username';
                        self::$login_message = Language::get($login_result);
                    }
                    // logout ลบ session และ cookie
                    unset($_SESSION['login']);
                    $time = time();
                    setcookie('login_username', '', $time, '/', null, null, true);
                    setcookie('login_password', '', $time, '/', null, null, true);
                }
            } elseif (isset($_SESSION['login'])) {
                // login อยู่แล้ว

                return $_SESSION['login'];
            }
        }

        return $login;
    }

    /**
     * @param Request $request
     */
    public static function checkAccount(Request $request)
    {
        $login = self::isMember();
        $account_id = $request->request('account_id')->toInt();
        if ($account_id == 0 || $account_id == $login['account_id']) {
            // สมาชิก
            return $login;
        } elseif ($login['status'] == 1) {
            // แอดมินเข้าระบบเป็นคนอื่น
            $user = \Kotchasan\Model::createQuery()
                ->from('user U')
                ->where(array('U.id', $account_id))
                ->toArray()
                ->first('U.*', 'U.id account_id');
            if ($user) {
                // ใช้ชื่อของ user
                $_SESSION['login']['id'] = $user['id'];
                $_SESSION['login']['account_id'] = $user['account_id'];
                $_SESSION['login']['name'] = $user['name'] == '' ? $user['username'] : $user['name'];

                return $_SESSION['login'];
            }
        }

        return null;
    }

    /**
     * ตรวจสอบความสามารถในการตั้งค่า
     * แอดมินสูงสุด (status=1) ทำได้ทุกอย่าง
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าไม่สามารถทำรายการได้คืนค่า null.
     *
     * @param array        $login
     * @param array|string $permission
     *
     * @return array|null
     */
    public static function checkPermission($login, $permission)
    {
        if (!empty($login)) {
            if ($login['status'] == 1) {
                // แอดมิน
                return $login;
            } elseif (!empty($permission)) {
                if (is_array($permission)) {
                    foreach ($permission as $item) {
                        if (in_array($item, $login['permission'])) {
                            // มีสิทธิ์
                            return $login;
                        }
                    }
                } elseif (in_array($permission, $login['permission'])) {
                    // มีสิทธิ์
                    return $login;
                }
            }
        }
        // ไม่มีสิทธิ

        return null;
    }

    /**
     * ฟังก์ชั่นส่งอีเมลลืมรหัสผ่าน.
     */
    public function forgot(Request $request)
    {
        // ค่าที่ส่งมา
        $username = $request->post('login_username')->url();
        if (empty($username)) {
            if ($request->post('action')->toString() === 'forgot') {
                self::$login_message = Language::get('Please fill in');
            }
        } else {
            self::$login_params['username'] = $username;
            // ชื่อฟิลด์สำหรับตรวจสอบอีเมล ใช้ฟิลด์แรกจาก config
            $field = reset(self::$cfg->login_fields);
            // Model
            $model = new \Kotchasan\Model();
            // ตาราง user
            $table = $model->getTableName('user');
            // ค้นหาอีเมล
            $search = $model->db()->first($table, array(array($field, $username), array('fb', '0')));
            if ($search === false) {
                self::$login_message = Language::get('not a registered user');
            } else {
                // ขอรหัสผ่านใหม่
                $err = \Index\Forgot\Model::execute($search->id, \Kotchasan\Text::rndname(6), $search->$field);
                if ($err == '') {
                    // คืนค่า
                    self::$login_message = Language::get('Your message was sent successfully');
                    self::$request = $request->withQueryParams(array('action' => 'login'));
                } else {
                    // ไม่สำเร็จ
                    self::$login_message = $err;
                }
            }
        }
    }

    /**
     * ฟังก์ชั่นตรวจสอบว่า เป็นสมาชิกตัวอย่างหรือไม่
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าไม่ใช่สมาชิกตัวอย่าง, null ถ้าเป็นสมาชิกตัวอย่างและเปิดโหมดตัวอย่างไว้.
     *
     * @param array|null $login
     *
     * @return array|null
     */
    public static function notDemoMode($login)
    {
        return $login && !empty($login['fb']) && self::$cfg->demo_mode ? null : $login;
    }
}
