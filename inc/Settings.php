<?php 
/**
 * Settings class for mystmts
 * 
 * @author  Rune Mathisen <devel@bitjungle.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3
 */
class Settings
{
    public $db;
    public $page;
    public $security;
    public $cookie;
    public $root_dir;

    /**
     * Create a new Page object
     * 
     * Warning: Do NOT store the ini file inside the web server root!
     * 
     * @param string $file INI file name.
     */
    public function __construct($file = 'mystmts.ini')
    {
        $ini = parse_ini_file($file, true);
        $this->db = $ini['db'];
        $this->page = $ini['page'];
        $this->security = $ini['security'];
        $this->cookie = $ini['cookie'];
        $this->cookie['expires'] = time() + $this->cookie['expires'];

        $this->root_dir = dirname(dirname(__FILE__)) . '/';
    }

}
?>