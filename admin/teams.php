<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 * wgTeams module for xoops
 *
 * @copyright       The XOOPS Project (http://xoops.org)
 * @license         GPL 2.0 or later
 * @package         wgteams
 * @since           1.0
 * @min_xoops       2.5.7
 * @author          Goffy - Wedega.com - Email:<webmaster@wedega.com> - Website:<http://wedega.com>
 * @version         $Id: 1.0 teams.php 1 Sun 2015/12/27 23:18:00Z Goffy - Wedega $
 */
include __DIR__ . '/header.php';
// It recovered the value of argument op in URL$ 
$op = XoopsRequest::getString('op', 'list');
// Request team_id
$teamId = XoopsRequest::getInt('team_id', 0);
// Switch options
switch ($op) {
    case 'list':
    default:
        $start        = XoopsRequest::getInt('start', 0);
        $limit        = XoopsRequest::getInt('limit', $wgteams->getConfig('adminpager'));
        $templateMain = 'wgteams_admin_teams.tpl';
        $GLOBALS['xoopsTpl']->assign('navigation', $adminMenu->addNavigation('teams.php'));
        $adminMenu->addItemButton(_AM_WGTEAMS_TEAM_ADD, 'teams.php?op=new', 'add');
        $GLOBALS['xoopsTpl']->assign('buttons', $adminMenu->renderButton());
        $teamsCount = $teamsHandler->getCountTeams();
        $teamsAll   = $teamsHandler->getAllTeams($start, $limit);
        $GLOBALS['xoopsTpl']->assign('teams_count', $teamsCount);
        $GLOBALS['xoopsTpl']->assign('wgteams_url', WGTEAMS_URL);
        $GLOBALS['xoopsTpl']->assign('wgteams_upload_url', WGTEAMS_UPLOAD_URL);
        // Table view
        if ($teamsCount > 0) {
            foreach (array_keys($teamsAll) as $i) {
                $team = $teamsAll[$i]->getValuesTeams();
                $GLOBALS['xoopsTpl']->append('teams_list', $team);
                unset($team);
            }
            if ($teamsCount > $limit) {
                include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
                $pagenav = new XoopsPageNav($teamsCount, $limit, $start, 'start', 'op=list&limit=' . $limit);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav(4));
            }
        } else {
            $GLOBALS['xoopsTpl']->assign('error', _AM_WGTEAMS_THEREARENT_TEAMS);
        }
        break;

    case 'set_onoff':
        if (isset($teamId)) {
            $teamsObj =& $teamsHandler->get($teamId);
            // get Var team_online
            $team_online = ($teamsObj->getVar('team_online') == 1) ? '0' : '1';
            // Set Var team_online
            $teamsObj->setVar('team_online', $team_online);
            if ($teamsHandler->insert($teamsObj, true)) {
               redirect_header('teams.php?op=list', 2, _AM_WGTEAMS_FORM_OK);
            }
        } else {
            echo "invalid params";
        }
        break;
    
    case 'new':
        $templateMain = 'wgteams_admin_teams.tpl';
        $adminMenu->addItemButton(_AM_WGTEAMS_TEAMS_LIST, 'teams.php', 'list');
        $GLOBALS['xoopsTpl']->assign('navigation', $adminMenu->addNavigation('teams.php'));
        $GLOBALS['xoopsTpl']->assign('buttons', $adminMenu->renderButton());
        // Get Form
        $teamsObj =& $teamsHandler->create();
        $form     = $teamsObj->getFormTeams();
        $GLOBALS['xoopsTpl']->assign('form', $form->render());
        break;

    case 'save':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('teams.php', 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        if (isset($teamId)) {
            $teamsObj =& $teamsHandler->get($teamId);
        } else {
            $teamsObj =& $teamsHandler->create();
        }
        // Set Vars
        // Set Var team_name
        $teamsObj->setVar('team_name', $_POST['team_name']);
        // Set Var team_descr
        $teamsObj->setVar('team_descr', $_POST['team_descr']);
        // Set Var team_image
        include_once XOOPS_ROOT_PATH . '/class/uploader.php';
        $uploader = new XoopsMediaUploader(WGTEAMS_UPLOAD_PATH.'/teams/images',
														$wgteams->getConfig('wgteams_img_mimetypes'),
                                                        $wgteams->getConfig('wgteams_img_maxsize'), null, null);
        if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
            $extension = preg_replace('/^.+\.([^.]+)$/sU', '', $_FILES['attachedfile']['name']);
            $imgName   = str_replace(' ', '', $_POST['team_name']) . '.' . $extension;
            $uploader->setPrefix($imgName);
            $uploader->fetchMedia($_POST['xoops_upload_file'][0]);
            if (!$uploader->upload()) {
                $errors = $uploader->getErrors();
                redirect_header('javascript:history.go(-1)', 3, $errors);
            } else {
                $teamsObj->setVar('team_image', $uploader->getSavedFileName());
            }
        } else {
            $teamsObj->setVar('team_image', $_POST['team_image']);
        }
        // Set Var team_nb_cols
        $teamsObj->setVar('team_nb_cols', $_POST['team_nb_cols']);
        // Set Var team_tablestyle
        $teamsObj->setVar('team_tablestyle', $_POST['team_tablestyle']);
        // Set Var team_imagestyle
        $teamsObj->setVar('team_imagestyle', $_POST['team_imagestyle']);
        // Set Var team_displaystyle
        $teamsObj->setVar('team_displaystyle', $_POST['team_displaystyle']);
        // Set Var team_weight
        $teamsObj->setVar('team_weight', $_POST['team_weight']);
        // Set Var team_online
        $teamsObj->setVar('team_online', ((1 == $_REQUEST['team_online']) ? '1' : '0'));
        // Set Var team_submitter
        $teamsObj->setVar('team_submitter', $_POST['team_submitter']);
        // Set Var team_date_create
        $teamsObj->setVar('team_date_create', time());
        // Insert Data
        if ($teamsHandler->insert($teamsObj)) {
            redirect_header('teams.php?op=list', 2, _AM_WGTEAMS_FORM_OK);
        }
        // Get Form
        $GLOBALS['xoopsTpl']->assign('error', $teamsObj->getHtmlErrors());
        $form =& $teamsObj->getFormTeams();
        $GLOBALS['xoopsTpl']->assign('form', $form->render());
        break;

    case 'edit':
        $templateMain = 'wgteams_admin_teams.tpl';
        $adminMenu->addItemButton(_AM_WGTEAMS_TEAM_ADD, 'teams.php?op=new', 'add');
        $adminMenu->addItemButton(_AM_WGTEAMS_TEAMS_LIST, 'teams.php', 'list');
        $GLOBALS['xoopsTpl']->assign('navigation', $adminMenu->addNavigation('teams.php'));
        $GLOBALS['xoopsTpl']->assign('buttons', $adminMenu->renderButton());
        // Get Form
        $teamsObj = $teamsHandler->get($teamId);
        $form     = $teamsObj->getFormTeams();
        $GLOBALS['xoopsTpl']->assign('form', $form->render());
        break;

    case 'delete':
        $teamsObj =& $teamsHandler->get($teamId);
        if (isset($_REQUEST['ok']) && 1 == $_REQUEST['ok']) {
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header('teams.php', 3, implode(', ', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            $team_img = $teamsObj->getVar('team_image');
            if ($teamsHandler->delete($teamsObj)) {
                if (!$team_img == '') {
                    unlink(WGTEAMS_UPLOAD_PATH . '/teams/images/' . $team_img);
                }
                redirect_header('teams.php', 3, _AM_WGTEAMS_FORM_DELETE_OK);
            } else {
                $GLOBALS['xoopsTpl']->assign('error', $teamsObj->getHtmlErrors());
            }
        } else {
            xoops_confirm(array('ok' => 1, 'team_id' => $teamId, 'op' => 'delete'), $_SERVER['REQUEST_URI'], sprintf(_AM_WGTEAMS_FORM_SURE_DELETE, $teamsObj->getVar('team_name')));
        }
        break;
}

include __DIR__ . '/footer.php';
