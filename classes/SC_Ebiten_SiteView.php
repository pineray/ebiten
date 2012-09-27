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

// {{{ requires
require_once PLUGIN_UPLOAD_REALDIR . 'Ebiten/classes/Model_Plugin_Ebiten.php';

/**
 * Rewrite SC_SiteView_Ex.
 *
 * @author pineray
 */
class SC_Ebiten_SiteView extends SC_SiteView {
    function __construct($setPrevURL = true) {
        parent::__construct($setPrevURL);
    }

    function init() {
        parent::init();

        // 機種判別
        $device_type_id = SC_Display_Ex::detectDevice();
        // テンプレートコードを取得
        $tpl_code = Model_Plugin_Ebiten::getTplCode($device_type_id);

        // SC_SiteView::init() で割り当てられたパスを変更.
        $this->_smarty->template_dir = SMARTY_TEMPLATES_REALDIR . $tpl_code . "/";
        $this->_smarty->compile_dir = DATA_REALDIR . "Smarty/templates_c/" . $tpl_code . "/";

        // テンプレート変数を割り当て
        $this->assign('TPL_URLPATH', ROOT_URLPATH . USER_DIR . USER_PACKAGE_DIR . $tpl_code . "/");

        // ヘッダとフッタを割り当て
        $templatePath = SMARTY_TEMPLATES_REALDIR . $tpl_code . "/";
        $header_tpl = $templatePath . 'header.tpl';
        $footer_tpl = $templatePath . 'footer.tpl';

        $this->assign('header_tpl', $header_tpl);
        $this->assign('footer_tpl', $footer_tpl);
    }
}
