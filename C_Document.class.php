<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/../library/classes/Controller.class.php");
require_once(dirname(__FILE__) . "/../library/classes/Document.class.php");
require_once(dirname(__FILE__) . "/../library/classes/CategoryTree.class.php");
require_once(dirname(__FILE__) . "/../library/classes/TreeMenu.php");
require_once(dirname(__FILE__) . "/../library/classes/Note.class.php");

class C_Document extends Controller {

	var $template_mod;
	var $documents;
	var $document_categories;
	var $tree;
	var $_config;
	var $file_path;
	

	function C_Document($template_mod = "general") {
		parent::Controller();
		$this->documents = array();
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("CURRENT_ACTION", $GLOBALS['webroot']."/controller.php?" . "document&");
		
		//get global config options for this namespace
		$this->_config = $GLOBALS['oer_config']['documents'];
		
		$this->file_path = $this->_config['repository'] . preg_replace("/[^A-Za-z0-9]/","_",$_GET['patient_id']) . "/";
		
		$this->_args = array("patient_id" => $_GET['patient_id']);
		
		$this->assign("STYLE", $GLOBALS['style']);
		$t = new CategoryTree(1);
		//print_r($t->tree);
		$this->tree = $t;
	}
	
	function upload_action($patient_id,$category_id) {
		$category_name = $this->tree->get_node_name($category_id);
		$this->assign("category_id", $category_id);
		$this->assign("category_name", $category_name);
		$this->assign("patient_id", $patient_id);
		$activity = $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_upload.html");
		$this->assign("activity", $activity);
		return $this->list_action($patient_id);
	}
	
	function upload_action_process() {
		
		if ($_POST['process'] != "true")
			return;
			
		if (is_numeric($_POST['category_id'])) {	
			$category_id = $_POST['category_id'];
		}
		if (is_numeric($_POST['patient_id'])) {
			$patient_id = $_POST['patient_id'];
		}
			
		foreach ($_FILES as $file) {
		  $fname = $file['name'];
		  $err = "";
		  if ($file['error'] > 0 || empty($file['name']) || $file['size'] == 0) {
		  	$fname = $file['name'];
		  	if (empty($fname)) {
		  		$fname = htmlentities("<empty>");
		  	}
		  	$error = "Error number: " . $file['error'] . " occured while uploading file named: " . $fname . "\n";
		  	if ($file['size'] == 0) {
		  		$error .= "The system does not permit uploading files of with size 0.\n";
		  	}
		  	
		  }
		  else {
		  	
		  	if (!file_exists($this->file_path)) {
		  		if (!mkdir($this->file_path,0700)) {
		  			$error .= "The system was unable to create the directory for this upload, '" . $this->file_path . "'.\n";
		  		}
		  	}
  		    if ( $_POST['destination'] != '' ) {
  		        $fname = $_POST['destination'];
  		    }
		  	$fname = preg_replace("/[^a-zA-Z0-9_.]/","_",$fname);
		  	if (file_exists($this->file_path.$fname)) {
                                $error .= xl('File with same name already exists at location:','','',' ') . $this->file_path . "\n";
		  		$fname = basename($this->_rename_file($this->file_path.$fname));
		  		$file['name'] = $fname;
                                $error .= xl('Current file name was changed to','','',' ') . $fname ."\n";
		  	}
		  	if (move_uploaded_file($file['tmp_name'],$this->file_path.$fname)) {
        		$this->assign("upload_success", "true");
		  		$d = new Document();
		  		$d->url = "file://" .$this->file_path.$fname;
		  		$d->mimetype = $file['type'];
		  		$d->size = $file['size'];
		  		$sha1Hash = sha1_file( $this->file_path.$fname );
		  		$d->hash = $sha1Hash;
		  		$d->type = $d->type_array['file_url'];
		  		$d->set_foreign_id($patient_id);
		  		$d->persist();
		  		$d->populate();
		  		$this->assign("file",$d);
		  		
		  		if (is_numeric($d->get_id()) && is_numeric($category_id)) {
		  		  $sql = "REPLACE INTO categories_to_documents set category_id = '" . $category_id . "', document_id = '" . $d->get_id() . "'";
		  		  $d->_db->Execute($sql);
		  		}
		  	}
		  	else {
		  		$error .= "The file could not be succesfully stored, this error is usually related to permissions problems on the storage system.\n";
		  	}
		  }
		}
		$this->assign("error", nl2br($error));
		//$this->_state = false;
		$_POST['process'] = "";
		//return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_upload.html");
	}
	
	function note_action_process($patient_id) {
		
		if ($_POST['process'] != "true")
			return;
			
		$n = new Note();
		parent::populate_object($n);
		$n->persist();
		
		$this->_state = false;
		$_POST['process'] = "";
		return $this->view_action($patient_id,$n->get_foreign_id());		
	}

	function default_action() {
		return $this->list_action();
	}
	
	function view_action($patient_id="",$doc_id) {
		// Added by Rod to support document delete:
		global $gacl_object, $phpgacl_location;
		global $ISSUE_TYPES;

		require_once(dirname(__FILE__) . "/../library/acl.inc");
		require_once(dirname(__FILE__) . "/../library/lists.inc");

		$d = new Document($doc_id);	
		$n = new Note();
		
		$notes = $n->notes_factory($doc_id);
		
		$this->assign("file", $d);
		$this->assign("web_path", $this->_link("retrieve") . "document_id=" . $d->get_id() . "&");
		$this->assign("NOTE_ACTION",$this->_link("note"));
		$this->assign("MOVE_ACTION",$this->_link("move") . "document_id=" . $d->get_id() . "&process=true");

		// Added by Rod to support document delete:
		$delete_string = '';
		if (acl_check('admin', 'super')) {
			$delete_string = "<a href='' class='css_button' onclick='return deleteme(" . $d->get_id() .
				")'><span><font color='red'>" . xl('Delete') . "</font></span></a>";
		}
		$this->assign("delete_string", $delete_string);
		$this->assign("REFRESH_ACTION",$this->_link("list"));
		
		$this->assign("VALIDATE_ACTION",$this->_link("validate") .
			"document_id=" . $d->get_id() . "&process=true");

		// Added by Rod to support document date update:
		$this->assign("DOCDATE", $d->get_docdate());
		$this->assign("UPDATE_ACTION",$this->_link("update") .
			"document_id=" . $d->get_id() . "&process=true");

		// Added by Rod to support document issue update:
		$issues_options = "<option value='0'>-- " . xl('Select Issue') . " --</option>";
		$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
			"pid = $patient_id " . // AND enddate IS NULL " .
			"ORDER BY type, begdate");
		while ($irow = sqlFetchArray($ires)) {
			$desc = $irow['type'];
			if ($ISSUE_TYPES[$desc]) $desc = $ISSUE_TYPES[$desc][2];
			$desc .= ": " . $irow['begdate'] . " " . htmlspecialchars(substr($irow['title'], 0, 40));
			$sel = ($irow['id'] == $d->get_list_id()) ? ' selected' : '';
			$issues_options .= "<option value='" . $irow['id'] . "'$sel>$desc</option>";
		}
		$this->assign("ISSUES_LIST", $issues_options);

		$this->assign("notes",$notes);
		
		$this->_last_node = null;
		
		$menu  = new HTML_TreeMenu();
		
		//pass an empty array because we don't want the documents for each category showing up in this list box
 		$rnode = $this->_array_recurse($this->tree->tree,array());
		$menu->addItem($rnode);
		$treeMenu_listbox  = &new HTML_TreeMenu_Listbox($menu, array("promoText" => xl('Move Document to Category:')));
		
		$this->assign("tree_html_listbox",$treeMenu_listbox->toHTML());
		
		$activity = $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_view.html");
		$this->assign("activity", $activity);
		
		return $this->list_action($patient_id);
	}
	
	function retrieve_action($patient_id="",$document_id,$as_file=true,$original_file=true) {
	    
	        //controller function ruins booleans, so need to manually re-convert to booleans
	        if ($as_file == "true") {
		        $as_file=true;
		}
	        else if ($as_file == "false") {
		        $as_file=false;    
		}
                if ($original_file == "true") {
		        $original_file=true;
		}
	        else if ($original_file == "false") {
		        $original_file=false;   
		}
	    
		$d = new Document($document_id);
		$url =  $d->get_url();
		
		//strip url of protocol handler
		$url = preg_replace("|^(.*)://|","",$url);
		
		//change full path to current webroot.  this is for documents that may have
		//been moved from a different filesystem and the full path in the database
		//is not current.  this is also for documents that may of been moved to
		//different patients
		// NOTE that $from_filename and basename($url) are the same thing
		$from_all = explode("/",$url);
	        $from_filename = array_pop($from_all);
	        $from_patientid = array_pop($from_all);
    $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_patientid . '/' . $from_filename;
		if (file_exists($temp_url)) {
			$url = $temp_url;
		}
		if (!file_exists($url)) {
			echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.','','',' ') . $url;	
		}
		else {
		        if ($original_file) {
			    //normal case when serving the file referenced in database
                            header("Pragma: public");
			    header("Expires: 0");
			    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			    header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename($d->get_url()) . "\"");
			    header("Content-Type: " . $d->get_mimetype());
			    header("Content-Length: " . $d->get_size());
			    $f = fopen($url,"r");
			    fpassthru($f);
			    exit;
		        }
		        else {
			    //special case when retrieving a document that has been converted to a jpg and not directly referenced in database
			    $convertedFile = substr(basename($url), 0, strrpos(basename($url), '.')) . '_converted.jpg';			    
          $url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_patientid . '/' . $convertedFile;
                            header("Pragma: public");
			    header("Expires: 0");
			    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			    header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename($url) . "\"");
			    header("Content-Type: image/jpeg");
			    header("Content-Length: " . filesize($url));
			    $f = fopen($url,"r");
			    fpassthru($f);
			    exit;
			}
		}
	}
	
	function queue_action($patient_id="") {
		$messages = $this->_tpl_vars['messages'];
		$queue_files = array();
		
		//see if the repository exists and it is a directory else error
		if (file_exists($this->_config['repository']) && is_dir($this->_config['repository'])) {
			$dir = opendir($this->_config['repository']);
			//read each entry in the directory
			while (($file = readdir($dir)) !== false) {
				//concat the filename and path
				$file = $this->_config['repository'] .$file;
				$file_info = array();
				//if the filename is a file get its info and put into a tmp array
				if (is_file($file) && strpos(basename($file),".") !== 0) {
					$file_info['filename'] = basename($file);
					$file_info['mtime'] = date("m/d/Y H:i:s",filemtime($file));
					$d = Document::document_factory_url("file://" . $file);
					preg_match("/^([0-9]+)_/",basename($file),$patient_match);
					$file_info['patient_id'] = $patient_match[1];
					$file_info['document_id'] = $d->get_id();
					$file_info['web_path'] = $this->_link("retrieve",true) . "document_id=" . $d->get_id() . "&";
					
					//merge the tmp array into the larger array
					$queue_files[] = $file_info; 
				}
       		}
       		closedir($dir);
		}
		else {
			$messages .= "The repository directory does not exist, it is not a directory or there are not sufficient permissions to access it. '" . $this->config['repository'] . "'\n";	
		}
		
		
		$this->assign("queue_files",$queue_files);
		$this->_last_node = null;
		
		$menu  = new HTML_TreeMenu();
		
		//pass an empty array because we don't want the documents for each category showing up in this list box
 		$rnode = $this->_array_recurse($this->tree->tree,array());
		$menu->addItem($rnode);
		$treeMenu_listbox  = &new HTML_TreeMenu_Listbox($menu, array());
		
		$this->assign("tree_html_listbox",$treeMenu_listbox->toHTML());
		
		$this->assign("messages",nl2br($messages));
		return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_queue.html");
	}
	
	function queue_action_process() {	
		if ($_POST['process'] != "true")
			return;
		
		$messages = $this->_tpl_vars['messages'];	
		
		//build a category tree so we can have a list of category ids that are valid
		$ct = new CategoryTree(1);
		$categories = $ct->_id_name;
		
		//see if there were and posted files and assign them
		$files = null;
		is_array($_POST['files']) ? $files = $_POST['files']: $files = array();		
		
		//loop through posted files
		foreach($files as $doc_id=> $file) {
			//only operate on files checked as active
			if (!$file['active']) continue;
			
			//run basic validation checks 
			if (!is_numeric($file['patient_id']) || !is_numeric($file['category_id']) || !is_numeric($doc_id)) {
				$messages .= "Error processing file '" . $file['name'] ."' the patient id must be a number and the category must exist.\n";
				continue;	
			}
			
			//validate that the pod exists
			$d = new Document($doc_id);
			$sql = "SELECT pid from patient_data where pubpid = '" . $file['patient_id'] . "'";
			$result = $d->_db->Execute($sql);
			
			if (!$result || $result->EOF) {
				//patient id does not exist
				$messages .= "Error processing file '" . $file['name'] ." the specified patient id '" . $file['patient_id'] . "' could not be found.\n";
				continue;
			}
			
			//validate that the category id exists
			if (!isset($categories[$file['category_id']])) {
				$messages .= "Error processing file '" . $file['name'] . " the specified category with id '" . $file['category_id'] . "' could not be found.\n";
				continue;
			}
			
			//now do the work of moving the file
			$new_path = $this->_config['repository'] . $file['patient_id'] ."/";
			
			//see if the patient dir exists in the repository and create if not
			if (!file_exists($new_path)) {		  		
		  		if (!mkdir($new_path,0700)) {
		  			$messages .= "The system was unable to create the directory for this upload, '" . $this->file_path . "'.\n";
		  			continue;
		  		}
		  	}
		  	
		  	//fname is the name of the file after it is moved
		  	$fname = $file['name'];
		  	
		  	//see if patient autonumbering is used in this filename, if so strip out the autonumber part
		  	preg_match("/^([0-9]+)_/",basename($fname),$patient_match);
		  	if ($patient_match[1] == $file['patient_id']) {
		  		$fname = preg_replace("/^([0-9]+)_/","",$fname);
		  	}
		  	
		  	//filenames should not have funny chars
		  	$fname = preg_replace("/[^a-zA-Z0-9_.]/","_",$fname);
		  	
		  	//see if there is an existing file with the same name and rename as necessary
		  	if (file_exists($new_path.$file['name'])) {
		  		$messages .= "File with same name already exists at location: " . $new_path . "\n";
		  		$fname = basename($this->_rename_file($new_path.$file['name']));
		  		$messages .= "Current file name was changed to " . $fname ."\n";	
		  	}
		  	
		  	//now move the file
		  	if (rename($this->_config['repository'].$file['name'],$new_path.$fname)) {
		  		$messages .= "File " . $fname . " moved to patient id '" . $file['patient_id'] ."' and category '" . $categories[$file['category_id']]['name'] . "' successfully.\n";
		  		$d->url = "file://" .$new_path.$fname;
		  		$d->set_foreign_id($file['patient_id']);
		  		$d->set_mimetype($mimetype);
		  		$d->persist();
		  		$d->populate();
		  		
		  		if (is_numeric($d->get_id()) && is_numeric($file['category_id'])) {
		  		  $sql = "REPLACE INTO categories_to_documents set category_id = '" . $file['category_id'] . "', document_id = '" . $d->get_id() . "'";
		  		  $d->_db->Execute($sql);
		  		}
		  	}
		  	else {
		  		$error .= "The file could not be succesfully stored, this error is usually related to permissions problems on the storage system.\n";
		  	}
		}
			$this->assign("messages",$messages);
			$_POST['process'] = "";
	}
	
	function move_action_process($patient_id="",$document_id) {
		if ($_POST['process'] != "true")
			return;
		
		$new_category_id = $_POST['new_category_id'];
		$new_patient_id = $_POST['new_patient_id'];
		
		//move to new category
		if (is_numeric($new_category_id) && is_numeric($document_id)) {
			$sql = "UPDATE categories_to_documents set category_id = '" . $new_category_id . "' where document_id = '" . $document_id ."'";
			$messages .= xl('Document moved to new category','','',' \'') . $this->tree->_id_name[$new_category_id]['name']  . xl('successfully.','','\' ') . "\n";
			//echo $sql;
			$this->tree->_db->Execute($sql);
		}
		
		//move to new patient
		if (is_numeric($new_patient_id) && is_numeric($document_id)) {
			$d = new Document($document_id);
			// $sql = "SELECT pid from patient_data where pubpid = '" . $new_patient_id . "'";
			$sql = "SELECT pid from patient_data where pid = '" . $new_patient_id . "'";
			$result = $d->_db->Execute($sql);
			
			if (!$result || $result->EOF) {
				//patient id does not exist
				$messages .= xl('Document could not be moved to patient id','','',' \'') . $new_patient_id  . xl('because that id does not exist.','','\' ') . "\n";
			}
			else {
				//set the new patient
				$d->set_foreign_id($new_patient_id);
				$d->persist();
				$this->_state = false;
				$messages .= xl('Document moved to patient id','','',' \'') . $new_patient_id  . xl('successfully.','','\' ') . "\n";
				$this->assign("messages",$messages);
				return $this->list_action($patient_id);
			}
		}
		//in this case return the document to the queue instead of moving it
		elseif (strtolower($new_patient_id) == "q" && is_numeric($document_id)) {
			$d = new Document($document_id);
			$new_path = $this->_config['repository'];
			$fname = $d->get_url_file();

			//see if there is an existing file with the same name and rename as necessary
		  	if (file_exists($new_path.$d->get_url_file())) {
		  		$messages .= "File with same name already exists in the queue.\n";
		  		$fname = basename($this->_rename_file($new_path.$d->get_url_file()));
		  		$messages .= "Current file name was changed to " . $fname ."\n";	
		  	}
		  	 
		  	//now move the file
		  	if (rename($d->get_url_filepath(),$new_path.$fname)) {
		  		$d->url = "file://" .$new_path.$fname;
		  		$d->set_foreign_id("");
				$d->persist();
		  		$d->persist();
		  		$d->populate();
		  		
		  		$sql = "DELETE FROM categories_to_documents where document_id =" . $d->_db->qstr($document_id);
				$d->_db->Execute($sql);
				$messages .= "Document returned to queue successfully.\n";
				
		  	}
		  	else {
		  		$messages .= "The file could not be succesfully stored, this error is usually related to permissions problems on the storage system.\n";
		  	}

			$this->_state = false;
			$this->assign("messages",$messages);
			return $this->list_action($patient_id);
		}
		
		$this->_state = false;
		$this->assign("messages",$messages);
		return $this->view_action($patient_id,$document_id);
	}
	
	function validate_action_process($patient_id="", $document_id) {
	    if ($_POST['process'] != "true") {
			die("process is '" . $_POST['process'] . "', expected 'true'");
			return;
		}
		
		$d = new Document( $document_id );
		$current_hash = sha1_file( $d->get_url_filepath() );
		$messages = xl('Current Hash').": ".$current_hash."<br>";
		$messages .= xl('Stored Hash').": ".$d->get_hash()."<br>";
		if ( $current_hash != $d->get_hash() ) {
		    $messages .= xl('Hash does not match. Data integrity has been compromised.');
		} else {
		    $messages .= xl('Document passed integrity check.');
		}
		
		$this->_state = false;
		$this->assign("messages", $messages);
		return $this->view_action($patient_id, $document_id);
	}

	// Added by Rod for metadata update.
	//
	function update_action_process($patient_id="", $document_id) {
		if ($_POST['process'] != "true") {
			die("process is '" . $_POST['process'] . "', expected 'true'");
			return;
		}

		$docdate = $_POST['docdate'];
		$docname = $_POST['docname'];
		$issue_id = $_POST['issue_id'];

		if (is_numeric($document_id)) {
		    $messages = '';
		    $d = new Document( $document_id );
		    $file_name = $d->get_url_file();
		    if ( $docname != '' &&
		         $docname != $file_name ) {
		        $path = $d->get_url_filepath();
		        $path = str_replace( $file_name, "", $path );  
		        $new_url = $path.$docname;
     		    if (rename($d->get_url(),$new_url)) {
     		        $d->url = $new_url;
     	            $d->persist();
     	            $d->populate();
     				$messages .= xl('Document successfully renamed.')."<br>";
     		  	} else {
     		  		$messages .= xl('The file could not be succesfully renamed, this error is usually related to permissions problems on the storage system.')."<br>";
     		  	}
		    }
		 
			if (preg_match('/^\d\d\d\d-\d+-\d+$/', $docdate)) {
				$docdate = "'$docdate'";
			} else {
				$docdate = "NULL";
			}
			if (!is_numeric($issue_id)) {
				$issue_id = 0;
			}
			$sql = "UPDATE documents SET docdate = $docdate, " .
				"list_id = '$issue_id' " .
				"WHERE id = '$document_id'";
			$this->tree->_db->Execute($sql);
			$messages .= xl('Document date and issue updated successfully') . "<br>";
		}

		$this->_state = false;
		$this->assign("messages", $messages);
		return $this->view_action($patient_id, $document_id);
	}

	function list_action($patient_id = "") {
		$this->_last_node = null;
		$categories_list = $this->tree->_get_categories_array($patient_id);
		//print_r($categories_list);
				
		$menu  = new HTML_TreeMenu();
 		$rnode = $this->_array_recurse($this->tree->tree,$categories_list);
		$menu->addItem($rnode);
		$treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => 'images', 'defaultClass' => 'treeMenuDefault'));
		$treeMenu_listbox  = &new HTML_TreeMenu_Listbox($menu, array('linkTarget' => '_self'));
		
		$this->assign("tree_html",$treeMenu->toHTML());
		
		return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_list.html");
	}
	
	/*
	*	This is a recursive function to rename a file to something that doesn't already exist.
	*       Modified in version 3.2.0 to place a counter within the filename (previously was placed at end)
	*        to ensure documents opened correctly by external browser viewers. If the counter is at the
        *        end of the file, then will use it (to continue to work with older files), however all new
	*        counters will be placed within filenames. 
	*/
	function _rename_file($fname) {
		$file = basename($fname);
		$fparts = split("\.",$fname);
		$path = dirname($fname);
	        if (count($fparts) > 1) {
		  if (is_numeric($fparts[count($fparts) -2]) && (count($fparts) > 2)) {
                        //increment the counter in filename
			$fparts[count($fparts) -2] = $fparts[count($fparts) -2] + 1;
		        $fname = join(".",$fparts);
		  }
		  elseif (is_numeric($fparts[count($fparts) -1]) && $fparts[count($fparts) -1] < 1000) {
		        //increment counter at end of filename (so compatible with previous openemr version files
			$fparts[count($fparts) -1] = $fparts[count($fparts) -1] + 1;
		        $fname = join(".",$fparts);
		  }
	          elseif (is_numeric($fparts[count($fparts) -1])) {
		        //leave date at end and place counter in filename
			array_splice($fparts, -1, 0, "1");
		        $fname = join(".",$fparts);
		  } 		    
		  else {
		        //add the counter to filename
		        array_splice($fparts, -1, 0, "1");
		        $fname = join(".",$fparts);
		  }
	        }
	        else { // (count($fparts) == 1)
		  //place counter at end of filename
		  array_push($fparts,"1");
		  $fname = join(".",$fparts);
		}
	    
		if (file_exists($fname)) {
			return $this->_rename_file($fname);
		}
		else {
			return($fname);	
		}
	}
	
	function &_array_recurse($array,$categories = array()) {
		if (!is_array($array)) {
			$array = array();	
		}
 		$node = &$this->_last_node;
 		$current_node = &$node;
		$expandedIcon = 'folder-expanded.gif';
 		foreach($array as $id => $ar) {
 			$icon = 'folder.gif';
 			if (is_array($ar)  || !empty($id)) {
 			  if ($node == null) {
 			  	//echo "r:" . $this->tree->get_node_name($id) . "<br>";
			    $rnode = new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => false));
			    $this->_last_node = &$rnode;
 			  	$node = &$rnode;
 			  	$current_node =&$rnode;
			  }
			  else {
			  	//echo "p:" . $this->tree->get_node_name($id) . "<br>";
 			    $this->_last_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
 			    $current_node =&$this->_last_node;
			  }
 			  
 			  $this->_array_recurse($ar,$categories);
 			}
 			else {
 				if ($id === 0 && !empty($ar)) {
 				  $info = $this->tree->get_node_info($id);
 				  //echo "b:" . $this->tree->get_node_name($id) . "<br>";
 				  $current_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $info['value'], 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
 				}
 				else {
 					//there is a third case that is implicit here when title === 0 and $ar is empty, in that case we do not want to do anything
 					//this conditional tree could be more efficient but working with recursive trees makes my head hurt, TODO
 					if ($id !== 0 && is_object($node)) {
 					  //echo "n:" . $this->tree->get_node_name($id) . "<br>";
 				  	  $current_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
 				  	  
 					}
 				}
 			}	

			// If there are documents in this document category, then add their
			// attributes to the current node.
			$icon = "file3.png";
			if (is_array($categories[$id])) {
				foreach ($categories[$id] as $doc) {
					$current_node->addItem(new HTML_TreeNode(array(
						'text' => $doc['docdate'] . ' ' . basename($doc['url']),
						'link' => $this->_link("view") . "doc_id=" . $doc['document_id'] . "&",
						'icon' => $icon,
						'expandedIcon' => $expandedIcon
					)));
				}
			}

		}
		return $node;
	}

}
//place to hold optional code
//$first_node = array_keys($t->tree);
		//$first_node = $first_node[0];
		//$node1 = new HTML_TreeNode(array('text' => $t->get_node_name($first_node), 'link' => "test.php", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => true), array('onclick' => "alert('foo'); return false", 'onexpand' => "alert('Expanded')"));
		
		//$this->_last_node = &$node1;

?>
