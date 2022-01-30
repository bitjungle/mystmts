<?php
/**
 * Index list and entry display for mystmts
 * 
 * @author  Rune Mathisen <devel@bitjungle.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3
 */
ini_set('display_errors', 1); error_reporting(E_ALL);//REMOVE IN PRODUCTION
require_once 'inc/Settings.php';
require_once 'inc/Database.php';
require_once 'inc/Page.php';
require_once 'inc/Parsedown.php';

//$settings = new Settings();
$settings = new Settings('../../bitjunglenet-settings.ini');

if (
    isset($_COOKIE['submitkey']) 
    &&  $_COOKIE['submitkey'] == $settings->security['pwd_hash']
) {
    $make_edit_link = true;
} else {
    $make_edit_link = false;
}

try {
    $db = new Database($settings->db);
} catch (exception $e) {
    http_response_code(503); // Service Unavailable
    exit($e->getMessage());
}

$page = new Page($settings->page);

if (isset($_GET['id']) && strlen($_GET['id']) > 0 && is_numeric($_GET['id'])) {
    $statement = $db->getStatement($_GET['id']);
    $page->title = $statement['case_name'];
    $page->description = $statement['preamble'];
    $page->content = "<div class=\"w3-container w3-grey statement-header\">
                      <h2>&star;&nbsp;Innlegg/forslag den {$statement['case_date']}</h2>
                      <h3>{$statement['preamble']}</h3>";
    if (strlen($statement['case_doc_url']) > 4) {
        $page->content .= "<h4><a href=\"{$statement['case_doc_url']}\" 
                           style=\"text-decoration: none;\">
                           &#9998;&nbsp;Sakspapirer&nbsp;&rarr;</a></h4>";
    };
    if ($make_edit_link) {
        $page->content .= "<h5>
                           (<a href=\"addedit.php?id={$_GET['id']}\">rediger</a>)
                           </h5>";
    }
    $page->content .= "</div>\n<div class=\"w3-container w3-white w3-row\">";
    $parsedown = new Parsedown();
    $page->content .= "<div class=\"w3-container w3-white w3-padding w3-twothird\">
                       {$parsedown->text($statement['statement_txt'])}</div>";
    if (strlen($statement['img_file_name']) > 4) {
        $page->image = $settings->page['img_post_path'] . $statement['img_file_name'];
        $page->image_attrib = $statement['img_attrib'];
    } else {
        $page->image = $settings->page['img_default'];
    }
    $page->content .= "<div class=\"w3-container w3-white w3-padding w3-third\">
                        <a href=\"{$page->image}\">
                        <img src=\"{$page->image}\" 
                        alt=\"Bilde\" class=\"w3-image w3-right w3-mobile\" 
                        title=\"{$page->image_attrib}\" 
                        style=\"width:100%;max-width:800px;\"></a></div>";
    $page->content .= "</div>\n";
} else {
    $statements = $db->getStatementList();
    $page->title = $settings->page['title_default'];
    $page->description = $settings->page['about'];
    $page->image = $settings->page['img_default'];
    $page->content = "<div class=\"w3-container w3-white w3-padding-32\">\n";
    foreach ($statements as $s) {
        if ($make_edit_link) {
            $case_date = "<a href=\"addedit.php?id={$s['id']}\">{$s['case_date']}</a>";
        } else {
            $case_date = $s['case_date'];
        }
        $page->content .= "<h1><a href=\"?id={$s['id']}\">{$s['case_name']}</a>  
                           <span class=\"w3-right w3-tag w3-dark-grey w3-round\">
                           {$case_date}
                           </span>
                           </h1>\n
                           <p class=\"w3-text-grey\">{$s['preamble']}</p>\n<hr>\n";
    }
    $page->content .= "</div>";
}


?>
<!DOCTYPE html>
<html lang="<?php echo $settings->page['lang']; ?>">
<head>
    <title><?php echo $settings->page['owner_name'] . ': ' . $page->title; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="<?php echo $settings->page['owner_name']; ?>">
    <meta property="og:title" content="<?php echo $page->title; ?>" />
    <meta property="og:description" content="<?php echo $page->description; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo $settings->page_url; ?>" />
    <meta property="og:image" content="<?php echo $settings->app_url . $page->image; ?>" />
    <!-- <meta property="og:image:type" content="image/jpeg" /> -->
    <link rel="stylesheet" href="./css/w3.css">
    <link rel="stylesheet" href="./css/w3-colors-highway.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/favicon/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="./img/favicon/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="384x384" href="./img/favicon/icon-384x384.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./img/favicon/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./img/favicon/icon-16x16.png">
    <link rel="manifest" href="./site.webmanifest"><!-- https://manifest-gen.netlify.app -->
    <script src="./js/script.js" defer></script>
    <script type="application/ld+json">
    { "@context": "https://schema.org", 
      "@type": "BlogPosting",
      "headline": "<?php echo $page->title; ?>",
      "image": "<?php echo $settings->app_url . $page->image; ?>",
      "editor": "<?php echo $settings->page['owner_name']; ?>", 
      "genre": "politics", 
      "url": "<?php echo $settings->page_url; ?>",
      "datePublished": "<?php echo $statement['case_date']; ?>",
      "description": "<?php echo $page->description; ?>",
    }
    </script>
</head>
<body class="w3-black">
    <nav id="standardNavbar" class="w3-container w3-bar w3-xlarge w3-padding-32 w3-highway-red">
        <div class="w3-container">
            <span id="homelink">
                <a href="<?php echo $settings->app_url; ?>" style="text-decoration: none;">
                    <img src="./img/fist-50x50.png" alt="Home" style="width: 50px; height: 50px;">
                </a>
            </span>
            <span id="title"><?php echo $page->title ?></span>
            <span id="searchbox" class="w3-right w3-large">
                <input type="text" id="searchtext" placeholder="&nbsp;Skriv her&hellip;">
            </span>
            <span id="searchpre" class="w3-right w3-xlarge">Søk: </span>
        </div>
    </nav>
    <main class="w3-container w3-margin w3-black">
        <div class="w3-bar w3-black">
            <button 
            onclick="document.getElementById('aboutbox').style.display='block'" 
            class="w3-button w3-black w3-right">Om&hellip;</button>
            <div id="aboutbox" class="w3-modal">
                <div class="w3-modal-content w3-container w3-white">
                    <span 
                    onclick="document.getElementById('aboutbox').style.display='none'" 
                    class="w3-button w3-display-topright w3-highway-red">&times;</span>
                    <p class="w3-padding"><?php echo $settings->page['about'];?></p>
                </div>
            </div>
            <div id="addnew" class="w3-right">
                <a href="addedit.php" style="text-decoration: none;"><sub>&plus;</sub></a>
            </div>
            <ul id="dataViewer" class="w3-container w3-ul w3-black">
                <!-- Search hit list inserted from Javascript -->
            </ul>
        </div>
        <?php echo $page->content; ?>
    </main>
    <footer id="standardFooter" class="w3-container w3-bar w3-padding w3-highway-red">
        <div class="w3-container" style="color: silver;">
            <a rel="license" href="<?php echo $settings->page['license_url']; ?>">
                <img alt="License Image" style="border-width:0" 
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