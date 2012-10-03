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
 * プラグイン のメインクラス.
 *
 * @package Ebiten
 * @author pineray
 * @version $Id: $
 */
class Ebiten extends SC_Plugin_Base {

    /**
     * コンストラクタ
     * プラグイン情報(dtb_plugin)をメンバ変数をセットします.
     */
    public function __construct(array $arrSelfInfo) {
        parent::__construct($arrSelfInfo);
    }

    public function disable($arrPlugin) {
        
    }

    public function enable($arrPlugin) {
        
    }

    public function install($arrPlugin) {
        Ebiten::initialize();
    }

    public function uninstall($arrPlugin) {
        
    }

    /**
     * 初期設定
     * 
     * @return void
     */
    public static function initialize() {
        $data = array(
            DEVICE_TYPE_PC => array(
                'status' => 0,
                'per' => '',
                'tpl_value' => '',
                'case' => array(0 => array())
            ),
            DEVICE_TYPE_MOBILE => array(
                'status' => 0,
                'per' => '',
                'tpl_value' => '',
                'case' => array(0 => array())
            ),
            DEVICE_TYPE_SMARTPHONE => array(
                'status' => 0,
                'per' => '',
                'tpl_value' => '',
                'case' => array(0 => array())
            ),
            'general' => array(
                'max_cases' => 10,
            )
        );
        Model_Plugin_Ebiten::update($data);
    }

    /**
     * スーパーフックポイントコールバック関数
     *
     * @param LC_Page_Ex $objPage ページオブジェクト
     * @return void
     */
    public function preProcess(LC_Page_Ex $objPage) {
        // 機種判別
        $objPage->device_type_id = SC_Display_Ex::detectDevice();
        // テストケースの判別
        Model_Plugin_Ebiten::sessionVerify($objPage->device_type_id);
        // 出力コードをテンプレート変数にセット
        $objPage->tpl_ebiten = Model_Plugin_Ebiten::getTplValue($objPage->device_type_id);
    }

    /**
     * スーパーフックポイントコールバック関数
     *
     * @param LC_Page_Ex $objPage ページオブジェクト
     * @return void
     */
    public function process(LC_Page_Ex $objPage) {
        // テンプレートパスを変更
        $objPage->tpl_mainpage = str_replace(SMARTY_TEMPLATES_REALDIR . 'default/', SMARTY_TEMPLATES_REALDIR . Model_Plugin_Ebiten::getTplCode($objPage->device_type_id) . '/', $objPage->tpl_mainpage);
        $objPage->template = str_replace(SMARTY_TEMPLATES_REALDIR . 'default/', SMARTY_TEMPLATES_REALDIR . Model_Plugin_Ebiten::getTplCode($objPage->device_type_id) . '/', $objPage->template);
    }

    /**
     * クラスロード時のコールバック関数
     * 
     * @param string $classname クラス名
     * @param string $classpath クラスのファイルパス
     * @return void
     */
    public function hookClassLoad(&$classname, &$classpath) {
        // 閲覧環境毎の SC_View 拡張クラスを差し替え.
        if ($classname == 'SC_SiteView_Ex') {
            // 読み込むクラスファイルのパスを変更
            $classpath = PLUGIN_UPLOAD_REALDIR . 'Ebiten/classes/SC_Ebiten_SiteView.php';
            // 読み込むクラス名をファイル内のクラス名に変更
            $classname = 'SC_Ebiten_SiteView';
        }
        elseif ($classname == 'SC_MobileView_Ex') {
            // 読み込むクラスファイルのパスを変更
            $classpath = PLUGIN_UPLOAD_REALDIR . 'Ebiten/classes/SC_Ebiten_MobileView.php';
            // 読み込むクラス名をファイル内のクラス名に変更
            $classname = 'SC_Ebiten_MobileView';
        }
        elseif ($classname == 'SC_SmartphoneView_Ex') {
            // 読み込むクラスファイルのパスを変更
            $classpath = PLUGIN_UPLOAD_REALDIR . 'Ebiten/classes/SC_Ebiten_SmartphoneView.php';
            // 読み込むクラス名をファイル内のクラス名に変更
            $classname = 'SC_Ebiten_SmartphoneView';
        }
    }

    /**
     * テンプレート管理アクション実行後コールバック関数
     *
     * @param LC_Page_Ex $objPage ページオブジェクト
     * @return void
     */
    public function hookAdminTemplate(LC_Page_Admin_Design_Template_Ex $objPage) {
        // 削除またはデフォルトとなったテンプレートがテストケースに指定されていれば
        // その閲覧環境のステータスを停止にする.
        if (SC_Utils_Ex::isBlank($objPage->arrErr) && in_array($objPage->getMode(), array('register','delete'))) {
            Model_Plugin_Ebiten::disableTpl($objPage->device_type_id, $_POST['template_code']);
        }
    }
}
