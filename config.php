<?php

include_once('gtd_constants.inc.php');

/******************************************/
/**********   Private settings     ********/
/******************************************/

include_once('config_private.php');

/******************************************/
/**********  Non-private settings  ********/
/******************************************/

date_default_timezone_set('Australia/Sydney');

/*********  openAI API ************/

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
$config['fir_max_tkn'] = 1500; # default if word limit not set
$config['stream'] = FALSE; # not operational

/*********  password filter ************/

$config['login_timeout'] = 30; // days

$config['pass_off_minutes'] = 2;

$config['time_now'] = getdate();
$config['time_now'] = $config['time_now'][0];
$file = fopen("config_pass_off_to.txt", "r");
$config['pass_off_to'] = intval(fgets($file));
fclose($file);

if ($config['pass_off_to'] > 0 && $config['time_now'] < $config['pass_off_to']) $config['password_on'] = FALSE;
else $config['password_on'] = TRUE;

$config['pass_back_on'] = date('Y-m-d H:i', $config['pass_off_to']);


/*********  Interface Settings ************/

// The following settings change settings for the user interface.
// These can be left at their default values, or changed if you have a different preference.

$config["title"]= 'Intertext'; // site name (appears at the top of each page)
$config["datemask"] = 'Y-m-d'; // date format - required - affects sorting
$config["datemaskYr"] = 'Y-m'; // date format - required - affects sorting
$config["theme"] = 'menu_sidebar'; //default | menu_sidebar
$config["title_suffix"]	= false; // true | false - add filename to title tag
$config["trimLength"] = 2398;     // max visible length of descriptions when listing items
$config["trimLengthInReport"] = 0;     // max visible length of descriptions when reporting children
$config["firstDayOfWeek"] = 6; // 0=Sunday, 1=Monday, ... 6=Saturday
$config['ReportMaxCompleteChildren']=0;  // maximum number of child items of any one type shown in itemReport
$config['useLiveEnhancements']=true; // javascript productivity aids: tested on PC/IE7, PC/Firefox2, Linux/Firefox2, Linux/Epiphany, Linux/Konqueror3

// These are the shortcut settings for menu options.  Add a key for any page or page view in the main menus.
// Note IE only allows 26 access keys (a-z).
$acckey = array();

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
			'p'		=>	'item', // project preference
			'm'		=>	'item', // value preference
			'v'		=>	'item', // vision preference
			'o'		=>	'item', // role preference
			'g'		=>	'item' // goal preference
	    );

// uses initials as above; so o=role, m=value, etc., each in single quotes, separated by commas
$config['suppressAsOrphans']="'i','m','v','o','g','p'";

/*********  Customize Weekly Review  ************/
$config['reviewProjectsWithoutOutcomes']=true; // false | true - list projects which have no outcome
$config['show7']=true; // false | true - show the Seven Habits of Highly Effective People and Sharpening the Saw in Weekly Review

// Entirely optional: add custom items to the weekly review.
// Uncomment to use, add more fields to the array for more lines.

$custom_review = array(
	"x" => "yz",
	"m" => "no"
);

/*********  Advanced Settings  ************/

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
    "getlistitemsbulk"          => "li.`item` desc, li.`priority`, li.`notes` asc",
    "getitemsandparent"     => "type ASC, title ASC, ptitle ASC",
    // "getitemsandparentTrades"     => "type ASC, dateCreated DESC, title ASC, ptitle ASC",
    "getorphaneditems"      => "ia.`type` ASC, i.`title` ASC",
    "selectchecklist"       => "cl.`title` ASC",
		//    "getchecklists"         => "c.`category` DESC, sortBy ASC, `title` ASC",
    "getchecklists"         => "`title` ASC",
		//    "getlists"              => "c.`category` DESC, sortBy ASC",
    "getlists"              => "`title` ASC",
    "searchlists"           => "`type` ASC, `sortBy` ASC, `priority` ASC",

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
        => "cli.`priority`, cli.`item` ASC, cli.`effort` ASC, cli.`notes` ASC",
    "getchecklistitemsinst"
        => "i.`ignored` DESC, i.`checked` DESC, cli.`item` ASC, cli.`notes` ASC",

    ##########################

    "getchildren"              => "
        CASE WHEN na.`nextaction` IS NULL THEN 0 else 1 END DESC,
        i.`sortBy` ASC,
        i.`title` ASC,
        i.`description` DESC",
        // shows by next action then sortBy, then title, the tradeConditionId
    "getchildrenTrades"              => "
				i.`title` ASC,
				ia.`tradeConditionId` ASC,
        CASE WHEN na.`nextaction` IS NULL THEN 0 else 1 END DESC,
        i.`description` ASC
        ",
    "getitems"              => "
        CASE WHEN its.`dateCompleted` IS NULL THEN 0 else 1 END ASC,
        CASE WHEN its.`dateCompleted` IS NULL THEN ia.`isSomeday` ELSE its.`dateCompleted` END ASC,
        CASE WHEN its.`dateCompleted` IS NULL THEN i.`title` ELSE its.`dateCompleted` END ASC,
        i.`title` ASC", // shows by title, then not someday, then not complete
    "getitemsvisn"              => "
        CASE WHEN its.`dateCompleted` IS NULL THEN 0 else 1 END ASC,
        CASE WHEN its.`dateCompleted` IS NULL THEN ia.`isSomeday` ELSE its.`dateCompleted` END ASC,
        i.`sortBy` ASC,
        i.`title` ASC",
    "getqualities"          => "`sort` ASC",
    "getnotes"              => "tk.`date` DESC"

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
*/
$config['addons']['achievements']=array(
        'link'=>"addons/achievements/achievements.php",
        'title'=>"Completion rates", 'label' => "Achievements",
        'where'=>'listItems.php?type=a&amp;tickler=true','when'=>'after',
        'options'=>array('jpgraphdir'=>'jpgraph/')
      );

$config['formatTidy'] = true; // lazy check of items text formatting, called from cron

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
