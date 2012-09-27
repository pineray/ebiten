<?php
/*
 * Ebiten - Easy A/B Tester
 * Copyright (C) 2012 pineray. All Rights Reserved.
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * プラグイン の設定ページ呼び出し用.
 *
 * @package Ebiten
 * @author pineray
 * @version $Id: $
 */
// {{{ requires
require_once PLUGIN_UPLOAD_REALDIR . 'Ebiten/classes/LC_Page_Plugin_Ebiten_Config.php';

// }}}
// {{{ generate page
$objPage = new LC_Page_Plugin_Ebiten_Config();
register_shutdown_function(array($objPage, 'destroy'));
$objPage->init();
$objPage->process();
