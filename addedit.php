<?php
/**
 * Add or edit an entry in mystmts
 * 
 * @author  Rune Mathisen <devel@bitjungle.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3
 */
ini_set('display_errors', 1); error_reporting(E_ALL);//REMOVE IN PRODUCTION
require_once 'inc/Settings.php';
require_once 'inc/Database.php';
require_once 'inc/Page.php';
require_once 'inc/Parsedown.php';

$settings = new Settings();
//$settings = new Settings('../bitjunglenet-settings.ini');

/**
 * Create a POST response message
 * 
 * @param bool $success 
 * @param string $msg 
 * 
 * @return string
 */
function make_message($success, $msg) 
{
    if ($success) {
        $color = 'w3-green';
        $heading = 'Suksess!';
    } else {
        $color = 'w3-red';
        $heading = 'Feil!';
    }
    return "<div class=\"w3-panel {$color}\">
            <h3>{$heading}</h3>
            {$msg}
            </div>";
}

/**
 * Handle the POST request
 * 
 * @param array $p 
 * @param array $f 
 * @param object $db 
 * @param string $savekey 
 * @param string $img_path 
 * @param string $default_img
 * @param array $cookie
 * 
 * @return array (bool, string) 
 */
function handle_post($p, $f, $db, $savekey, $img_path, $default_img, $cookie) 
{
    if (!isset($p['submitkey']) || sha1($p['submitkey']) != $savekey) {
        // I do not trust this user, and refuse to save the statement
        return array(false, '<p>Nøkkelordet var feil!</p>');
    } else {
        // Store the key in a cookie for 30 days
        setcookie('submitkey', $savekey, $cookie);
    }
    if (isset($f['case_img']['name']) && !empty($f['case_img']['name'])) {
        // A file was uploaded
        $is_image = strpos(mime_content_type($f['case_img']['tmp_name']), 'image/');
        if (!is_int($is_image)) {
            // I don't trust that file to be an image
            $img_uploaded = false;
        } else {
            $uploadfile = $img_path . basename($f['case_img']['name']);
            $img_uploaded = move_uploaded_file(
                $f['case_img']['tmp_name'], 
                $uploadfile
            );
            $p['img_file_name'] = $f['case_img']['name'];
        }
    } else {
        // Keep old image file or set to default image
        if (!isset($p['img_file_name'])) {
            $p['img_file_name'] = $default_img;
        }
        $img_uploaded = true;
    }
    if (isset($p['id']) && is_numeric($p['id'])) {
        $post_status = $db->update($p);
    } else {
        $post_status = $db->add($p);
    }
    if ($post_status && $img_uploaded) {
        return array($post_status, '<p>Innlegget ble lagret.</p>');
    } else {
        $f = '<p>Det skjedde en feil ved lagring av innlegget.</p>';
        if (!$post_status) $f .= '<p>Innlegget ble ikke lagret i databasen!</p>';
        if (!$img_uploaded) $f .= '<p>Bildet ble ikke lagret!</p>';
        return array(false, $f);
    }
}

// =====================================================================

try {
    $db = new Database($settings->db);
} catch (exception $e) {
    http_response_code(503); // Service Unavailable
    exit($e->getMessage());
}

$page = new Page($settings->page);
$page->title = "Legg til nytt innlegg";

$id = false;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    //The user requested to load a statement
    $id = $_GET['id'];
    $s = $db->getStatement($id); // Load the requested statement
    if ($s) {
        $page->title = "Rediger innlegg";
    } else {
        $id = false; // Did not find a statement with the given id
    }
}

$statusbar = '';
if (isset($_POST) && !empty($_POST)) {
    // The user posted a statement, try to save it
    $status = handle_post(
        $_POST, 
        $_FILES,
        $db,
        $settings->security['pwd_hash'],
        $settings->root_dir . $settings->page['img_post_path'],
        $settings->page['img_default'],
        $settings->cookie
    );
    $statusbar = make_message($status[0], $status[1]);
    if (is_int($status[0])) {
        // Load the statement that was saved
        $id = $status[0];
        $s = $db->getStatement($id);
    }
}

?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <title><?php echo $page->title ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="BITJUNGLE Rune Mathisen">
    <link rel="stylesheet" href="./css/w3.css">
    <link rel="stylesheet" href="./css/w3-colors-highway.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/favicon/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="./img/favicon/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="384x384" href="./img/favicon/icon-384x384.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./img/favicon/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./img/favicon/icon-16x16.png">
    <link rel="manifest" href="./site.webmanifest">
    <script src="./js/script.js" defer></script>
</head>
<body class="w3-black">
    <nav id="standardNavbar" class="w3-container w3-bar w3-xlarge w3-padding-32 w3-highway-red">
        <div class="w3-container">
            <span id="homelink">
                <a href="<?php echo $settings->page['root_path']?>"  style="text-decoration: none;">
                    <img src="./img/fist-50x50.png" alt="Home" style="width: 50px; height: 50px;">
                </a>
            </span>
            <span id="title"><?php echo $page->title ?></span>
        </div>
    </nav>
    <main class="w3-container w3-margin w3-black">
        <?php echo $statusbar; ?>
        <form action="addedit.php" 
              method="POST" 
              enctype="multipart/form-data" 
              id="newstatement" 
              class="w3-container">
            <?php if ($id) {
                echo "<input name=\"id\" type=\"hidden\" value=\"{$id}\">\n"; 
            }
            ?>
            <label>
                Saksnavn:
                <input name="case_name" 
                       id="case_name" 
                       type="text" 
                       class="w3-input" 
                       <?php if ($id) echo "value=\"{$s['case_name']}\""; ?> required><br>
            </label>
            <label>
                Ingress:
                <input name="preamble" 
                       id="preamble" 
                       type="text" 
                       class="w3-input" 
                       <?php if ($id) echo "value=\"{$s['preamble']}\""; ?> required><br>
            </label>
            <label>
                Innlegg dato:
                <input name="case_date" 
                       id="case_date" 
                       type="date" 
                       class="w3-input" 
                       <?php if ($id) echo "value=\"{$s['case_date']}\""; ?> required><br>
            </label>
            <label>
                Saksdokumenter URL:
                <input name="case_doc_url" 
                       id="case_doc_url" 
                       type="url" 
                       class="w3-input"
                       <?php if ($id) echo "value=\"{$s['case_doc_url']}\""; ?>><br>    
            </label>
            <input type="hidden" name="MAX_FILE_SIZE" value="2000000" /><!-- 2 MB -->
            <label>
                Bildefil:
                <input name="case_img" 
                       id="case_img" 
                       type="file">
            </label>
            <label>
                Bilde opphavsperson:
                <input name="case_img_attrib" 
                       id="case_img_attrib" 
                       size="50"
                       type="text"
                       <?php if ($id) echo "value=\"{$s['img_attrib']}\""; ?>><br>
            </label>
            <?php if ($id) {
                echo "<input name=\"img_file_name\"  
                             id=\"img_file_name\" 
                             type=\"hidden\"
                             value=\"{$s['img_file_name']}\">";
            }
            ?>
            <textarea name="statement_txt" 
                      form="newstatement" 
                      style="width: 100%; height: 25rem;" 
                      required><?php if ($id) echo $s['statement_txt']; ?></textarea><br>
            <div class="w3-row-padding">
                <div class="w3-half">
                    <input name="submitkey" id="submitkey" 
                           type="password" class="w3-input w3-red" 
                           placeholder="Skriv inn nøkkelord" required>
                </div>
                <div class="w3-half">
                    <input type="submit" value="Lagre" class="w3-btn w3-red"><br>
                </div>
            </div>
        </form>
    </main>
    <footer id="standardFooter" class="w3-container w3-bar w3-padding w3-highway-red">
        <div class="w3-container" style="color: silver;">
            <a rel="license" href="<?php echo $settings->page['license_url']; ?>">
                <img alt="Creative Commons-lisens" style="border-width:0" 
                     src="<?php echo $settings->page['license_img']; ?>" />
            </a> 
            <a href="<?php echo $settings->page['owner_url']; ?>">
            <?php echo $settings->page['owner_name']; ?></a> - 
            <?php echo $settings->page['license_pretext']; ?>
            <a rel="license" href="<?php echo $settings->page['license_url']; ?>">
            <?php echo $settings->page['license_name']; ?></a>.<br>
            <span id="mystmts_src">Publiseringssystemet <b>mystmts</b> har en GNU GPLv3-lisens, 
            <a href="https://github.com/bitjungle/mystmts">og kan lastes ned her</a>.</span>
        </div>
    </footer>
</body>
</html>