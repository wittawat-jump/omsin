<?php
/**
 * @filesource modules/index/views/welcome.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Welcome;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Kotchasan\Template;
use \Kotchasan\Login;

/**
 * Login, Forgot, Register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * ฟอร์มเข้าระบบ
   *
   * @param Request $request
   * @return object
   */
  public static function login(Request $request)
  {
    // template
    $template = Template::create('', '', 'login');
    $template->add(array(
      '/<FACEBOOK>(.*)<\/FACEBOOK>/' => empty(self::$cfg->facebook_appId) ? '' : '\\1',
      '/{TOKEN}/' => $request->createToken(),
      '/{EMAIL}/' => Login::$login_params['username'],
      '/{PASSWORD}/' => Login::$login_params['password'],
      '/{MESSAGE}/' => Login::$login_message,
      '/{CLASS}/' => empty(Login::$login_message) ? 'hidden' : (empty(Login::$login_input) ? 'message' : 'error'),
      '/<REGISTER>(.*)<\/REGISTER>/isu' => self::$cfg->demo_mode ? '' : '\\1'
    ));
    return (object)array(
        'content' => $template->render(),
        'title' => Language::get('Login with an existing account')
    );
  }

  /**
   * ฟอร์มขอรหัสผ่านใหม่
   *
   * @param Request $request
   * @return object
   */
  public static function forgot(Request $request)
  {
    // template
    $template = Template::create('', '', 'forgot');
    $template->add(array(
      '/{TOKEN}/' => $request->createToken(),
      '/{EMAIL}/' => Login::$login_params['username'],
      '/{MESSAGE}/' => Login::$login_message,
      '/{CLASS}/' => empty(Login::$login_message) ? 'hidden' : (empty(Login::$login_input) ? 'message' : 'error'),
      '/<REGISTER>(.*)<\/REGISTER>/isu' => self::$cfg->demo_mode ? '' : '\\1'
    ));
    return (object)array(
        'content' => $template->render(),
        'title' => Language::get('Get new password')
    );
  }

  /**
   * ฟอร์มสมัครสมาชิก
   *
   * @param Request $request
   * @return object
   */
  public static function register(Request $request)
  {
    // template
    $template = Template::create('', '', 'register');
    $template->add(array(
      '/{Terms of Use}/' => '<a href="{WEBURL}index.php?module=terms">{LNG_Terms of Use}</a>',
      '/{Privacy Policy}/' => '<a href="{WEBURL}index.php?module=policy">{LNG_Privacy Policy}</a>',
      '/{TOKEN}/' => $request->createToken()
    ));
    return (object)array(
        'content' => $template->render(),
        'title' => Language::get('Register')
    );
  }
}
