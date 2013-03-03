<?php
/**
 * -----------------------------------------------------------------------------
 * MY_Controller : enhancement of standard CI_Controller class
 * 
 * @author berndvf
 * -----------------------------------------------------------------------------
 */
class MY_Controller extends CI_Controller {
  
  /*---------------------------------------------------------------------
   * Private Variables to control behaviour
   *---------------------------------------------------------------------
   */
  protected $login_url        = "/user/login";
  protected $nopermission_url = "/user/nopermission";
  
  /*
   * A Regular Expression of URLs that won't be subjected to authentication
   */
  protected $non_auth_url_reg = "/user\/login|user\/logout|user\/nopermission|^api\//";
  /*
   * Turn authentication on and off
   */
  protected $use_auth = FALSE;


  /*---------------------------------------------------------------------
   * Public Variables
   *---------------------------------------------------------------------
   */
  /*
   * Current URL
   */
  public $current_url;
  
  /*
   * Current Logged on user
   */
  public $loggeduser;
  
  /*
   * Main view to be used for the request - this can be overridden in a controller
   * or in a particular function before _loadview_withparse is called
   */
  public $view = "main";
  
  public $user;
  
  /* 
   * This is the data array to be passed to the templates
   */
  public $data = array();
  
  /*
   * This contains the data from a POST request
   */
  public $postData;
  
  /* 
   * This contains [controller]/[function] to be used in template parsing
   */
  public $parseView; 
  
  /* 
   * This will be the title passed which will be available as $title in the views
   */
  public $pageTitle = "<my app title>";
  
  /* 
   * This is the model name to be autoloaded _model_autoload
   */
  public $modelDB;   
  
  /*
   * This is Javascript to be executed on the jQuery init function. Each function
   * in the controller can add any JS to this.
   */
  public $jQueryOnInit   = array();
  
  /*
   * Extra JS files that will be loaded. These can be added by controller functions.
   */
  public $extraJSFiles   = array();
  
  /*
   * Extra CSS files that will be loaded. These can be added by controller
   * functions.
   */
  public $extraCSSFiles  = array();
  
  /*
   * These are options that can be loaded from the DB
   */
  public $app_options = array();
  
  /*---------------------------------------------------------------------
   * Default Constructor
   *---------------------------------------------------------------------
   */
  public function __construct() {
    parent::__construct();
    
    $this->load->library( "parser" );
    $this->load->helper( "url" );    
    
    $this->current_url = $this->uri->uri_string();
    $this->loggeduser = $this->session->userdata( "current_user" );

    /* 
     * If this is a POST then grab the "data" object which we use in forms 
     * and put it into the postData property.
     */
    if ( $this->_is_post() ) {
      $this->postData = $this->input->post( "data" );
    } else {
      $this->postData = NULL;
    }
    
    /* 
     * Get the current view to be used in the controller function from the 
     * URL segments
     */
    $this->parseView = trim( $this->uri->rsegment(1)."/".$this->uri->rsegment(2), "/" );
   
    /*
     *  Get the current model
     */
    $this->modelDB   = strtolower( $this->uri->rsegment(1) )."db";    
    
    /*
     * Now checked whether we need to be logged on
     */
    if ( $this->use_auth ) {
      if ( preg_match( $this->non_auth_url_reg, $this->current_url ) == 0 ) {
        $this->_checkloggedon();
        $this->_checkpermissions();
      }
    }
  }
  
  /*---------------------------------------------------------------------
   * Load any settings from the DB or INI file etc.
   *---------------------------------------------------------------------
   */
  protected function _loadsettings() {
  }
  
  /*---------------------------------------------------------------------
   * Check whether there is currently a user logged on
   *--------------------------------------------------------------------- 
   */
  protected function _checkloggedon() {
    if ( !in_array( $this->uri->uri_string(), array( "user/login" ) ) ) {
      if ( !$this->_is_logged_on() ) {
        redirect( $this->login_url );
      }
    } 
  }
  
  /*---------------------------------------------------------------------
   * Check whether the current user is logged in
   *---------------------------------------------------------------------  
   */
  protected  function _is_logged_on() {
    $loggedon = is_array( $this->loggeduser );
    if ( $loggedon ) {
      $loggedon = is_int( $this->loggeduser["ID"] * 1 );
      if ( $loggedon ) {
        $loggedon = $this->loggeduser["ID"] > 0;
      }
    }
    return $loggedon;
  }
  
  
  
  /*---------------------------------------------------------------------
   * Redirect if the user does not have permission
   *---------------------------------------------------------------------
   */
  protected function _checkpermissions() {
    if ( !$this->_has_permission() ) {
      redirect( $this->$nopermission_url );
    }
  }
  
  /*---------------------------------------------------------------------
   * Check whether the user has permission
   *---------------------------------------------------------------------
   */
  protected function _has_permission() {
    return TRUE;
  }

  /*---------------------------------------------------------------------
   * Custom HTML to show current user or ask to login etc
   *---------------------------------------------------------------------
   */
  public function _userbox() {
    if ( $this->_is_logged_on() ) {
      return "<div id='userbox'>[".$this->loggeduser["Description"]."] <a href='/user/logoff'>Log Off</a></div>";
    } 
    return "{not logged in ...}";
  }

  /*---------------------------------------------------------------------
   * Build a breadcrumb
   *---------------------------------------------------------------------
   */  
  protected function _breadcrumb( $links ) {
    $linkhtml = array();
    $linkhtml[] = "\t<li><a href='/'>Home</a><span class='divider'>/</span></li>\n";

    // *** Remove the last element
    $last_link    = end( $links );
    $last_caption = key( $links );
    array_pop($links);
    
   
    foreach( $links as $caption => $href ) {
      $linkhtml[] = "\t<li><a href='$href'>$caption</a><span class='divider'>/</span></li>\n";
    }
    $linkhtml[] = "\t<li class='active'>$last_caption</li>";
    return "<ul class='breadcrumb'>\n".implode("\t\n", $linkhtml)."</ul>";
  }

  /*---------------------------------------------------------------------    
   * Load a main view, while parsing the current template file. Extra 
   * information is sent to the main view such as title, jQuery to run,
   * extra JS, CSS files etc
   *---------------------------------------------------------------------
   */
  public function _loadview_withparse( $parse_view = "" ) {
    /* 
     * If we haven't passed a specific view then use the automatic one
     */
    if ( $parse_view == "" ) {
      $parse_view = $this->parseView;
    }
   
    /*
     *  Now parse the template and pass it to the view
     */
    $this->load->view( $this->view,
                       array( "content"          => $this->parser->parse( $parse_view,
                                                                          $this->data,
                                                                          TRUE ),
                              "userbox"          => $this->_userbox(),                                   
                              "title"            => $this->pageTitle,
                              "modules"          => array(), 
                              "jquery_on_init"   => $this->jQueryOnInit,
                              "extra_js_files"   => $this->extraJSFiles,
                              "extra_css_files"  => $this->extraCSSFiles) );
  }  
  
  /*---------------------------------------------------------------------    
   * Set the main view
   *---------------------------------------------------------------------
   */
  public function set_view( $view ) {
    $this->view = $view;
  }
  
 
  /*---------------------------------------------------------------------    
   * Loads the automatically assigned model for this controller
   *---------------------------------------------------------------------
   */
  public function _model_autoload() {
    $this->load->model( $this->modelDB );
  }
 
  /*---------------------------------------------------------------------    
   * Returns this controllers model
   *---------------------------------------------------------------------
   */
  public function _model() {
    return $this->{$this->modelDB}; 
  }
 
  /*---------------------------------------------------------------------    
   * Test whether the current request method is a POST 
   *---------------------------------------------------------------------
   */
  public function _is_post() {
    return ($_SERVER['REQUEST_METHOD'] === 'POST');
  }  
 
  /*---------------------------------------------------------------------    
   * Automatically add the posted data to the DB. Validation should have
   * been done before this is called.
   *---------------------------------------------------------------------
   */
  protected function _auto_add() {
    if ( $this->_is_post() ) {
      $this->data["newid"] = $this->_model()->add_id( $this->postData );
    }
  }
  
  /*---------------------------------------------------------------------    
   * Automatically update the posted data in the DB. Validation should 
   * have been done before this call.
   *---------------------------------------------------------------------
   */
  protected function _auto_update( $id ) {
    if ( $this->_is_post() ) {
      $this->_model()->update( $id, $this->postData );
      $this->data["updated"] = 1;
    }    
  }
  
  /*---------------------------------------------------------------------    
   * Automatically delete the posted data in the DB. Validation/confirmation
   * should have been done before this call.
   *---------------------------------------------------------------------
   */
  protected function _auto_delete( $id ) {
    if ( $this->_is_post() ) {
      $this->_model()->delete( $id );
      $this->data["deleted"] = 1;
    }    
  }
  
  /*---------------------------------------------------------------------    
   * Returns JSON data directly
   *---------------------------------------------------------------------
   */
  protected function _return_json( $object ) {
    $this->output->set_content_type( "application/json" )
                 ->set_output( json_encode($object));
  }
}

?>
