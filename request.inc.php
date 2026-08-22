<?php

function normaliseCurrentRequest()
{
    static $normalised = false;
    if ($normalised) {
        return;
    }
    $normalised = true;

    $originalGet = $_GET;
    $originalPost = $_POST;
    $originalRequest = $_REQUEST;

    $required = array(
        'addon.php' => array('request' => array('addonid', 'url')),
        'assigntype.php' => array('get' => array('itemId')),
        'childrenmove.php' => array('request' => array('action', 'itemId', 'type')),
        'childrenupdate.php' => array('get' => array('itemId', 'type')),
        'editcat.php' => array('get' => array('field')),
        'fi_response.php' => array('post' => array('chat_id', 'model_id', 'msg', 'word_count')),
        'fi_summary.php' => array('post' => array('chat_id')),
        'itemreport.php' => array('get' => array('itemId')),
        'matrixquery.php' => array('post' => array('table', 'updCol')),
        'matrixsave.php' => array('post' => array('table', 'updCol', 'updVal')),
        'matrixsavecareer.php' => array('post' => array('id4', 'updVal')),
        'matrixsavecl.php' => array('post' => array('listId')),
        'matrixsavedays.php' => array('post' => array('table', 'updVal')),
        'matrixsavemonths.php' => array('post' => array('id4', 'updVal')),
        'note.php' => array('get' => array('noteId', 'referrer', 'type')),
        'processcat.php' => array('post' => array('field')),
        'processitems.php' => array('request' => array('action')),
        'processnote.php' => array(
            'post' => array('date', 'title', 'note', 'repeat', 'suppressUntil', 'referrer'),
        ),
        're.php' => array('get' => array('h')),
        'redirect.php' => array('get' => array('link')),
        'updatenote.php' => array(
            'get' => array('noteId'),
            'post' => array(
                'date',
                'note',
                'referrer',
                'repeat',
                'suppressUntil',
                'title',
            ),
        ),
    );

    $script = strtolower(basename($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? ''));
    if (isset($required[$script])) {
        $sources = array(
            'get' => $originalGet,
            'post' => $originalPost,
            'request' => $originalRequest,
        );
        foreach ($required[$script] as $source => $keys) {
            foreach ($keys as $key) {
                if (!array_key_exists($key, $sources[$source])) {
                    rejectMissingRequestValue($key);
                }
            }
        }
    }

    if ($script === 'processlists.php') {
        $actionKeys = array(
            'action',
            'allclear',
            'clearitemlists',
            'delete',
            'down_priorities',
            'ignoreclear',
            'listclear',
            'reset',
            'up_priorities',
        );
        $hasAction = false;
        foreach ($actionKeys as $key) {
            if (array_key_exists($key, $originalRequest)) {
                $hasAction = true;
                break;
            }
        }
        if (!$hasAction) {
            rejectMissingRequestValue('action');
        }
    }

    $knownKeys = array(
        'access_login',
        'access_password',
        'acknowledge',
        'action',
        'addonid',
        'addedItem',
        'addedList',
        'afterCreate',
        'again',
        'allclear',
        'assessed',
        'behaviour',
        'calc',
        'cancel',
        'career',
        'catMultiId',
        'catcodeId',
        'categoryId',
        'check',
        'checked',
        'checkboxes',
        'checklistId',
        'chat_id',
        'clearitemlists',
        'col2',
        'col3',
        'col4',
        'col5',
        'compare',
        'comprefs',
        'completed',
        'conclusion',
        'conditions',
        'content',
        'convert',
        'createnote',
        'data',
        'date',
        'dateCompleted',
        'dateCreated',
        'deadline',
        'debugSave',
        'delete',
        'description',
        'display',
        'db',
        'down_priorities',
        'effort',
        'everything',
        'expand',
        'field',
        'frequency',
        'h',
        'hyperlink',
        'id',
        'id2',
        'id3',
        'id4',
        'id5',
        'ignoreclear',
        'ignored',
        'install',
        'installkey',
        'instanceId',
        'isContext',
        'isMarked',
        'isNAs',
        'isSomeday',
        'isTrade',
        'itemId',
        'last',
        'link',
        'listId',
        'listclear',
        'live',
        'matrix',
        'menu',
        'metaphor',
        'model_id',
        'msg',
        'multi',
        'name',
        'needle',
        'newVisId',
        'next',
        'nextAction',
        'nextonly',
        'nometa',
        'note',
        'noteId',
        'notes',
        'notContext',
        'oldType',
        'oldVisId',
        'oldtype',
        'orphans',
        'output',
        'pId',
        'parentId',
        'pass_off',
        'pcol1',
        'pid1',
        'prefix',
        'premiseA',
        'premiseB',
        'prioritise',
        'priority',
        'q',
        'qLimit',
        'query',
        'quickfind',
        'random',
        'referrer',
        'repeat',
        'replacewith',
        'reset',
        'safe',
        'scen',
        'score',
        'scored',
        'showCompleted',
        'someday',
        'sortBy',
        'sortItems',
        'source',
        'standard',
        'submit',
        'suppress',
        'suppressIsDeadline',
        'suppressUntil',
        'table',
        'tablesToDelete',
        'test',
        'thrs_obs',
        'thrs_score',
        'title',
        'travel',
        'type',
        'unprioritised',
        'up_priorities',
        'updCol',
        'updVal',
        'upgrade',
        'url',
        'vLimit',
        'visId',
        'visions_hide',
        'wasNAonEntry',
        'word_count',
    );

    foreach ($knownKeys as $key) {
        $default = null;
        if (!array_key_exists($key, $_GET)) {
            $_GET[$key] = $default;
        }
        if (!array_key_exists($key, $_POST)) {
            $_POST[$key] = $default;
        }
        if (array_key_exists($key, $originalRequest)) {
            $_REQUEST[$key] = $originalRequest[$key];
        } elseif (array_key_exists($key, $originalPost)) {
            $_REQUEST[$key] = $originalPost[$key];
        } elseif (array_key_exists($key, $originalGet)) {
            $_REQUEST[$key] = $originalGet[$key];
        } else {
            $_REQUEST[$key] = $default;
        }
    }

    $_SERVER += array(
        'HTTP_HOST' => '',
        'HTTP_REFERER' => '',
        'HTTP_USER_AGENT' => '',
        'PHP_SELF' => '',
        'QUERY_STRING' => '',
        'REMOTE_ADDR' => '',
        'REQUEST_SCHEME' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
        'REQUEST_URI' => '/',
        'SCRIPT_FILENAME' => '',
        'SCRIPT_NAME' => '',
        'SERVER_NAME' => '',
    );
}

function rejectMissingRequestValue($key)
{
    if (!headers_sent()) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    exit("Missing required request parameter: $key");
}
