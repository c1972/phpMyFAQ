<?php
/**
* $Id: record.add.php,v 1.29 2006-01-17 19:43:04 thorstenr Exp $
*
* Adds a record in the database
*
* @author       Thorsten Rinne <thorsten@phpmyfaq.de>
* @since        2003-02-23
* @copyright    (c) 2001-2006 phpMyFAQ Team
* 
* The contents of this file are subject to the Mozilla Public License
* Version 1.1 (the "License"); you may not use this file except in
* compliance with the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
* 
* Software distributed under the License is distributed on an "AS IS"
* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
* License for the specific language governing rights and limitations
* under the License.
*/

if (!defined('IS_VALID_PHPMYFAQ_ADMIN')) {
    header('Location: http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

if ($permission["editbt"]) {
	$submit = $_REQUEST["submit"];
	
	if (isset($submit[1]) && isset($_REQUEST["thema"]) && $_REQUEST["thema"] != "" && isset($_REQUEST['rubrik']) && is_array($_REQUEST['rubrik'])) {
		// new entry
		adminlog("Beitragcreatesave");
        print "<h2>".$PMF_LANG["ad_entry_aor"]."</h2>\n";
        $lang = $_REQUEST["language"];
		$thema = $db->escape_string($_REQUEST["thema"]);
		$content = $db->escape_string($_REQUEST["content"]);
		$keywords = $db->escape_string($_REQUEST["keywords"]);
		$author = $db->escape_string($_REQUEST["author"]);
        if (isset($_REQUEST["comment"])) {
            $comment = $db->escape_string($_REQUEST["comment"]);
        } else {
			$comment = "n";
	    }
		$datum = date("YmdHis");
        $rubrik = $_REQUEST['rubrik'];
		
        $nextID = $db->nextID(SQLPREFIX."faqdata", "id");
        
        $result_record = $db->query("INSERT INTO ".SQLPREFIX."faqdata (id, lang, thema, content, keywords, author, active, datum, email, comment) VALUES (".$nextID.", '".$lang."', '".$thema."', '".$content."', '".$keywords."', '".$author."', '".$_REQUEST["active"]."', '".$datum."', '".$_REQUEST["email"]."', '".$comment."')");
        
        $result_visits = $db->query("INSERT INTO ".SQLPREFIX."faqvisits (id, lang, visits, last_visit) VALUES (".$nextID.", '".$lang."', 1, ".time().")");
    	
        if ($result_record) {
    		print $PMF_LANG["ad_entry_savedsuc"];
			link_ondemand_javascript($nextID, $lang);
        } else {
    		print $PMF_LANG["ad_entry_savedfail"].$db->error();
        }
        
        // insert the new category relations
        foreach ($rubrik as $categories) {
            $db->query('INSERT INTO '.SQLPREFIX.'faqcategoryrelations VALUES ('.$categories.', \''.$_REQUEST["language"].'\', '.$nextID.', \''.$_REQUEST["language"].'\')');
        }
	} elseif (isset($submit[2]) && isset($_REQUEST["thema"]) && $_REQUEST["thema"] != "" && isset($_REQUEST['rubrik']) && is_array($_REQUEST['rubrik'])) {
		// Preview
	    $rubrik = $_REQUEST["rubrik"];
        $cat = new Category;
        $cat->transform(0);
        $categorylist = '';
        foreach ($rubrik as $categories) {
            $categorylist .= $cat->getPath($categories).'<br />';
        }
        if (isset($_REQUEST["id"]) && $_REQUEST["id"] != "") {
            $id = $_REQUEST["id"];
        } else {
            $id = "";
        }
        $content = $_POST['content'];
?>
	<h3><strong><em><?php print $categorylist; ?></em>
    <?php print $_REQUEST["thema"]; ?></strong></h3>
    <?php print $content; ?>
    <p class="little"><?php print $PMF_LANG["msgLastUpdateArticle"].makeDate(date("YmdHis")); ?><br />
    <?php print $PMF_LANG["msgAuthor"].' '.$_REQUEST["author"]; ?></p>
    
    <form action="<?php print $_SERVER["PHP_SELF"].$linkext; ?>&amp;aktion=editpreview" method="post">
    <input type="hidden" name="id" value="<?php print $id; ?>" />
    <input type="hidden" name="thema" value="<?php print htmlspecialchars($_REQUEST["thema"]); ?>" />
    <input type="hidden" name="content" value="<?php print htmlspecialchars($_POST['content']); ?>" />
    <input type="hidden" name="lang" value="<?php print $_REQUEST["language"]; ?>" />
    <input type="hidden" name="keywords" value="<?php print $_REQUEST["keywords"]; ?>" />
    <input type="hidden" name="author" value="<?php print $_REQUEST["author"]; ?>" />
    <input type="hidden" name="email" value="<?php print $_REQUEST["email"]; ?>" />
<?php
        foreach ($rubrik as $key => $categories) {
            print '    <input type="hidden" name="rubrik['.$key.']" value="'.$categories.'" />';
        }
?>
    <input type="hidden" name="active" value="<?php print $_REQUEST["active"]; ?>" />
    <input type="hidden" name="changed" value="<?php print $_REQUEST["changed"]; ?>" />
    <input type="hidden" name="comment" value="<?php print $_REQUEST["comment"]; ?>" />
    <p align="center"><input class="submit" type="submit" name="submit" value="<?php print $PMF_LANG["ad_entry_back"]; ?>" /></p>
    </form>
<?php
    } else {
        print "<h2>".$PMF_LANG["ad_entry_aor"]."</h2>\n";
		print "<p>".$PMF_LANG["ad_entryins_fail"]."</p>";
	    $rubrik = isset($_POST['rubrik']) ? $_POST['rubrik'] : null;
?>
    <form action="<?php print $_SERVER['PHP_SELF'].$linkext; ?>&amp;aktion=editpreview" method="post">
    <input type="hidden" name="thema" value="<?php print htmlspecialchars($_POST['thema']); ?>" />
    <input type="hidden" name="content" value="<?php print htmlspecialchars($_POST['content']); ?>" />
    <input type="hidden" name="lang" value="<?php print $_POST['language']; ?>" />
    <input type="hidden" name="keywords" value="<?php print $_POST['keywords']; ?>" />
    <input type="hidden" name="author" value="<?php print $_POST['author']; ?>" />
    <input type="hidden" name="email" value="<?php print $_POST['email']; ?>" />
<?php
        if (is_array($rubrik)) {
            foreach ($rubrik as $key => $categories) {
                print '    <input type="hidden" name="rubrik['.$key.']" value="'.$categories.'" />';
            }
        }
?>
    <input type="hidden" name="active" value="<?php print $_POST['active']; ?>" />
    <input type="hidden" name="changed" value="<?php print $_POST['changed']; ?>" />
    <input type="hidden" name="comment" value="<?php print $_POST['comment']; ?>" />
    <p align="center"><input class="submit" type="submit" name="submit" value="<?php print $PMF_LANG['ad_entry_back']; ?>" /></p>
    </form>
<?php
	}
} else {
	print $PMF_LANG["err_NotAuth"];
}
?>
