#!/usr/bin/php5
<?
	class WinMain{
		private $glade;
		private $window;
		private $tv_files;
		
		function __construct(){
			$this->glade = new GladeXML("glades/win_main.glade");
			$this->glade->signal_autoconnect_instance($this);
			
			$this->window = $this->glade->get_widget("window");
			$this->window->show_all();
			
			$this->tv_files = $this->glade->get_widget("tv_files");
			$this->tv_files->get_selection()->connect("changed", array($this, "on_tvFiles_changed"));
			treeview_addColumn($this->tv_files, array("Filename"));
			
			$this->DirChoose();
		}
		
		function CloseWindow(){
			Gtk::main_quit();
		}
		
		/** Handels the event when a new file is selected in the treeview. */
		function on_tvFiles_changed(){
			$sel = treeview_getSelection($this->tv_files);
			if (!$sel){
				return null;
			}
			
			$this->glade->get_widget("tex_replace_from")->set_text($sel[0]);
		}
		
		function DirChoose(){
			$this->tv_files->get_model()->clear();
			$folder = $this->glade->get_widget("fc_dir")->get_current_folder();
			
			$fp = opendir($folder);
			while(($file = readdir($fp)) !== false){
				if ($file != "." && $file != ".." && !is_dir($folder . "/" . $file)){
					$this->tv_files->get_model()->append(array($file));
				}
			}
		}
		
		function ReplaceClicked(){
			//henter replace-text.
			$replace_from = $this->glade->get_widget("tex_replace_from")->get_text();
			$replace_to = $this->glade->get_widget("tex_replace_to")->get_text();
			
			//clearer list-store og henter current-folder.
			$this->tv_files->get_model()->clear();
			$folder = $this->glade->get_widget("fc_dir")->get_current_folder();
			
			//replacer file-names og tilføjer nye navne til listen.
			$fp = opendir($folder);
			while(($file = readdir($fp)) !== false){
				if ($file != "." && $file != ".."){
					//nyt navn.
					$file_new = str_replace($replace_from, $replace_to, $file);
					
					//rename fil og tilføj til liste.
					$renames[$file] = $file_new;
				}
			}
			
			foreach($renames AS $key => $value){
				if (rename($folder . "/" . $key, $folder . "/" . $value)){
					$this->tv_files->get_model()->append(array($value));
				}else{
					$this->tv_files->get_model()->append(array($key));
				}
			}
		}
	}
	
	//Start program.
	require_once("knjphpframework/functions_knj_extensions.php");
	require_once("knjphpframework/functions_knj_os.php");
	require_once("knjphpframework/functions_treeview.php");
	
	if (!knj_dl("gtk2")){
		die("Could not load PHP-GTK2-module.\n");
	}
	
	if (knj_os::getOS() == "windows"){
		//Set Windows-skin if running Windows.
		Gtk::rc_parse("gtkrc");
	}
	
	$win_main = new WinMain();
	Gtk::main();
?>