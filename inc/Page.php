<?php 
/**
 * HTML Page class for mystmts
 * 
 * @author  Rune Mathisen <devel@bitjungle.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3
 */
class Page
{
    private $_settings;
    public $title = '';
    public $description = '';
    public $image = '';
    public $image_attrib = '';
    public $content = '';

    /**
     * Create a new Page object
     * 
     * @param string $settings Page settings
     */
    public function __construct($settings)
    {
        $this->_settings = $settings;
    }


    /**
     * Make a HTML hyperlink
     * 
     * @param string $text  Link text
     * @param string $href  url
     * @param string $title Text for the title attribute
     * @param string $class CSS class(es)
     * 
     * @return string
     */
    public function makeHyperlink($text, $href, $title='', $class='') 
    {
        return "<a href=\"{$href}\" class=\"{$class}\" title=\"{$title}\">{$text}</a>";
    }

    /**
     * Make a HTML image element
     * 
     * @param string $src   Link to image source
     * @param string $alt   Alternate text
     * @param string $class CSS class(es)
     * 
     * @return string
     */
    public function makeImg($src, $alt='', $class='') 
    {
        return "<img src=\"{$src}\" alt=\"{$alt}\" class=\"{$class}\">";
    }

    /**
     * Make a HTML list item element including a hyperlink
     * 
     * @param string $text  Link text
     * @param string $href  url
     * @param string $title Text for the title attribute
     * 
     * @return string
     */
    function makeListItem($text, $href, $title='') 
    {
        return '<li>' 
            . $this->makeHyperlink(
                $text, 
                $href, 
                $title, 
                'w3-bar-item noTextDecoration'
            )
            . '</li>';
    }

    /**
     * Get image base path from ini file
     * 
     * @return string
     */
    public function getImgBasePath() {
        return $this->_ini['page']['img_base_path'];
    }

    /**
     * Get task image base path from ini file
     * 
     * @return string
     */
    public function getTaskImgBasePath() {
        return $this->_ini['page']['img_task_base_path'];
    }

}
?>