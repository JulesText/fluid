<?php
include_once('gtd_constants.inc.php');
/*
    Note that for any of these settings, if you wish to set them to blank,
    assign them an empty string, rather than deleting the line from the file:
    e.g.:
    "prefix" => '',
*/

/******************************************/
/**********   REQUIRED SETTINGS    ********/
/******************************************/


/*********  Database Settings ************/

// Database settings are NOT optional.

$config = array(

    //connection information
        "host"                      => 'localhost', //the hostname of your database server
        "db"                        => '', //the name of your database
        "prefix"					=> 'gtdphp_', // the GTD table prefix for your installation (optional)
        "user"                      => '', //username for database access
        "pass"                      => '', //database password
    //database information
        "dbtype"                    => 'mysql',  //database type: currently only mysql is valid.  DO NOT CHANGE!
);

/*********  openAI API ************/

$config['openAI'] = 'xxx';

# fi_summary.php params
$config['fis_endpoint'] = "https://api.openai.com/v1/completions";
$config['fis_input_length'] = 3000; # limit to 3000 tokens
$config['fis_model'] = "gpt-3.5-turbo-instruct";
$config['fis_temp'] = 0.8; # temperature: creativity = 1, none =  0 (deterministic)
$config['fis_words'] = 5;

# fi_response.php params
$config['fir_endpoint'] = "https://api.openai.com/v1/chat/completions";
$config['fir_temp'] = 0; # 0 for api determined, 0.1 is deterministic, 0.9 is random
$config['fir_freq_pen'] = 0; # frequency penalty
$config['fir_pres_pen'] = 0; # presence penalty
#'gpt-3.5-turbo-16k' # unclear what 16k does (still 4k tokens)
#'gpt-3.5-turbo' # 4k tokens
#'gpt-4'
$config['fir_model_3'] = 'gpt-3.5-turbo';
$config['fir_model_4'] = 'gpt-4';
$config['fir_model_5'] = 'gpt-5';
$config['fir_max_tkn'] = 1.0; # temperature: creativity = 1, none =  0 (deterministic)
$config['fir_max_tkn'] = 1000; # default if word limit not set
$config['stream'] = FALSE; # not operational

/*********  IP filter (revert to password) ************/

$config['email_admin'] = 'gg@gmail';

$config['login_timeout'] = 30; // days

$LOGIN_INFORMATION = array(
  'mickey' => 'mouse'
);

$config['pass_off_minutes'] = 2;

$config['time_now'] = getdate();
$config['time_now'] = $config['time_now'][0];
$file = fopen("config_pass_off_to.txt", "r");
$config['pass_off_to'] = intval(fgets($file));
fclose($file);

if ($config['pass_off_to'] > 0 && $config['time_now'] < $config['pass_off_to']) $config['password_on'] = FALSE;
else $config['password_on'] = TRUE;

$config['pass_back_on'] = date('Y-m-d H:i', $config['pass_off_to']);

/******************************************/
/**********   OPTIONAL SETTINGS    ********/
/******************************************/


/*********  Interface Settings ************/

// The following settings change settings for the user interface.
// These can be left at their default values, or changed if you have a different preference.

$config["title"]= 'GTD-PHP'; // site name (appears at the top of each page)
$config["datemask"] = 'Y-m-d D'; // date format - required
$config["theme"] = 'default'; //default | menu_sidebar
$config["title_suffix"]	= false; // true | false - add filename to title tag
$config["trimLength"] = 72;     // max visible length of descriptions when listing items
$config["trimLengthInReport"] = 0;     // max visible length of descriptions when reporting children
$config["firstDayOfWeek"] = 0; // 0=Sunday, 1=Monday, ... 6=Saturday
$config['ReportMaxCompleteChildren']=0;  // maximum number of child items of any one type shown in itemReport
$config['useLiveEnhancements']=true; // javascript productivity aids: tested on PC/IE7, PC/Firefox2, Linux/Firefox2, Linux/Epiphany, Linux/Konqueror3

// These are the shortcut settings for menu options.  Add a key for any page or page view in the main menus.
// Note IE only allows 26 access keys (a-z).

$acckey = array(
	"about.php"								=> "", // License
	"achievements.php"						=> "", // Achievements
	"credits.php"							=> "", // Credits
	"donate.php"							=> "", // Donate
	"item.php?type=a"						=> "", // add Action
	"item.php?type=a&amp;nextonly=true"     => "", // add Next Action
	"item.php?type=g"						=> "", // add Goal
	"item.php?type=i"						=> "i", // add Inbox item
	"item.php?type=m"						=> "", // add Value
	"item.php?type=o"						=> "", // add Role
	"item.php?type=p"						=> "p", // add Project
	"item.php?type=p&amp;someday=true"	   	=> "", // add Someday/Maybe
	"item.php?type=r"						=> "", // add Reference
	"item.php?type=v"						=> "", // add Vision
	"item.php?type=w"						=> "", // add Waiting On
	"leadership.php"						=> "", // Leadership
	"listItems.php?quickfind"				=> "f", // quick find
	"listItems.php?type=a"					=> "a", // Actions
	"listItems.php?type=a&amp;nextonly=true"=> "n", // Next Actions
	"listItems.php?type=a&tickler=true"		=> "", // Tickler File
	"listItems.php?type=g"					=> "", // Goals
	"listItems.php?type=i"					=> "", // Inbox
	"listItems.php?type=m"					=> "", // Values
	"listItems.php?type=o"					=> "", // Roles
	"listItems.php?type=p"					=> "v", // Projects
	"listItems.php?type=p&someday=true"		=> "m", // Someday/Maybes
	"listItems.php?type=r"					=> "", // References
	"listItems.php?type=v"					=> "", // Visions
	"listItems.php?type=w"					=> "w", // Waiting On
	"listLists.php?type=C"					=> "c", // Checklists
	"listLists.php?type=L"					=> "l", // Lists
	"management.php"						=> "", // Management
	"editLists.php?type=C"					=> "", // new Checklist
	"editLists.php?type=L"					=> "", // new List
	"orphans.php"							=> "", // Orphaned Items
	"preferences.php"						=> "", // User Preferences
	"reportCategory.php"					=> "", // Categories
	"reportContext.php"						=> "x", // Space Contexts
	"reportTimeContext.php"					=> "", // Time Contexts
	"index.php"        						=> "s", // Summary
	"weekly.php"							=> "r" // Weekly Review
);


/*********  Behavior Settings ************/

// The following settings change how the interface behaves.
// These can be left at their default values, or changed if you have a different preference.

$config["contextsummary"] = 'all';  //all | nextaction (Show all actions on context report, or nextactions only?)
$config["nextaction"] = 'multiple'; //single | multiple (Allow single or multiple nextactions per project)
$config["afterCreate"]	= array (  // parent | item | list | another - default view after creating an item
			'i'		=>	'another', // inbox preference
			'a'		=>	'parent', // action preference
			'w'		=>	'parent', // waiting-on preference
			'r'		=>	'parent', // reference preference
			'p'		=>	'list', // project preference
			'm'		=>	'item', // value preference
			'v'		=>	'item', // vision preference
			'o'		=>	'item', // role preference
			'g'		=>	'list' // goal preference
	    );

// uses initials as above; so o=role, m=value, etc., each in single quotes, separated by commas
$config['suppressAsOrphans']="'i','m','v','o','g','p'";

/*********  Customize Weekly Review  ************/
$config['reviewProjectsWithoutOutcomes']=true; // false | true - list projects which have no outcome
$config['show7']=false; // false | true - show the Seven Habits of Highly Effective People and Sharpening the Saw in Weekly Review

// Entirely optional: add custom items to the weekly review.
// Uncomment to use, add more fields to the array for more lines.

/*
$custom_review = array(
	"Play the Lottery" => "Before Saturday's drawing!",
	"Pay Allowances" => "I want the kids to let me move in after I retire.",
	"Check my Oil" => "Check the oil in the car.",
	"Send Update" => "Send Weekly Update to Tom"
);
*/


/*********  Advanced Settings  ************/

//A bit too complicated for the average admin.  Will be simplified in a later release.

//Default sort order for each query.  The sort order can be overridden within each page.

$sort = array(
    "spacecontextselectbox" => "cn.`name` ASC",
    "categoryselectbox"     => "c.`category` ASC",
    "checklistselectbox"    => "cl.`title` ASC",
    "listselectbox"         => "l.`title` ASC",
    "parentselectbox"       => "i.`title` ASC",
    "timecontextselectbox"  => "ti.`timeframe` DESC",
    "getlistitems"          => "li.`item` desc, li.`priority`, li.`notes` asc",
    "getlistitemsprioritise" => "li.`priority`, li.`item` desc, li.`notes` asc",
    "getlistitemsbulk"      => "li.`item` desc, li.`priority`, li.`notes` asc",
    "getitemsandparent"     => "type ASC, ptitle ASC, title ASC, deadline ASC, dateCreated DESC",
    "getorphaneditems"      => "ia.`type` ASC, i.`title` ASC",

    ### CL item sort order ###
    # 'title > notes 4 > prioritise'
    "getchecklistitems"
        => "cli.`ignored` DESC, cli.`checked` DESC, cli.`item`, SUBSTRING(cli.`notes`, 1, 4) ASC, cli.`priority`, SUBSTRING(cli.`notes`, 1, 20) ASC",
    # 'title 2 > notes 4 > prioritise'
    "getchecklistitems_title_notes"
        => "cli.`ignored` DESC, cli.`checked` DESC, SUBSTRING(cli.`item`, 1, 2) ASC, SUBSTRING(cli.`notes`, 1, 4) ASC, cli.`priority`, SUBSTRING(cli.`notes`, 1, 20) ASC",
    # 'title > prioritise > notes'
    "getchecklistitems_title_prioritise"
        => "cli.`ignored` DESC, cli.`checked` DESC, cli.`item` ASC, cli.`priority`, SUBSTRING(cli.`notes`, 1, 20) ASC",
    # 'prioritise > title > notes'
    "getchecklistitems_prioritise"
        => "cli.`ignored` DESC, cli.`checked` DESC, cli.`priority`, cli.`item` ASC, SUBSTRING(cli.`notes`, 1, 20) ASC",
    # edit items view
    "getchecklistitemsbulk"
        => "cli.`item` ASC, cli.`priority`, cli.`effort` ASC, cli.`notes` ASC",
    "getchecklistitemsinst"
        => "i.`ignored` DESC, i.`checked` DESC, cli.`item` ASC, cli.`notes` ASC",

    ##########################
    
    "getchecklists"         => "`title` ASC",
    "getlists"              => "c.`category` ASC",
    "getchecklistitems"     => "cli.`checked` DESC, cli.`item` ASC",
    "getchildren"           => "its.`dateCompleted` DESC, ia.`deadline` DESC, i.`title` ASC",
    "getitems"              => "i.`title` ASC",
    "getnotes"              => "tk.`date` DESC",
);

$config["storeRecurrences"] = true; // false | true - when recurring items are completed, store each occurrence as a completed item
$config['useTypesForTimeContexts'] = false; // false | true - Time Contexts will be bound to a particular type
$config['separator'] = '^&*#@#%&*%^@$^*$$&%#@#@^^'; // should be an arbitrary string that you'll never use in titles of items; used to separate titles in mysql queries
$config['forceAllFields'] = false; // false | true - all fields will always be displayed on item.php
$config['allowChangingTypes'] = false; // false | true - allows the user to change the types of any item (false=change only inbox items)
$config['showAdmin'] = true; // false | true - adds the Admin option to the menu items
$config['charset'] = 'ISO8859-15'; // character-encoding for pages: utf-8 IS NOT YET SUPPORTED, nor is any other multi-byte character set
$config['withholdVersionInfo']=false; // true | false - if false, will send the version numbers of your installations of gtd-php, PHP and MySQL when you report a bug
$config['addons']=array();
/*
    addons go below this line.  For example:

$config['addons']['achievements']=array(
        "link"=>"addons/achievements/achievements.php",
        'title'=>"Notable Achievements", 'label' => "Achievements",
        'where'=>'listItems.php?type=a&amp;tickler=true','when'=>'after',
        'options'=>array('jpgraphdir'=>'../jpgraph/'));*/

/*********  Developer Settings ************/

/* The debug value is generally for the developers of the application.  You will probably want this to remain 0
Values: (use "|" to combine, "&" to test)
            0 - no debugging output
_GTD_DEBUG    - display debugging text (there will be lots of it - use debugKey to toggle its display)
_GTD_FREEZEDB - do not execute commands which would otherwise update the items table: use in conjunction with _GTD_DEBUG to display sql commands without running them
_GTD_NOTICE   - force the display of PHP notices
_GTD_WAIT     - pause after updating an item, to allow user to view processing screen
*/
$config["debug"] = 0;  // integer (actually a set of boolean flags)
$config["debugKey"] = 'H'; // the key that will toggle the display of debug text - a letter here must typed in upper case.

// php closing tag has been omitted deliberately, to avoid unwanted blank lines being sent to the browser
